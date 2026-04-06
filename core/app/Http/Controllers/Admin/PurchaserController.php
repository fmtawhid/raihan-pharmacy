<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchaser;
use Illuminate\Http\Request;

class PurchaserController extends Controller
{
    // List all purchasers with search and filtering
    public function index(Request $request)
    {
        $pageTitle = 'Suppliers';
        $query = Purchaser::query();

        // Search by name, email, or phone
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort by created date (newest first)
        $query->orderBy('created_at', 'desc');

        $purchasers = $query->paginate(20);

        return view('admin.purchaser.index', compact('purchasers', 'pageTitle'));
    }

    // Store new purchaser
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $purchaser = Purchaser::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Purchaser added successfully']);
        }

        return redirect()->route('admin.purchasers.index')
            ->with('success', 'Purchaser added successfully');
    }

    // Update purchaser
    public function update(Request $request, Purchaser $purchaser)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $purchaser->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Purchaser updated successfully']);
        }

        return redirect()->route('admin.purchasers.index')
            ->with('success', 'Purchaser updated successfully');
    }

    // Show edit form
    public function edit(Purchaser $purchaser)
    {
        $pageTitle = 'Edit Supplier';
        return view('admin.purchaser.edit', compact('purchaser', 'pageTitle'));
    }

    // Delete purchaser
    public function destroy(Purchaser $purchaser)
    {
        $purchaser->delete();

        return redirect()->route('admin.purchasers.index')
            ->with('success', 'Purchaser deleted successfully');
    }

    // Select2 dropdown search (for AJAX)
    public function select2(Request $r)
    {
        $q = $r->get('q', '');              // Select2 sends ?q=term
        $items = Purchaser::when(
            $q,
            fn($qry) =>
            $qry->where('name', 'like', "%{$q}%")
        )
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json([
            'results' => $items->map(fn($row) => [
                'id'   => $row->id,
                'text' => $row->name,
            ]),
        ]);
    }
}
