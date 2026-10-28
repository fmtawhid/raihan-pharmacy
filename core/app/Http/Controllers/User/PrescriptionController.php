<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\PrescriptionUpload;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index()
    {
        $pageTitle = 'Upload Prescription';
        $prescriptions = PrescriptionUpload::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('Template::user.prescription', compact('pageTitle', 'prescriptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prescription' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png,webp', 'max:5120'],
            'note'         => ['nullable', 'string', 'max:500'],
        ], [
            'prescription.mimes' => 'Only PDF, JPEG, JPG, PNG, and WEBP files are allowed.',
            'prescription.max'   => 'File size must not exceed 5 MB.',
        ]);

        $file          = $request->file('prescription');
        $ext           = $file->getClientOriginalExtension();
        $originalName  = $file->getClientOriginalName();
        $fileName      = uniqid('rx_') . '_' . time() . '.' . $ext;

        $path = 'prescriptions/' . auth()->id();
        $file->storeAs($path, $fileName, 'public');

        PrescriptionUpload::create([
            'user_id'       => auth()->id(),
            'original_name' => $originalName,
            'file_path'     => $path . '/' . $fileName,
            'file_extension'=> $ext,
            'note'          => $request->note,
        ]);

        // Admin notification
        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->id();
        $adminNotification->title     = auth()->user()->fullname . ' uploaded a new prescription';
        $adminNotification->click_url = urlPath('admin.users.detail', auth()->id());
        $adminNotification->save();

        $notify[] = ['success', 'Prescription uploaded successfully.'];
        return back()->withNotify($notify);
    }

    public function destroy($id)
    {
        $prescription = PrescriptionUpload::where('user_id', auth()->id())->findOrFail($id);

        $fullPath = storage_path('app/public/' . $prescription->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $prescription->delete();

        $notify[] = ['success', 'Prescription deleted successfully.'];
        return back()->withNotify($notify);
    }
}
