<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'quantity', 'price', 'discount', 'purchase_price', 'batch_id', 'profit'];

    public function order()
    {
        return $this->belongsTo(Order::class)->withoutGlobalScope('notPaid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function digitalFile()
    {
        return $this->morphOne(DigitalFile::class, 'fileable');
    }
}
