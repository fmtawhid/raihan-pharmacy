<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiExpressOrder extends Model
{
    protected $fillable = [
        'deal_id','user_id','name','contact_no','email','address','quantity',
        'price_per_item',  // <-- THIS WAS MISSING
        'payment_type','payment_status','delivery_type','delivery_status',
        'total_price','delivery_option_id'
    ];


    // Deal relation
    public function deal() { 
        return $this->belongsTo(MultiExpressDeal::class); 
    }

    // User relation
    public function user() { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    // Delivery option relation
    public function deliveryOption() { 
        return $this->belongsTo(MultiExpressDeliveryOption::class, 'delivery_option_id'); 
    }

    public function payments()
    {
        return $this->hasMany(MultiExpressOrderPayment::class, 'order_id');
    }

    // Helper function to get total paid
    public function totalPaid()
    {
        return $this->payments()->where('status','paid')->sum('amount');
    }

    // Helper function to count number of payments
    public function paymentCount()
    {
        return $this->payments()->where('status','paid')->count();
    }

}
