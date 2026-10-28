<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeelkoSmsService;
use Illuminate\Http\Request;

class SmsPanelController extends Controller
{
    public function index()
    {
        $pageTitle = 'SMS Panel';
        return view('admin.sms_panel', compact('pageTitle'));
    }

    public function send(Request $request, DeelkoSmsService $smsService)
    {
        $validated = $request->validate([
            'mobile' => 'required|string|max:2000',
            'message' => 'required|string|max:5000',
        ]);

        $result = $smsService->send($validated['mobile'], $validated['message']);
        $notify[] = [$result['success'] ? 'success' : 'error', $result['message']];

        return back()->withInput()->withNotify($notify);
    }
}