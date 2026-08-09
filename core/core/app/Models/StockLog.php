<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'order_id', 'batch_id', 'change_quantity', 'post_quantity', 'description', 'remark'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch() 
    { 
        // If batch_id is numeric, treat as FK; otherwise it's stored batch number
        if (is_numeric($this->batch_id)) {
            return $this->belongsTo(ProductBatch::class, 'batch_id');
        }
        return null;
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
