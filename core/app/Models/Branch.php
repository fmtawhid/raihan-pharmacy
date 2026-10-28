<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'is_active', 'allow_backorder_sales'];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_backorder_sales' => 'boolean',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }
}