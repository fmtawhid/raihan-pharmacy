<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUpLog;
use Illuminate\Http\Request;

class FollowUpLogController extends Controller
{
    public function index()
    {
        $logs = FollowUpLog::with('admin')
            ->where('admin_id', auth('admin')->id())
            ->latest('contact_date')
            ->paginate(15);

        $pageTitle = 'Follow-up Logs';

        return view('admin.followups.index', compact('logs', 'pageTitle'));
    }

    public function create()
    {
        $pageTitle = 'Follow-up Logs';
        return view('admin.followups.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_date'        => ['required', 'date'],
            'customers_contacted' => ['required', 'integer', 'min:0'],
            'potential_customers' => ['required', 'integer', 'min:0'],
            'notes'               => ['nullable', 'string'],
        ]);

        auth('admin')->user()->followUpLogs()->create($data);


        return redirect()->route('admin.followups.index')
            ->withSuccess('Follow-up saved.');
    }

    public function show(FollowUpLog $log)
    {
        // optional: ensure the viewer owns it
        // abort_if($log->admin_id !== auth('admin')->id(), 403);

        $pageTitle = 'Follow-Up Details';
        return view('admin.followups.show', compact('log', 'pageTitle'));
    }

    public function edit(FollowUpLog $log)
    {
        $pageTitle = 'Edit Follow-Up Log';
        return view('admin.followups.edit', compact('log', 'pageTitle'));
    }

    public function update(Request $request, FollowUpLog $log)
    {
        $data = $request->validate([
            'contact_date'        => ['required', 'date'],
            'customers_contacted' => ['required', 'integer', 'min:0'],
            'potential_customers' => ['required', 'integer', 'min:0'],
            'notes'               => ['nullable', 'string'],
        ]);

        $log->update($data);

        return redirect()->route('admin.followups.index')
            ->withSuccess('Follow-up updated.');
    }

    public function destroy(FollowUpLog $log)
    {
        $log->delete();

        return redirect()->route('admin.followups.index')
            ->withSuccess('Follow-up log deleted.');
    }
}
