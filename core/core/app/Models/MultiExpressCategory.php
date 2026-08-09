<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiExpressCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'status'];

    public function deals()
    {
        return $this->hasMany(MultiExpressDeal::class, 'category_id');
    }
}
