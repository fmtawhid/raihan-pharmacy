<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchaser;
use Illuminate\Http\Request;

class PurchaserController extends Controller
{
    //

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $purchaser = Purchaser::create($data);

        // return id so the JS can replace "new" with the real one
        return response()->json(['id' => $purchaser->id]);
    }

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
