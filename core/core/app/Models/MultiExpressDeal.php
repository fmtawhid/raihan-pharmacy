<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiExpressDeal extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'short_description', 'description',
        'regular_price', 'deal_price', 'discount_percent', 'min_required', 'max_capacity',
        'purchase_limit_per_user', 'stock_status', 'delivery_start_date', 'delivery_end_date',
        'delivery_note', 'status', 'feature_image', 'deal_start_time', 'deal_end_time'
    ];


    public function category() { return $this->belongsTo(MultiExpressCategory::class); }
    public function pricingTiers()
    {
        return $this->hasMany(MultiExpressPricingTier::class, 'deal_id');
    }

    public function deliveryOptions()
    {
        return $this->hasMany(MultiExpressDeliveryOption::class, 'deal_id');
    }


    public function orders()
    {
        return $this->hasMany(MultiExpressOrder::class, 'deal_id');
    }



}
