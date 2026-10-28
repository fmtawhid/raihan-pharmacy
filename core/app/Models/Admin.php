<?php

namespace App\Models;
use App\Models\Branch;
use Spatie\Permission\Traits\HasRoles;


use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasRoles;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function followUpLogs()
    {
        return $this->hasMany(\App\Models\FollowUpLog::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager');
    }

    public function isBranchSalesman(): bool
    {
        return $this->hasRole('branch_salesman');
    }

    public function isEcommerceManager(): bool
    {
        return $this->hasRole(['ecommerce_manager', 'Ecommerce Manager']);
    }
}
