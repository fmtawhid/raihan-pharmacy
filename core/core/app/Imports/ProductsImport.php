<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchaser;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;

    public function collection(Collection $rows)
    {
        \Log::info('🚀 Starting Excel import', ['total_rows' => $rows->count()]);
        
        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip completely empty rows
                    if (collect($row)->filter(fn($v) => !empty($v))->isEmpty()) {
                        \Log::info("⏩ Skipping empty row " . ($rowIndex + 2));
                        $this->skipCount++;
                        continue;
                    }

                    $this->importRow($row, $rowIndex + 2); // +2 for header and 1-based
                } catch (\Exception $e) {
                    $errorMsg = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $this->errors[] = $errorMsg;
                    \Log::error("❌ Row import failed", ['error' => $errorMsg, 'trace' => $e->getTraceAsString()]);
                }
            }

            DB::commit();
            \Log::info('✅ Import committed to database', [
                'success' => $this->successCount,
                'skipped' => $this->skipCount,
                'errors' => count($this->errors)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Import transaction failed, rolled back', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function importRow($row, $rowNumber)
    {
        // Make all keys lowercase for flexible matching
        $rowLower = collect($row)->mapWithKeys(fn($v, $k) => [strtolower(str_replace(' ', '_', $k)) => $v])->toArray();
        
        // Map Excel columns to variables with fallback options
        $productName = trim($rowLower['product'] ?? $rowLower['product_name'] ?? $rowLower['name'] ?? '');
        $categoryName = trim($rowLower['category'] ?? $rowLower['category_name'] ?? '');
        $brandName = trim($rowLower['brand'] ?? $rowLower['brand_name'] ?? '');
        $sku = trim($rowLower['sku'] ?? $rowLower['code'] ?? '');
        $purchasePrice = $this->parsePrice($rowLower['purchase'] ?? $rowLower['purchase_price'] ?? '0');
        $sellPrice = $this->parsePrice($rowLower['sell'] ?? $rowLower['sell_price'] ?? $rowLower['sale_price'] ?? '0');
        $wholesalePrice = $this->parsePrice($rowLower['wholesale'] ?? $rowLower['wholesale_price'] ?? '0');
        $totalQty = $this->parseQuantity($rowLower['total_qty'] ?? $rowLower['qty'] ?? $rowLower['quantity'] ?? $rowLower['total'] ?? '0');
        $soldQty = $this->parseQuantity($rowLower['sold_qty'] ?? $rowLower['sold'] ?? '0');
        $remainingQty = $this->parseQuantity($rowLower['remaining'] ?? $rowLower['stock'] ?? $rowLower['remaining_qty'] ?? '0');
        
        \Log::info("📋 Row {$rowNumber} data extracted", [
            'product' => $productName,
            'category' => $categoryName,
            'brand' => $brandName,
            'sku' => $sku,
            'purchase_price' => $purchasePrice,
            'sell_price' => $sellPrice,
            'total_qty' => $totalQty,
            'sold_qty' => $soldQty
        ]);

        // Validation
        if (empty($productName)) {
            throw new \Exception("Product name is required");
        }

        if ($totalQty <= 0) {
            throw new \Exception("Total Qty must be greater than 0");
        }

        // Calculate remaining if not provided
        if ($remainingQty === 0 && $soldQty > 0) {
            $remainingQty = $totalQty - $soldQty;
        } elseif ($remainingQty === 0) {
            $remainingQty = $totalQty;
        }

        // Get or create category
        $category = null;
        if (!empty($categoryName)) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => \Str::slug($categoryName), 'status' => 1]
            );
        }

        // Get or create brand
        $brand = null;
        if (!empty($brandName)) {
            $brand = Brand::firstOrCreate(
                ['name' => $brandName],
                ['logo' => null, 'status' => 1]
            );
        }

        // Check if product exists by SKU
        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            // Create new product
            try {
                $product = Product::create([
                    'name' => $productName,
                    'slug' => \Str::slug($productName) . '-' . time(),
                    'sku' => $sku ?: \Str::random(12),
                    'regular_price' => $sellPrice > 0 ? $sellPrice : $purchasePrice,
                    'sale_price' => $sellPrice > 0 ? $sellPrice : $purchasePrice,
                    'wholesale_price' => $wholesalePrice > 0 ? $wholesalePrice : $purchasePrice,
                    'purchase_price' => $purchasePrice,
                    'description' => '',
                    'status' => 1,
                ]);
                
                \Log::info("✅ Product created", [
                    'product_id' => $product->id,
                    'name' => $productName,
                    'sku' => $product->sku
                ]);
            } catch (\Exception $e) {
                \Log::error("❌ Failed to create product", [
                    'name' => $productName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception("Failed to create product: " . $e->getMessage());
            }

            // Attach category if exists
            if ($category) {
                $product->categories()->attach($category->id);
            }

            // Attach brand if exists
            if ($brand) {
                $product->update(['brand_id' => $brand->id]);
            }
        } else {
            // Update existing product
            $product->update([
                'name' => $productName,
                'regular_price' => $sellPrice > 0 ? $sellPrice : $purchasePrice,
                'sale_price' => $sellPrice > 0 ? $sellPrice : $purchasePrice,
                'wholesale_price' => $wholesalePrice > 0 ? $wholesalePrice : $purchasePrice,
                'purchase_price' => $purchasePrice,
            ]);

            if ($category && !$product->categories->contains($category)) {
                $product->categories()->attach($category->id);
            }

            if ($brand) {
                $product->update(['brand_id' => $brand->id]);
            }
        }

        // Create or update batch
        // First, try to find existing batch with same SKU
        $batch = ProductBatch::where('product_id', $product->id)
            ->where('batch_no', $sku ?: $product->sku)
            ->first();

        // Default purchaser (will be "Import" or user can specify)
        $purchaser = Purchaser::firstOrCreate(
            ['name' => 'Import Source'],
            ['email' => null, 'phone' => null]
        );

        if (!$batch) {
            // Create new batch
            try {
                $newBatch = ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_no' => $sku ?: $product->sku . '-' . date('YmdHis'),
                    'purchaser_id' => $purchaser->id,
                    'purchase_price' => $purchasePrice,
                    'qty_received' => $totalQty,
                    'qty_sold' => $soldQty,
                    'purchased_at' => now(),
                    'purchaser_source' => 'external',
                ]);
                
                \Log::info("✅ ProductBatch created", [
                    'batch_id' => $newBatch->id,
                    'product_id' => $product->id,
                    'qty_received' => $totalQty,
                    'qty_sold' => $soldQty
                ]);
                
                $this->successCount++;
            } catch (\Exception $e) {
                \Log::error("❌ Failed to create batch", [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception("Failed to create batch: " . $e->getMessage());
            }
        } else {
            // Update batch quantities
            try {
                $batch->update([
                    'qty_received' => $totalQty,
                    'qty_sold' => $soldQty,
                    'purchase_price' => $purchasePrice,
                ]);
                
                \Log::info("✅ ProductBatch updated", [
                    'batch_id' => $batch->id,
                    'qty_received' => $totalQty,
                    'qty_sold' => $soldQty
                ]);
                
                $this->successCount++;
            } catch (\Exception $e) {
                \Log::error("❌ Failed to update batch", [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception("Failed to update batch: " . $e->getMessage());
            }
        }

        \Log::info("Product imported: {$productName} (SKU: {$sku}) - Qty: {$totalQty}, Sold: {$soldQty}");
    }

    /**
     * Parse price from string (handles "12BDT", "0.72BDT", etc.)
     */
    protected function parsePrice($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove currency text and extract number
        $value = preg_replace('/[^\d.]/', '', (string) $value);
        return (float) ($value ?: 0);
    }

    /**
     * Parse quantity from string (handles "232 Pcs", "1100 Pcs", etc.)
     */
    protected function parseQuantity($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove non-numeric characters except decimal
        $value = preg_replace('/[^\d]/', '', (string) $value);
        return (int) ($value ?: 0);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkipCount()
    {
        return $this->skipCount;
    }
}
