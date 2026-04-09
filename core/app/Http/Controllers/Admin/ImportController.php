<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    /**
     * Show import products form
     */
    public function showImportForm()
    {
        $pageTitle = 'Import Products';
        return view('admin.import.products', compact('pageTitle'));
    }

    /**
     * Handle Excel file upload and import
     * POST /admin/import/products
     */
    public function importProducts(Request $request)
    {
        \Log::info('Import products request received', [
            'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'no file',
            'method' => $request->method(),
        ]);

        // Validate file
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $import = new ProductsImport();
            
            // Import the file
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $errors = $import->getErrors();

            \Log::info('Import completed', [
                'success' => $successCount,
                'skipped' => $skipCount,
                'errors' => count($errors),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Import completed! {$successCount} products imported, {$skipCount} rows skipped.",
                'success_count' => $successCount,
                'skip_count' => $skipCount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download sample Excel template
     */
    public function downloadTemplate()
    {
        $file = storage_path('app/public/product_import_template.xlsx');

        if (!file_exists($file)) {
            $this->generateTemplate();
        }

        return response()->download($file, 'product_import_template.xlsx');
    }

    /**
     * Generate sample template
     */
    protected function generateTemplate()
    {
        $data = [
            [
                'Sl' => 1,
                'Product' => 'Product Name',
                'Category' => 'Category Name',
                'Brand' => 'Brand Name',
                'SKU' => 'SKU123',
                'Purchase' => '10BDT',
                'Sell' => '15BDT',
                'Wholesale' => '12BDT',
                'Total Qty' => '100 Pcs',
                'Sold Qty' => '50 Pcs',
                'Remaining' => '50 Pcs',
            ],
            [
                'Sl' => 2,
                'Product' => 'Another Product',
                'Category' => 'Another Category',
                'Brand' => 'Another Brand',
                'SKU' => 'SKU456',
                'Purchase' => '5BDT',
                'Sell' => '8BDT',
                'Wholesale' => '7BDT',
                'Total Qty' => '200 Pcs',
                'Sold Qty' => '100 Pcs',
                'Remaining' => '100 Pcs',
            ],
        ];

        // Create Excel file using maatwebsite/excel
        Excel::store(new class {
            public function collection() {
                return collect($data);
            }
        }, 'product_import_template.xlsx', 'public');
    }
}
