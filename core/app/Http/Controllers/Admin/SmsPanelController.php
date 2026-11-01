<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\DeelkoSmsService;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SmsPanelController extends Controller
{
    public function index()
    {
        $pageTitle = 'SMS Panel';
        return view('admin.sms_panel', compact('pageTitle'));
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['sent', 'failed'])],
            'source' => ['nullable', Rule::in(['sms_panel', 'due_bill'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $logs = SmsLog::query()
            ->when($request->filled('status'), fn($query) => $query->where('status', $validated['status']))
            ->when($request->filled('source'), fn($query) => $query->where('source', $validated['source']))
            ->when($request->filled('q'), function ($query) use ($validated) {
                $term = '%' . $validated['q'] . '%';
                $query->where(function ($search) use ($term) {
                    $search->where('recipient_number', 'like', $term)
                        ->orWhere('message', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate(getPaginate())
            ->withQueryString();

        $pageTitle = 'Previous SMS';
        return view('admin.sms_history', compact('pageTitle', 'logs'));
    }

    public function customers(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $hasFirstname = Schema::hasColumn('users', 'firstname');
        $hasLastname = Schema::hasColumn('users', 'lastname');
        $searchColumns = array_filter([
            Schema::hasColumn('users', 'name') ? 'name' : null,
            Schema::hasColumn('users', 'username') ? 'username' : null,
            Schema::hasColumn('users', 'email') ? 'email' : null,
            Schema::hasColumn('users', 'mobile') ? 'mobile' : null,
        ]);

        $users = User::query()
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->where(function ($search) use ($query, $searchColumns, $hasFirstname, $hasLastname) {
                foreach ($searchColumns as $column) {
                    $search->orWhere($column, 'like', '%' . $query . '%');
                }
                if ($hasFirstname && $hasLastname) {
                    $search->orWhereRaw(
                        "CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, '')) LIKE ?",
                        ['%' . $query . '%']
                    );
                }
            })
            ->orderBy('id')
            ->limit(30)
            ->get();

        return response()->json($users->map(function (User $user) use ($hasFirstname, $hasLastname) {
            $name = $hasFirstname && $hasLastname
                ? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''))
                : '';

            return [
                'id' => $user->id,
                'name' => $name ?: ($user->name ?? $user->username ?? $user->email ?? 'Customer'),
                'mobile' => $user->mobileNumber,
            ];
        }));
    }

    public function send(Request $request, DeelkoSmsService $smsService)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:all,selected,manual',
            'customer_ids' => 'required_if:recipient_type,selected|array|min:1|max:200',
            'customer_ids.*' => 'required|integer|distinct',
            'mobile' => 'required_if:recipient_type,manual|nullable|string|max:20000',
            'message' => 'required|string|max:5000',
        ]);

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $duplicateNumbers = 0;
        $seenNumbers = [];
        $message = $validated['message'];
        $sendUnique = function (string $mobile, ?int $userId) use (
            $smsService,
            $message,
            &$sent,
            &$failed,
            &$duplicateNumbers,
            &$seenNumbers
        ) {
            $numberKey = $this->recipientNumberKey($mobile);
            if (isset($seenNumbers[$numberKey])) {
                $duplicateNumbers++;
                return;
            }

            $seenNumbers[$numberKey] = true;
            $this->sendAndLog($smsService, $mobile, $userId, $message, 'sms_panel', $sent, $failed);
        };

        if ($validated['recipient_type'] === 'all') {
            User::query()
                ->when(BranchContext::id(), fn($query, $branchId) => $query->where('branch_id', $branchId))
                ->whereNotNull('mobile')
                ->where('mobile', '!=', '')
                ->select(['id', 'mobile', 'dial_code'])
                ->chunkById(100, function ($users) use ($sendUnique) {
                    foreach ($users as $user) {
                        $sendUnique($user->mobileNumber, $user->id);
                    }
                });
        } elseif ($validated['recipient_type'] === 'selected') {
            $customerIds = array_map('intval', $validated['customer_ids']);
            $users = User::query()->whereIn('id', $customerIds)->get();
            if ($users->count() !== count(array_unique($customerIds))) {
                throw ValidationException::withMessages([
                    'customer_ids' => 'One or more selected customers are unavailable.',
                ]);
            }

            foreach ($users as $user) {
                $mobile = trim((string) $user->mobileNumber);
                if ($mobile === '') {
                    $skipped++;
                    continue;
                }
                $sendUnique($mobile, $user->id);
            }
        } else {
            $numbers = preg_split('/[,;\r\n]+/', $validated['mobile']) ?: [];
            $numbers = array_values(array_unique(array_filter(array_map('trim', $numbers))));
            if (empty($numbers)) {
                throw ValidationException::withMessages([
                    'mobile' => 'Enter at least one mobile number.',
                ]);
            }

            foreach ($numbers as $number) {
                $sendUnique($number, null);
            }
        }

        $notify = [];
        if ($sent > 0) {
            $notify[] = ['success', "{$sent} SMS sent successfully; {$duplicateNumbers} duplicate number(s) skipped."];
        }
        if ($failed > 0 || $skipped > 0) {
            $notify[] = ['error', "{$failed} SMS failed; {$skipped} selected customer(s) had no mobile number."];
        }
        if ($sent === 0 && $failed === 0 && $skipped === 0) {
            $notify[] = ['error', 'No customers with mobile numbers were found.'];
        }

        return back()->withInput()->withNotify($notify);
    }

    private function recipientNumberKey(string $mobile): string
    {
        $normalized = preg_replace('/[\s().-]+/u', '', trim($mobile)) ?? trim($mobile);
        if (str_starts_with($normalized, '+')) {
            $normalized = substr($normalized, 1);
        } elseif (str_starts_with($normalized, '00')) {
            $normalized = substr($normalized, 2);
        }

        if (preg_match('/^01[3-9]\d{8}$/', $normalized)) {
            $normalized = '880' . substr($normalized, 1);
        }

        $digits = preg_replace('/\D+/u', '', $normalized);
        return $digits !== '' ? $digits : mb_strtolower($normalized);
    }

    private function sendAndLog(
        DeelkoSmsService $smsService,
        string $mobile,
        ?int $userId,
        string $message,
        string $source,
        int &$sent,
        int &$failed
    ): void {
        $result = $smsService->sendMasking($mobile, $message);
        $success = (bool) ($result['success'] ?? false);

        SmsLog::create([
            'admin_id' => auth('admin')->id(),
            'user_id' => $userId,
            'recipient_number' => $mobile,
            'message' => $message,
            'status' => $success ? 'sent' : 'failed',
            'source' => $source,
            'response_message' => $result['message'] ?? null,
            'sent_at' => $success ? now() : null,
        ]);

        $success ? $sent++ : $failed++;
    }
}
