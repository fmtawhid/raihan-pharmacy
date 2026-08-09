<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiExpressPricingTier extends Model
{
    protected $fillable = ['deal_id','min_quantity','max_quantity','price_per_item','label'];

    public function deal() { return $this->belongsTo(MultiExpressDeal::class); }
}
