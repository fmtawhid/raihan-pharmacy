<?php

namespace App\Models;

use App\Support\Sku;
use App\Support\Barcode;
use Illuminate\Support\Facades\Storage;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class ProductVariant extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'attribute_values' => 'array',
        'sale_price'         => 'double',
        'regular_price'      => 'double',
        'sale_starts_from'   => 'datetime',
        'sale_ends_at'       => 'datetime',
    ];

    public function digitalFile()
    {
        return $this->morphOne(DigitalFile::class, 'fileable');
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class, 'stock_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::creating(function ($v) {
            if ($v->sku) return;      // admin typed one

            $parentSku = $v->product->sku;
            $suffix    = Sku::suffix($v->attribute_values);
            $candidate = "{$parentSku}-{$suffix}";

            $i = 1;
            $sku = $candidate;
            while (self::where('sku', $sku)->exists()) {
                $sku = "{$candidate}-{$i}";
                $i++;
            }
            $v->sku = $sku;
        });

        /* REPLACE the previous “static::saving” with this ↓ */

        static::saving(function ($v) {

            $needsNewFile = $v->isDirty('sku') || !$v->barcode_path;

            if ($needsNewFile) {
                // delete any stale file
                if ($v->getOriginal('barcode_path')) {
                    Storage::disk('public')->delete($v->getOriginal('barcode_path'));
                }

                // write fresh PNG + set path
                $v->barcode_path = Barcode::file(
                    $v->sku,
                    'barcodes/variants'
                );
            }
        });
    }

    public function displayImage()
    {
        return $this->belongsTo(Media::class,  'main_image_id');
    }

    public function galleryImages()
    {
        return $this->belongsToMany(Media::class);
    }

    public function mainImage($thumb = true)
    {
        $image = $this->displayImage;
        $thumb = $thumb ? '/thumb_' : '/';

        if (!$image) {
            return getImage(null);
        }
        return getImage($image->path . $thumb . $image->file_name);
    }

    public function salePrice()
    {
        return productPriceManager()->getOnSalePrice($this);
    }

    public function formattedPrice($product = null)
    {
        $productManager = productPriceManager();
        return $productManager->getFormattedPrice($this->regular_price, $this->salePrice());
    }

    public function inStock($product = null)
    {
        if ($this->manage_stock) {
            return $this->in_stock;
        }
        $product = $product ?? $this->product;
        return $product->in_stock;
    }

    public function trackInventory($product = null)
    {
        if ($this->manage_stock) {
            return $this->track_inventory;
        }
        $product = $product ?? $this->product;
        return $product->track_inventory;
    }

    public function scopePublished($query)
    {
        return $query->where('product_variants.is_published', Status::YES);
    }

    public function batches() { return $this->hasMany(ProductBatch::class,'variant_id'); }


    protected static function boot()
    {
        parent::boot();

        static::saved(function ($variant) {
            Cache::forget("product_{$variant->product_id}_price_range");
        });

        static::deleted(function ($variant) {
            Cache::forget("product_{$variant->product_id}_price_range");
        });
    }
}
