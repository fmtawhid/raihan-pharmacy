<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUpLog;
use App\Exports\MonthlyReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FollowUpReportController extends Controller
{
    public function monthly(Request $request)
    {
        $rangeStart = now()->subDays(30)->startOfDay();

        $base = FollowUpLog::where('contact_date', '>=', now()->subDays(30))
            ->where('admin_id', auth('admin')->id())   // <— just this
            ->with('admin:id,name');

        /* ---- company-wide totals ---- */
        $stats = (clone $base)->selectRaw('
                    SUM(customers_contacted)  AS contacted,
                    SUM(potential_customers)  AS potential
                 ')->first();

        /* ---- per-admin breakdown ---- */
        $summaries = (clone $base)->groupBy('admin_id')
            ->select('admin_id')
            ->selectRaw('SUM(customers_contacted) contacted,
                         SUM(potential_customers) potential')
            ->with('admin:id,name')
            ->get();

        /* ---- Excel download ---- */
        if ($request->boolean('download')) {
            return Excel::download(
                new MonthlyReportExport($summaries, $stats),
                'follow-up-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pageTitle = '30-Day Follow-Up Report';
        return view('admin.followups.report', compact('stats', 'summaries', 'pageTitle'));
    }
}
