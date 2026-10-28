<?php

namespace App\Models;

use App\Constants\Status;
use App\Support\BranchContext;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\followUpLog;
use App\Traits\BranchScoped;

class User extends Authenticatable
{
    use HasApiTokens, UserNotify, BranchScoped;

    protected static function booted(): void
    {
        static::addGlobalScope('ecommerce_manager_website_users', function (Builder $query) {
            if (BranchContext::isEcommerceManagerRequest()) {
                $query->whereNull($query->getModel()->qualifyColumn('branch_id'));
            }
        });
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ver_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ver_code_send_at' => 'datetime'
    ];
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'email',
        'password',
        'mobile',
        'dial_code',
        'address',
        'status',
        'ev',
        'sv',
        'profile_complete',
        'branch_id',
    ];
    public function appliedCoupons()
    {
        return $this->hasMany(AppliedCoupon::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn() => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function mobileNumber(): Attribute
    {
        return new Attribute(
            get: fn() => $this->dial_code . $this->mobile,
        );
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query)
    {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        return $query->where('sv', Status::VERIFIED);
    }

    public function scopeProfileCompleted($query)
    {
        return $query->where('profile_complete', Status::YES);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function followUpLogs()
    {
        return $this->hasMany(FollowUpLog::class);
    }
}
