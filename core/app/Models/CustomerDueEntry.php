<?php

namespace App\Models;

use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

class CustomerDueEntry extends Model
{
    use BranchScoped;

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'balance',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
