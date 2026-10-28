<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiExpressDeliveryOption extends Model
{
    protected $fillable = ['deal_id','type','label','charge_per_item','note'];

    public function deal() { return $this->belongsTo(MultiExpressDeal::class); }
}
