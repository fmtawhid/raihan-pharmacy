<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchaser;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Handle inbound inventory (“Receive Stock” modal).
     *
     * POST /admin/stock/receive
     */
    public function receive(Request $r)
    {
        // ── 1. Validate ─────────────────────────────────────────────────────
        $r->validate([
            'product_id'     => 'required|exists:products,id',
            'variant_id'     => 'nullable|exists:product_variants,id',

            'batch_no'       => 'required|string|max:40',
            'purchaser_id'   => 'required',          // may be "new"
            'new_purchaser'  => 'nullable|string|max:255',

            'purchase_price' => 'nullable|numeric|min:0',
            'quantity'       => 'required|integer|min:1',
            'purchased_at'   => 'nullable|date',
        ]);

        // ── 2. Resolve / create purchaser ───────────────────────────────────
        $purchaserId = $r->purchaser_id;

        if ($purchaserId === 'new') {
            // Create the vendor row on the fly
            $purchaser = Purchaser::create([
                'name'  => trim($r->new_purchaser),
                'email' => null,
                'phone' => null,
            ]);
            $purchaserId = $purchaser->id;
        }

        // ── 3. Load product / variant ───────────────────────────────────────
        $product = Product::findOrFail($r->product_id);
        $variant = $r->variant_id ? ProductVariant::findOrFail($r->variant_id) : null;

        // ── 4. Pass everything to ProductManager ---------------------------
        $data = $r->all();
        $data['purchaser_id'] = $purchaserId;   // overwrite if “new”

        productManager()->receiveStock($product, $variant, $data);

        // ── 5. Done ---------------------------------------------------------
        return response()->json(['status' => 'success']);
    }
}
