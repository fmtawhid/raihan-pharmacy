<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    //

    protected $fillable = [
        'product_id',
        'variant_id',
        'batch_no',
        'purchaser_id',
        'purchase_price',
        'purchaser_source',
        'qty_received',
        'qty_sold',
        'purchased_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function purchaser()
    {
        return $this->belongsTo(Purchaser::class);
    }
    public function isSelfSource(): bool
    {
        return $this->purchaser_source === 'self';
    }

    public function isExternalSource(): bool
    {
        return $this->purchaser_source === 'external';
    }

    public function inStock(): int
    {
        return $this->qty_received - $this->qty_sold;
    }
}
