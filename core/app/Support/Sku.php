<?php

namespace App\Support;

use Illuminate\Support\Str;
use App\Models\Product;

class Sku
{
    /** Parent product code – e.g. ABCDE‑472 */
    public static function base(): string
    {
        do {
            $sku = strtoupper(Str::random(5)).'-'.rand(100,999);
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /** Suffix for a variant – e.g. RED‑M */
    public static function suffix(string $attributeJson): string
    {
        $pairs = json_decode($attributeJson, true) ?? [];
        return collect($pairs)->map(fn($v) => strtoupper(Str::slug($v, '')))->implode('-');
    }
}
