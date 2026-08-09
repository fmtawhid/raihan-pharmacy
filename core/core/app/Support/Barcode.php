<?php


namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;

class Barcode
{
    public static function file(string $sku, string $dir): string
    {
        // Check if finfo extension is available
        if (!extension_loaded('fileinfo')) {
            // Fallback: use direct file write without Storage facade
            return self::fileWithoutFinfo($sku, $dir);
        }

        $base64   = (new DNS1D)->getBarcodePNG($sku, 'C128'); // <- Milon gives base-64
        $binary   = base64_decode($base64);                   //    decode to real PNG
        $path     = "{$dir}/{$sku}.png";

        Storage::disk('public')->put($path, $binary);

        return $path;   //  e.g.  barcodes/products/ABC123.png
    }

    /**
     * Save barcode without using Storage facade (if finfo is not available)
     */
    private static function fileWithoutFinfo(string $sku, string $dir): string
    {
        $base64   = (new DNS1D)->getBarcodePNG($sku, 'C128');
        $binary   = base64_decode($base64);
        
        // Create directory if it doesn't exist
        $storagePath = storage_path('app/public');
        $fullDir = "{$storagePath}/{$dir}";
        
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        
        // Write file directly
        $filename = "{$sku}.png";
        $fullPath = "{$fullDir}/{$filename}";
        
        if (file_put_contents($fullPath, $binary) !== false) {
            return "{$dir}/{$filename}";
        }
        
        throw new \Exception("Failed to save barcode file: {$fullPath}");
    }
}