<?php

namespace App\Lib;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Comprehensive Stock Management Service
 * 
 * Handles all stock operations: deductions, batch tracking, logging, and audit trail.
 * Designed for accuracy in POS, orders, and inventory management.
 */
class StockManager
{
    /**
     * Deduct stock when product is sold (POS, Order, etc)
     * 
     * @param Product $product
     * @param int $quantity Quantity to deduct
     * @param array $options ['batch_id' => int, 'order_id' => int, 'description' => string]
     * @return array Status with 'success' => bool, 'message' => string, 'deducted' => int
     */
    public static function deductStock(Product $product, int $quantity, array $options = []): array
    {
        if ($quantity <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid quantity: must be greater than 0',
                'deducted' => 0
            ];
        }

        try {
            return DB::transaction(function () use ($product, $quantity, $options) {
                $batchId = $options['batch_id'] ?? null;
                $orderId = $options['order_id'] ?? null;
                $description = $options['description'] ?? "Product sold (qty: {$quantity})";
                $variant = $options['variant'] ?? null;

                // 1. Deduct from product in_stock (main table)
                $originalStock = $product->in_stock ?? 0;
                $newStock = max(0, $originalStock - $quantity);
                
                $product->update(['in_stock' => $newStock]);
                
                Log::info("Stock deducted from product", [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_deducted' => $quantity,
                    'original_stock' => $originalStock,
                    'new_stock' => $newStock
                ]);

                // 2. Update ProductBatch qty_sold (if batch specified)
                if ($batchId) {
                    $updateResult = ProductBatch::where('id', $batchId)->increment('qty_sold', $quantity);
                    
                    if ($updateResult) {
                        Log::info("Batch qty_sold updated", [
                            'batch_id' => $batchId,
                            'quantity_sold' => $quantity
                        ]);
                    } else {
                        Log::warning("Failed to update batch qty_sold", [
                            'batch_id' => $batchId,
                            'quantity' => $quantity
                        ]);
                    }
                }

                // 3. Create StockLog for audit trail
                try {
                    $stockLog = new StockLog();
                    $stockLog->product_id = $product->id;
                    $stockLog->product_variant_id = $variant ? $variant->id : 0;
                    $stockLog->order_id = $orderId;
                    $stockLog->batch_id = $batchId ? (string)$batchId : null;
                    $stockLog->change_quantity = $quantity;
                    $stockLog->post_quantity = $product->in_stock;
                    $stockLog->description = $description;
                    $stockLog->remark = '-'; // minus/deduction
                    $stockLog->save();

                    Log::info("Stock log created", [
                        'stock_log_id' => $stockLog->id,
                        'product_id' => $product->id,
                        'batch_id' => $batchId
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Failed to create stock log", [
                        'error' => $e->getMessage(),
                        'product_id' => $product->id
                    ]);
                }

                return [
                    'success' => true,
                    'message' => "Stock deducted successfully: -{$quantity} (was {$originalStock}, now {$newStock})",
                    'deducted' => $quantity,
                    'remaining_stock' => $newStock,
                    'batch_id' => $batchId,
                    'stock_log_created' => true
                ];
            });
        } catch (\Throwable $e) {
            Log::error("StockManager::deductStock failed", [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => "Failed to deduct stock: " . $e->getMessage(),
                'deducted' => 0
            ];
        }
    }

    /**
     * Get current available stock for a product
     * Calculates from batches if available, falls back to product.in_stock
     * 
     * @param Product $product
     * @return int Available quantity
     */
    public static function getAvailableStock(Product $product): int
    {
        // Option 1: Use batches if they exist (more accurate for batch-tracked inventory)
        $totalFromBatches = ProductBatch::where('product_id', $product->id)
            ->selectRaw('SUM(qty_received - qty_sold) as available')
            ->value('available') ?? 0;

        if ($totalFromBatches > 0) {
            return (int)$totalFromBatches;
        }

        // Option 2: Fall back to product.in_stock
        return (int)($product->in_stock ?? 0);
    }

    /**
     * Get stock summary for dashboard/reports
     * 
     * @param Product $product
     * @return array Summary data
     */
    public static function getStockSummary(Product $product): array
    {
        $batches = ProductBatch::where('product_id', $product->id)
            ->orderBy('purchased_at', 'asc')
            ->get();

        $totalReceived = $batches->sum('qty_received');
        $totalSold = $batches->sum('qty_sold');
        $availableInBatches = $totalReceived - $totalSold;

        $stockLogs = StockLog::where('product_id', $product->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'current_in_stock' => $product->in_stock,
            'available_from_batches' => $availableInBatches,
            'total_batches' => $batches->count(),
            'batches' => $batches->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'qty_received' => $batch->qty_received,
                    'qty_sold' => $batch->qty_sold,
                    'available' => $batch->qty_received - $batch->qty_sold,
                    'purchase_price' => $batch->purchase_price,
                    'purchased_at' => $batch->purchased_at
                ];
            }),
            'recent_logs' => $stockLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'quantity' => $log->change_quantity,
                    'remark' => $log->remark,
                    'description' => $log->description,
                    'created_at' => $log->created_at
                ];
            })
        ];
    }

    /**
     * Validate stock availability before sale
     * 
     * @param Product $product
     * @param int $requestedQuantity
     * @return array ['available' => bool, 'message' => string, 'available_qty' => int]
     */
    public static function validateStockAvailable(Product $product, int $requestedQuantity): array
    {
        $availableStock = self::getAvailableStock($product);

        if ($availableStock < $requestedQuantity) {
            return [
                'available' => false,
                'message' => "Insufficient stock. Requested: {$requestedQuantity}, Available: {$availableStock}",
                'available_qty' => $availableStock,
                'requested_qty' => $requestedQuantity,
                'shortfall' => $requestedQuantity - $availableStock
            ];
        }

        return [
            'available' => true,
            'message' => "Stock available",
            'available_qty' => $availableStock,
            'requested_qty' => $requestedQuantity
        ];
    }

    /**
     * Bulk deduct stock for multiple items (used in POS cart processing)
     * 
     * @param array $items [['product_id' => int, 'quantity' => int, 'batch_id' => int, ...], ...]
     * @param ?int $orderId
     * @return array Results for each item
     */
    public static function deductBulkStock(array $items, ?int $orderId = null): array
    {
        $results = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id'] ?? null);
            
            if (!$product) {
                $results[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'success' => false,
                    'message' => 'Product not found'
                ];
                continue;
            }

            $result = self::deductStock($product, (int)($item['quantity'] ?? 1), [
                'batch_id' => $item['batch_id'] ?? null,
                'order_id' => $orderId,
                'description' => $item['description'] ?? null,
                'variant' => $item['variant'] ?? null
            ]);

            $results[] = array_merge(['product_id' => $product->id], $result);
        }

        return $results;
    }
}
