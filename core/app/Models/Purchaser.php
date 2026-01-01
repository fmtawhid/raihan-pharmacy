<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchaser extends Model
{
    //

     protected $fillable = ['name','email','phone'];

    const SELF_ID = 1;

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public static function selfId()
    {
        return self::SELF_ID;
    }
}
