<?php

namespace App\Models;
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
}
