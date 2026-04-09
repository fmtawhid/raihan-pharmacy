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
        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip empty rows
                    if (empty($row['product']) && empty($row['sku'])) {
                        $this->skipCount++;
                        continue;
                    }

                    $this->importRow($row, $rowIndex + 2); // +2 for header and 1-based
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function importRow($row, $rowNumber)
    {
        // Map Excel columns to variables
        $productName = trim($row['product'] ?? '');
        $categoryName = trim($row['category'] ?? '');
        $brandName = trim($row['brand'] ?? '');
        $sku = trim($row['sku'] ?? '');
        $purchasePrice = $this->parsePrice($row['purchase'] ?? '0');
        $sellPrice = $this->parsePrice($row['sell'] ?? '0');
        $wholesalePrice = $this->parsePrice($row['wholesale'] ?? '0');
        $totalQty = $this->parseQuantity($row['total_qty'] ?? '0');
        $soldQty = $this->parseQuantity($row['sold_qty'] ?? '0');
        $remainingQty = $this->parseQuantity($row['remaining'] ?? '0');

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
            ProductBatch::create([
                'product_id' => $product->id,
                'batch_no' => $sku ?: $product->sku . '-' . date('YmdHis'),
                'purchaser_id' => $purchaser->id,
                'purchase_price' => $purchasePrice,
                'qty_received' => $totalQty,
                'qty_sold' => $soldQty,
                'purchased_at' => now(),
                'purchaser_source' => 'external',
            ]);

            $this->successCount++;
        } else {
            // Update batch quantities
            $batch->update([
                'qty_received' => $totalQty,
                'qty_sold' => $soldQty,
                'purchase_price' => $purchasePrice,
            ]);

            $this->successCount++;
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
