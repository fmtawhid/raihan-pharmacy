<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\DeelkoSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Rules\FileTypeValidate;
// use App\Http\Helpers\LocationHelper;

class ManageUsersController extends Controller
{

    public function allUsers()
    {
        $pageTitle = 'All Customers';
        $users = $this->userData();

        $locationData = getBangladeshLocationData();
        $divisions = collect($locationData['divisions'])->pluck('name', 'id')->toArray();
        $districts = collect($locationData['districts'])->pluck('name', 'id')->toArray();
        $bdData = $locationData;

        return view('admin.users.list', compact('pageTitle', 'users', 'divisions', 'districts', 'bdData'));
    }

    public function dueBills(Request $request)
    {
        $dueFilter = function ($query) {
            $query->where('status', Status::PAYMENT_PENDING)
                ->where('method_code', 0)
                ->where('trx', 'like', 'POS-%-DUE-%');
        };

        $users = User::query()
            ->whereHas('deposits', $dueFilter)
            ->withSum(['deposits as due_amount' => $dueFilter], 'amount')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->input('q'));
                $query->where(function ($search) use ($term) {
                    $search->where('firstname', 'like', "%{$term}%")
                        ->orWhere('lastname', 'like', "%{$term}%")
                        ->orWhereRaw("CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, '')) LIKE ?", ["%{$term}%"])
                        ->orWhere('username', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%")
                        ->orWhere('dial_code', 'like', "%{$term}%")
                        ->orWhereRaw("CONCAT(COALESCE(dial_code, ''), COALESCE(mobile, '')) LIKE ?", ["%{$term}%"]);
                });
            })
            ->orderByDesc('id')
            ->paginate(getPaginate())
            ->withQueryString();

        $pageTitle = 'Due Bills';
        return view('admin.users.due_bills', compact('pageTitle', 'users'));
    }

    public function dueBillDetails(Request $request, $id)
    {
        $data = $this->dueBillDetailsData($id);
        $paidPaymentIds = $request->filled('payments')
            ? $this->paidDuePayments($data['user'], $request->query('payments'))->pluck('id')->all()
            : [];
        $pageTitle = 'Due Bill Details - ' . $data['user']->username;

        return view('admin.users.due_bill_details', $data + compact('pageTitle', 'paidPaymentIds'));
    }

    public function printDueBill($id)
    {
        return $this->customerPosInvoice($id);
    }

    public function collectDueBillPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);
        $user = User::findOrFail($id);
        $amount = round((float) $validated['amount'], 2);

        $paymentIds = DB::transaction(function () use ($user, $amount) {
            $orders = Order::where('user_id', $user->id)
                ->where('order_number', 'like', 'POS-%')
                ->whereHas('deposits', function ($query) {
                    $query->where('status', Status::PAYMENT_PENDING)
                        ->where('method_code', 0)
                        ->where('trx', 'like', 'POS-%-DUE-%');
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $remainingDue = (float) $orders->sum(fn(Order $order) => $this->posOrderDueAmount($order));
            if ($amount > $remainingDue) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount cannot exceed the outstanding due.',
                ]);
            }

            $amountToApply = $amount;
            $paymentIds = [];
            foreach ($orders as $order) {
                if ($amountToApply <= 0) {
                    break;
                }

                $orderDue = $this->posOrderDueAmount($order);
                if ($orderDue <= 0) {
                    continue;
                }

                $payment = $this->recordPosPayment($user, $order, min($amountToApply, $orderDue));
                $paymentIds[] = $payment->id;
                $amountToApply = round($amountToApply - (float) $payment->amount, 2);
            }

            if ($amountToApply > 0) {
                throw ValidationException::withMessages([
                    'amount' => 'The outstanding due changed. Please refresh and try again.',
                ]);
            }

            return $paymentIds;
        });

        return redirect()->route('admin.users.due.bills.details', [
            'id' => $user->id,
            'payments' => implode(',', $paymentIds),
        ])->withNotify([['success', 'Payment recorded successfully.']]);
    }

    public function printDueBillReceipt(Request $request, $id)
    {
        $data = $this->dueBillDetailsData($id);
        $payments = $this->paidDuePayments($data['user'], $request->query('payments'));
        $paidAmount = (float) $payments->sum('amount');
        $pageTitle = 'Payment Receipt - ' . $data['user']->username;

        return view('invoice.customer-due-receipt-a5', $data + compact('payments', 'paidAmount', 'pageTitle'));
    }

    private function paidDuePayments(User $user, $paymentIds)
    {
        abort_unless(is_string($paymentIds), 404);
        $ids = collect(explode(',', (string) $paymentIds))
            ->filter(fn($id) => ctype_digit($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();
        abort_if($ids->isEmpty(), 404);

        $payments = Deposit::where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->where('status', Status::PAYMENT_SUCCESS)
            ->where('method_code', 0)
            ->where('trx', 'like', 'POS-%-PAID-%')
            ->with('order')
            ->orderBy('id')
            ->get();
        abort_if($payments->count() !== $ids->count(), 404);

        return $payments;
    }

    private function dueBillDetailsData($id): array
    {
        $user = User::whereHas('deposits', function ($query) {
            $query->where('method_code', 0)->where('trx', 'like', 'POS-%');
        })->findOrFail($id);
        $dueDeposits = $user->deposits()
            ->with(['order.orderDetail.product'])
            ->where('status', Status::PAYMENT_PENDING)
            ->where('method_code', 0)
            ->where('trx', 'like', 'POS-%-DUE-%')
            ->orderBy('created_at')
            ->get();
        $dueOrders = $dueDeposits->groupBy('order_id')->map(function ($deposits) {
            $order = $deposits->first()->order;
            if (!$order) {
                return null;
            }

            $order->setAttribute('due_amount', (float) $deposits->sum('amount'));
            return $order;
        })->filter()->values();
        $dueAmount = (float) $dueOrders->sum('due_amount');

        return compact('user', 'dueOrders', 'dueAmount');
    }

    public function sendDueBillSms($id, DeelkoSmsService $smsService)
    {
        $user = User::findOrFail($id);
        $dueAmount = (float) Deposit::where('user_id', $user->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->where('method_code', 0)
            ->where('trx', 'like', 'POS-%-DUE-%')
            ->sum('amount');

        if ($dueAmount <= 0 || !$user->mobileNumber) {
            $notify[] = ['error', 'This customer has no outstanding POS due or phone number'];
            return back()->withNotify($notify);
        }

        $message = 'রায়হান ফার্মেসি থেকে বলছি, আপনার বকেয়া ৳'
            . number_format($dueAmount, 2)
            . ' টাকা। অনুগ্রহ করে পরিশোধ করুন। ধন্যবাদ।';
        $result = $smsService->send($user->mobileNumber, $message);
        $notify[] = [$result['success'] ? 'success' : 'error', $result['message']];

        return back()->withNotify($notify);
    }





    public function activeUsers()
    {
        $pageTitle = 'Active Customers';
        $users = $this->userData('active');
        $locationData = getBangladeshLocationData();
        $divisions = collect($locationData['divisions'])->pluck('name', 'id')->toArray();
        $districts = collect($locationData['districts'])->pluck('name', 'id')->toArray();
        $bdData = $locationData;
        return view('admin.users.list', compact('pageTitle', 'users', 'divisions', 'districts', 'bdData'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Customers';
        $users = $this->userData('banned');

        $locationData = getBangladeshLocationData();
        $divisions = collect($locationData['divisions'])->pluck('name', 'id')->toArray();
        $districts = collect($locationData['districts'])->pluck('name', 'id')->toArray();
        $bdData = $locationData;
        return view('admin.users.list', compact('pageTitle', 'users', 'divisions', 'districts', 'bdData'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified Customers';
        $users = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified Customers';
        $users = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileUnverifiedUsers()
    {
        $pageTitle = 'Mobile Unverified Customers';
        $users = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileVerifiedUsers()
    {
        $pageTitle = 'Mobile Verified Customers';
        $users = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function profileCompletedUsers()
    {
        $pageTitle = 'Profile Completed Customers';
        $users = $this->userData('profileCompleted');
        $bdData = getBangladeshLocationData();
        return view('admin.users.list', compact('pageTitle', 'users', 'bdData'));
    }

    protected function userData($scope = null)
    {
        if ($scope) {
            $users = User::$scope();
        } else {
            $users = User::query();
        }

        // 🔍 Add location filters
        if (request()->filled('division_id')) {
            $users->where('division_id', request()->division_id);
        }

        if (request()->filled('district_id')) {
            $users->where('district_id', request()->district_id);
        }

        if (request()->filled('area_name')) {
            $users->where('area_name', request()->area_name);
        }

        // 🔍 Existing filters

        return $users
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->withSum(['deposits as due_amount' => function ($query) {
                $query->where('status', Status::PAYMENT_PENDING)
                    ->where('method_code', 0)
                    ->where('trx', 'like', 'POS-%-DUE-%');
            }], 'amount')
            ->searchable(['username', 'email'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
    }


    public function detail($id)
    {
        $user               = User::findOrFail($id);
        $pageTitle          = 'Customer Detail - ' . $user->username;

        $widget['totalOrders']        = Order::isValidOrder()->where('user_id', $user->id)->count();
        $widget['canceledOrders']     = Order::isValidOrder()->canceled()->where('user_id', $user->id)->count();
        $widget['pendingOrders']      = Order::isValidOrder()->pending()->where('user_id', $user->id)->count();
        $widget['processingOrders']   = Order::isValidOrder()->processing()->where('user_id', $user->id)->count();
        $widget['deliveredOrders']    = Order::isValidOrder()->delivered()->where('user_id', $user->id)->count();
        $widget['returnedOrders']     = Order::isValidOrder()->returned()->where('user_id', $user->id)->count();
        $widget['dispatchedOrders']   = Order::isValidOrder()->dispatched()->where('user_id', $user->id)->count();
        $widget['totalOrderAmount']   = Order::isValidOrder()->delivered()->where('user_id', $user->id)->sum('total_amount');
        $widget['dueAmount']          = Deposit::where('user_id', $user->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->where('method_code', 0)
            ->where('trx', 'like', 'POS-%-DUE-%')
            ->sum('amount');

        $widget['successfulPayment']  = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->count();

        $widget['totalPaymentCharge'] = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('charge');
        $widget['pendingPayments']     = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_PENDING)->count();
        $widget['rejectedPayments']    = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_REJECT)->count();

        $widget['openedTickets']      = SupportTicket::where('user_id', $user->id)->where('status', Status::TICKET_OPEN)->count();
        $widget['closedTickets']      = SupportTicket::where('user_id', $user->id)->where('status', Status::TICKET_CLOSE)->count();
        $widget['answeredTickets']    = SupportTicket::where('user_id', $user->id)->where('status', Status::TICKET_ANSWER)->count();
        $bdData = getBangladeshLocationData();

        // Fetch prescriptions if table exists
        $prescriptions = collect();
        try {
            $prescriptions = \App\Models\PrescriptionUpload::where('user_id', $user->id)->latest()->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet, continue without prescriptions
        }

        $posOrders = Order::where('user_id', $user->id)
            ->where('order_number', 'like', 'POS-%')
            ->with(['orderDetail.product', 'deposits'])
            ->orderByDesc('id')
            ->get();

        $posTotalAmount = (float) $posOrders->sum('total_amount');
        $posPaidAmount = (float) $posOrders->sum(function ($order) {
            $paid = (float) $order->deposits
                ->where('status', Status::PAYMENT_SUCCESS)
                ->sum('amount');

            if ($paid <= 0 && $order->payment_status == Status::PAYMENT_SUCCESS) {
                $paid = (float) $order->total_amount;
            }

            return min($paid, (float) $order->total_amount);
        });
        $posDueAmount = max($posTotalAmount - $posPaidAmount, 0);
        
        $countries          = getCountries();
        return view('admin.users.detail', compact('pageTitle', 'user', 'countries', 'widget', 'bdData', 'prescriptions', 'posOrders', 'posTotalAmount', 'posPaidAmount', 'posDueAmount'));
    }

    public function posInvoice(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'order_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $orderQuery = Order::where('user_id', $user->id)
            ->where('order_number', 'like', 'POS-%')
            ->with(['orderDetail.product', 'deposits.gateway']);

        if (isset($validated['order_ids'])) {
            $orderQuery->whereIn('id', $validated['order_ids']);
        }

        $posOrders = $orderQuery->orderBy('id')->get();

        abort_if($posOrders->isEmpty(), 404, 'No POS invoices found for this customer.');
        abort_if(
            isset($validated['order_ids']) && $posOrders->count() !== count($validated['order_ids']),
            404,
            'One or more selected POS invoices were not found for this customer.'
        );

        $posTotalAmount = (float) $posOrders->sum('total_amount');
        $posPaidAmount = (float) $posOrders->sum(function ($order) {
            $paid = (float) $order->deposits
                ->where('status', Status::PAYMENT_SUCCESS)
                ->sum('amount');

            if ($paid <= 0 && $order->payment_status == Status::PAYMENT_SUCCESS) {
                $paid = (float) $order->total_amount;
            }

            return min($paid, (float) $order->total_amount);
        });
        $posDueAmount = max($posTotalAmount - $posPaidAmount, 0);
        $pageTitle = 'POS Customer Invoice - ' . $user->username;
        $isFilteredInvoice = isset($validated['order_ids']);

        return view('invoice.customer-pos-a4', compact(
            'pageTitle', 'user', 'posOrders', 'posTotalAmount', 'posPaidAmount', 'posDueAmount', 'isFilteredInvoice'
        ));
    }

    public function collectPosPayment(Request $request, $id, $orderId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = User::findOrFail($id);
        $amount = round((float) $request->amount, 2);

        DB::transaction(function () use ($user, $orderId, $amount) {
            $order = Order::where('user_id', $user->id)
                ->where('order_number', 'like', 'POS-%')
                ->lockForUpdate()
                ->findOrFail($orderId);
            $this->recordPosPayment($user, $order, $amount);
        });

        $notify[] = ['success', 'POS payment recorded successfully'];
        return back()->withNotify($notify);
    }

    public function collectAllPosPayments($id)
    {
        $user = User::findOrFail($id);

        $paymentResult = DB::transaction(function () use ($user) {
            $orders = Order::where('user_id', $user->id)
                ->where('order_number', 'like', 'POS-%')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $total = 0;
            $paymentIds = [];
            foreach ($orders as $order) {
                $dueAmount = $this->posOrderDueAmount($order);
                if ($dueAmount > 0) {
                    $payment = $this->recordPosPayment($user, $order, $dueAmount);
                    $paymentIds[] = $payment->id;
                    $total += $dueAmount;
                }
            }

            return ['amount' => round($total, 2), 'payment_ids' => $paymentIds];
        });

        if ($paymentResult['amount'] <= 0) {
            $notify[] = ['error', 'There are no POS invoice dues to collect.'];
            return back()->withNotify($notify);
        }

        return back()->with('payment_success', [
            'user_id' => $user->id,
            'payment_ids' => implode(',', $paymentResult['payment_ids']),
            'amount' => $paymentResult['amount'],
        ]);
    }

    private function recordPosPayment(User $user, Order $order, float $amount): Deposit
    {
        $dueAmount = $this->posOrderDueAmount($order);
        $amount = round($amount, 2);
        if ($dueAmount <= 0 || $amount <= 0 || $amount > $dueAmount) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount cannot exceed the outstanding due.',
            ]);
        }

        $remainingDue = round($dueAmount - $amount, 2);
        $dueDeposits = $order->deposits()
            ->where('status', Status::PAYMENT_PENDING)
            ->where('method_code', 0)
            ->where('trx', 'like', 'POS-%-DUE-%')
            ->lockForUpdate()
            ->get();

        $dueDeposit = $dueDeposits->shift();
        if ($remainingDue > 0) {
            if ($dueDeposit) {
                $dueDeposit->amount = $remainingDue;
                $dueDeposit->save();
            } else {
                $dueDeposit = new Deposit();
                $dueDeposit->user_id = $user->id;
                $dueDeposit->order_id = $order->id;
                $dueDeposit->amount = $remainingDue;
                $dueDeposit->method_code = 0;
                $dueDeposit->method_currency = gs('cur_text');
                $dueDeposit->trx = 'POS-' . $order->id . '-DUE-' . getTrx();
                $dueDeposit->status = Status::PAYMENT_PENDING;
                $dueDeposit->save();
            }
        } elseif ($dueDeposit) {
            $dueDeposit->delete();
        }

        foreach ($dueDeposits as $extraDueDeposit) {
            $extraDueDeposit->delete();
        }

        $payment = new Deposit();
        $payment->user_id = $user->id;
        $payment->order_id = $order->id;
        $payment->amount = $amount;
        $payment->method_code = 0;
        $payment->method_currency = gs('cur_text');
        $payment->trx = 'POS-' . $order->id . '-PAID-' . getTrx();
        $payment->status = Status::PAYMENT_SUCCESS;
        $payment->save();

        if ($remainingDue <= 0) {
            $order->payment_status = Status::PAYMENT_SUCCESS;
            $order->save();
        }

        return $payment;
    }

    private function posOrderDueAmount(Order $order): float
    {
        $paidAmount = (float) $order->deposits()
            ->where('status', Status::PAYMENT_SUCCESS)
            ->sum('amount');

        if ($paidAmount <= 0 && $order->payment_status == Status::PAYMENT_SUCCESS) {
            $paidAmount = (float) $order->total_amount;
        }

        return round(max((float) $order->total_amount - $paidAmount, 0), 2);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray   = (array)$countryData;
        $countries      = implode(',', array_keys($countryArray));

        $countryCode    = $request->country;
        $country        = $countryData->$countryCode->country;
        $dialCode       = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile'    => 'required|string|max:40',
            'country'   => 'required|in:' . $countries,
            'division_id' => 'required|integer',
            'district_id' => 'required|integer',
            'area_name'   => 'required|string|max:255',
            'postcode'    => 'required|string|max:20',
        ]);

        $exists = User::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $user->id)->exists();
        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $user->mobile = $request->mobile;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;

        $user->division_id = $request->division_id;
        $user->district_id = $request->district_id;
        $user->area_name   = $request->area_name;
        $user->postcode    = $request->postcode;

        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->country_name = @$country;
        $user->dial_code = $dialCode;
        $user->country_code = $countryCode;

        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $user->save();

        $notify[] = ['success', 'Customer details updated successfully'];
        return back()->withNotify($notify);
    }

    public function login($id)
    {
        Auth::loginUsingId($id);
        return to_route('user.home');
    }

    public function status(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255'
            ]);
            $user->status = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[] = ['success', 'User banned successfully'];
        } else {
            $user->status = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[] = ['success', 'User unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);
    }


    public function showNotificationSingleForm($id)
    {
        $user = User::findOrFail($id);
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.users.detail', $user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email,sms,push',
            'subject' => 'required_if:via,email,push',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $imageUrl = null;
        if ($request->via == 'push' && $request->hasFile('image')) {
            $imageUrl = fileUploader($request->image, getFilePath('push'));
        }

        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via . '_status', Status::ENABLE)->exists();
        if (!$template) {
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }

        $user = User::findOrFail($id);
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ], [$request->via], pushImage: $imageUrl);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $notifyToUser = User::notifyToUser();
        $users        = User::active()->count();
        $pageTitle    = 'Notification to Verified Customers';

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view('admin.users.notification_all', compact('pageTitle', 'users', 'notifyToUser'));
    }

    public function sendNotificationAll(Request $request)
    {
        $request->validate([
            'via'                          => 'required|in:email,sms,push',
            'message'                      => 'required',
            'subject'                      => 'required_if:via,email,push',
            'start'                        => 'required|integer|gte:1',
            'batch'                        => 'required|integer|gte:1',
            'being_sent_to'                => 'required',
            'cooling_time'                 => 'required|integer|gte:1',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedUsers|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
            'image'                        => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }


        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via . '_status', Status::ENABLE)->exists();
        if (!$template) {
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }

        if ($request->being_sent_to == 'selectedUsers') {
            if (session()->has("SEND_NOTIFICATION")) {
                $request->merge(['user' => session()->get('SEND_NOTIFICATION')['user']]);
            } else {
                if (!$request->user || !is_array($request->user) || empty($request->user)) {
                    $notify[] = ['error', "Ensure that the user field is populated when sending an email to the designated user group"];
                    return back()->withNotify($notify);
                }
            }
        }

        $scope          = $request->being_sent_to;
        $userQuery      = User::oldest()->active()->$scope();

        if (session()->has("SEND_NOTIFICATION")) {
            $totalUserCount = session('SEND_NOTIFICATION')['total_user'];
        } else {
            $totalUserCount = (clone $userQuery)->count() - ($request->start - 1);
        }


        if ($totalUserCount <= 0) {
            $notify[] = ['error', "Notification recipients were not found among the selected user base."];
            return back()->withNotify($notify);
        }


        $imageUrl = null;

        if ($request->via == 'push' && $request->hasFile('image')) {
            if (session()->has("SEND_NOTIFICATION")) {
                $request->merge(['image' => session()->get('SEND_NOTIFICATION')['image']]);
            }
            if ($request->hasFile("image")) {
                $imageUrl = fileUploader($request->image, getFilePath('push'));
            }
        }

        $users = (clone $userQuery)->skip($request->start - 1)->limit($request->batch)->get();

        foreach ($users as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ], [$request->via], pushImage: $imageUrl);
        }

        return $this->sessionForNotification($totalUserCount, $request);
    }


    private function sessionForNotification($totalUserCount, $request)
    {
        if (session()->has('SEND_NOTIFICATION')) {
            $sessionData                = session("SEND_NOTIFICATION");
            $sessionData['total_sent'] += $sessionData['batch'];
        } else {
            $sessionData               = $request->except('_token');
            $sessionData['total_sent'] = $request->batch;
            $sessionData['total_user'] = $totalUserCount;
        }

        $sessionData['start'] = $sessionData['total_sent'] + 1;

        if ($sessionData['total_sent'] >= $totalUserCount) {
            session()->forget("SEND_NOTIFICATION");
            $message = ucfirst($request->via) . " notifications were sent successfully";
            $url     = route("admin.users.notification.all");
        } else {
            session()->put('SEND_NOTIFICATION', $sessionData);
            $message = $sessionData['total_sent'] . " " . $sessionData['via'] . "  notifications were sent successfully";
            $url     = route("admin.users.notification.all") . "?email_sent=yes";
        }
        $notify[] = ['success', $message];
        return redirect($url)->withNotify($notify);
    }

    public function countBySegment($methodName)
    {
        return User::active()->$methodName()->count();
    }

    public function list()
    {
        $query = User::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages()
        ]);
    }

    public function notificationLog($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $user->username;
        $logs = NotificationLog::where('user_id', $id)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user'));
    }
}
