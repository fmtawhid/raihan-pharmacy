<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\Batch;
use App\Models\Purchaser;
use App\Models\ProductBatch;
use App\Lib\ProductManager;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    private $productManager;

    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    // ── ADD STOCK PAGE ──────────────────
    public function addStock()
    {
        $pageTitle = 'Add Stock';
        $purchasers = Purchaser::orderBy('name')->get();
        return view('admin.product.add_stock', compact('pageTitle', 'purchasers'));
    }

    // ── GET PRODUCTS FOR ADD STOCK ──────────────────
    public function getProductsStock(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 100);
        $offset = ($page - 1) * $perPage;

        $totalProducts = Product::count();
        $totalPages = ceil($totalProducts / $perPage);

        $products = Product::offset($offset)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'sku'   => $p->sku,
                    'regular_price' => $p->regular_price ?? null,
                    'sale_price' => $p->sale_price ?? null,
                    'wholesale_price' => $p->wholesale_price ?? null,
                    'in_stock' => $p->in_stock ?? 0,
                    'stock' => $p->in_stock ?? 0,
                ];
            });

        return response()->json([
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalProducts,
                'per_page' => $perPage
            ]
        ]);
    }

    // ── SAVE STOCK PURCHASE ──────────────────
    public function saveStockPurchase(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $purchaserId = $request->input('purchaser_id');
            $batchNo = $request->input('batch_no');
            $purchasedAt = $request->input('purchased_at', now());

            if (empty($items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add at least one product'
                ], 422);
            }

            $totalQuantity = 0;
            $totalCost = 0;

            // Validate and process each item
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['quantity']) || !isset($item['purchase_price'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid item data'
                    ], 422);
                }

                $product = Product::find($item['id']);
                if (!$product) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Product not found: ID ' . $item['id']
                    ], 404);
                }

                $quantity = (int) $item['quantity'];
                $purchasePrice = (float) $item['purchase_price'];

                if ($quantity <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Quantity must be greater than 0'
                    ], 422);
                }

                if ($purchasePrice < 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Purchase price cannot be negative'
                    ], 422);
                }

                $totalQuantity += $quantity;
                $totalCost += $quantity * $purchasePrice;

                // Create or update batch
                $this->productManager->receiveStock(
                    $product,
                    null,
                    [
                        'batch_no'       => $batchNo,
                        'purchaser_id'   => $purchaserId,
                        'purchase_price' => $purchasePrice,
                        'quantity'       => $quantity,
                        'purchased_at'   => $purchasedAt,
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock added successfully',
                'data' => [
                    'total_items' => count($items),
                    'total_quantity' => $totalQuantity,
                    'total_cost' => $totalCost
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Add Stock Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stockLogByProduct($id)
    {
        $product = Product::findOrFail($id);
        $logs = StockLog::where('product_id', $product->id)->dateFilter()->with('order:id,order_number')->orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = 'Stock Log 0f ' . $product->name;

        return view('admin.product.stock_log', compact('logs', 'pageTitle'));
    }

    public function stockLogByVariant($id)
    {
        $productVariant = ProductVariant::with('product:id,name')->findOrFail($id);
        $logs = StockLog::where('product_variant_id', $productVariant->id)->dateFilter()->with('order:id,order_number')->orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = 'Stock Log 0f ' . $productVariant->product->name . ' (' . $productVariant->name . ')';

        return view('admin.product.stock_log', compact('logs', 'pageTitle'));
    }
    public function stockLogList(Request $request)
    {
        $logs = StockLog::dateFilter()->with('order:id,order_number')->orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = 'Stock Log List';

        return view('admin.product.stock_log', compact('logs', 'pageTitle'));
    }

    // ── PURCHASE LIST BY BATCH ──────────────────
    public function purchasesList(Request $request)
    {
        $query = ProductBatch::selectRaw('batch_no, purchaser_id, SUM(qty_received) as total_qty_received, COUNT(DISTINCT product_id) as product_count, MAX(id) as id, MAX(purchased_at) as purchased_at, SUM(qty_received * purchase_price) as total_purchase_amount')
            ->with(['purchaser:id,name'])
            ->groupBy('batch_no', 'purchaser_id')
            ->orderBy('purchased_at', 'desc');

        // Search by batch number or product name
        if ($request->has('q') && $request->input('q')) {
            $searchTerm = $request->input('q');
            $query->where('batch_no', 'like', "%{$searchTerm}%");
        }

        // Filter by purchaser if provided
        if ($request->has('purchaser_id') && $request->input('purchaser_id')) {
            $query->where('purchaser_id', $request->input('purchaser_id'));
        }

        // Filter by date range if provided
        if ($request->has('date_from') && $request->input('date_from')) {
            $query->whereDate('purchased_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to') && $request->input('date_to')) {
            $query->whereDate('purchased_at', '<=', $request->input('date_to'));
        }

        $purchases = $query->paginate(getPaginate());
        $pageTitle = 'Stock Purchase List';

        // Get purchasers for filter dropdown
        $purchasers = Purchaser::orderBy('name')->get();

        return view('admin.product.purchases_list', compact('purchases', 'pageTitle', 'purchasers'));
    }

    // ── BATCH DETAILS ──────────────────
    public function batchDetails($batchId)
    {
        $batch = ProductBatch::with(['product:id,name,sku', 'purchaser:id,name', 'variant'])->findOrFail($batchId);
        
        // Get all products in this batch (by batch_no)
        $batchItems = ProductBatch::where('batch_no', $batch->batch_no)
            ->with(['product:id,name,sku', 'variant'])
            ->get();
        
        $pageTitle = 'Batch Details - ' . $batch->batch_no;

        return view('admin.product.batch_details', compact('batch', 'batchItems', 'pageTitle'));
    }

    // ── EDIT BATCH ──────────────────
    public function editBatch($batchId)
    {
        $batch = ProductBatch::with(['product:id,name,sku', 'purchaser:id,name', 'variant'])->findOrFail($batchId);
        
        // Get all products in this batch (by batch_no)
        $batchItems = ProductBatch::where('batch_no', $batch->batch_no)
            ->with(['product:id,name,sku', 'variant'])
            ->get();
        
        $purchasers = Purchaser::orderBy('name')->get();
        $pageTitle = 'Edit Batch - ' . $batch->batch_no;

        return view('admin.product.batch_edit', compact('batch', 'batchItems', 'purchasers', 'pageTitle'));
    }

    // ── UPDATE BATCH ──────────────────
    public function updateBatch(Request $request, $batchId)
    {
        try {
            $batch = ProductBatch::findOrFail($batchId);
            
            $request->validate([
                'purchaser_id' => 'required|exists:purchasers,id',
                'purchase_price' => 'required|numeric|min:0',
            ]);

            $batch->update([
                'purchaser_id' => $request->input('purchaser_id'),
                'purchase_price' => $request->input('purchase_price'),
            ]);

            $notification = array(
                'message' => 'Batch updated successfully',
                'alert-type' => 'success'
            );

            return back()->with($notification);

        } catch (\Exception $e) {
            \Log::error('Update Batch Error: ' . $e->getMessage());
            $notification = array(
                'message' => 'Failed to update batch: ' . $e->getMessage(),
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
    }

    // ── DELETE BATCH ──────────────────
    public function deleteBatch(Request $request, $batchId)
    {
        try {
            $batch = ProductBatch::findOrFail($batchId);
            $batchNo = $batch->batch_no;
            
            // Delete all items in this batch (by batch_no)
            ProductBatch::where('batch_no', $batchNo)->delete();

            // Create stock log for deletion
            StockLog::create([
                'product_id' => $batch->product_id,
                'product_variant_id' => $batch->variant_id,
                'quantity' => -$batch->qty_received,
                'unit' => 'quantity',
                'message' => "Batch {$batchNo} deleted",
                'type' => 'decrease',
            ]);

            $notification = array(
                'message' => 'Batch deleted successfully',
                'alert-type' => 'success'
            );

            return redirect(route('admin.products.purchases.list'))->with($notification);

        } catch (\Exception $e) {
            \Log::error('Delete Batch Error: ' . $e->getMessage());
            $notification = array(
                'message' => 'Failed to delete batch: ' . $e->getMessage(),
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
    }
}
