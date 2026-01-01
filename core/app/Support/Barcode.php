<?php


namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;

class Barcode
{
    public static function file(string $sku, string $dir): string
    {
        $base64   = (new DNS1D)->getBarcodePNG($sku, 'C128'); // <- Milon gives base-64
        $binary   = base64_decode($base64);                   //    decode to real PNG
        $path     = "{$dir}/{$sku}.png";

        Storage::disk('public')->put($path, $binary);

        return $path;   //  e.g.  barcodes/products/ABC123.png
    }
}