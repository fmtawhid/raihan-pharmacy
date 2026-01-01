<?php

namespace App\Http\Controllers\Admin;

use App\Models\MonthlyFollowUpSummary;
use App\Http\Controllers\Controller;
use App\Models\Admin; 
use App\Http\Controllers\Admin\FollowUpReportController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;

class MonthlyFollowUpSummaryController extends Controller
{
    //
    public function index(Request $request)
    {
        $admins = Admin::select('id', 'name')->get();

        $months = MonthlyFollowUpSummary::with('admin:id,name')
            ->when($request->month,  fn($q) => $q->where('month', $request->month))
            ->when($request->admin_id, fn($q) => $q->where('admin_id', $request->admin_id))
            ->orderByDesc('month')
            ->paginate(20);

        /** quick map [admin_id][month] => contacted_total of previous month */
        $prevMonths = [];
        foreach ($months as $m) {
            $prevKey = \Carbon\Carbon::parse($m->month . '-01')
                ->subMonth()->format('Y-m');
            $prevMonths[$m->admin_id][$m->month] =
                MonthlyFollowUpSummary::where('admin_id', $m->admin_id)
                ->where('month', $prevKey)
                ->value('contacted_total');
        }

        $pageTitle = 'Monthly Follow-Up Snapshots';
        return view(
            'admin.followups.summaries',
            compact('months', 'admins', 'pageTitle', 'prevMonths')
        );
    }

    public function updateNote(Request $request, MonthlyFollowUpSummary $summary)
    {
        $request->validate(['summary_note' => 'nullable|string|max:5000']);
        $summary->update(['summary_note' => $request->summary_note]);
        return back()->withSuccess('Summary note updated.');
    }
}
