<?php

namespace App\Models;

use App\Support\BranchContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('ecommerce_manager_orders', function (Builder $query) {
            if (BranchContext::isEcommerceManagerRequest()) {
                $query->whereHas('order', function (Builder $orderQuery) {
                    $orderQuery->where('order_number', 'like', 'OID-%');
                });
            }
        });
    }

    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'order_detail_id',
        'product_id',
        'quantity_returned',
        'refund_amount',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order associated with this return
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the product associated with this return
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the order detail associated with this return
     */
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
