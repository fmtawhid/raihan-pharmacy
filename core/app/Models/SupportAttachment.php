<?php

namespace App\Models;

use App\Support\BranchContext;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupportAttachment extends Model
{
    protected $appends = ['encrypted_id'];

    protected static function booted(): void
    {
        static::addGlobalScope('ecommerce_manager_website_tickets', function (Builder $query) {
            if (BranchContext::isEcommerceManagerRequest()) {
                $query->whereHas('supportMessage.ticket.user', function (Builder $userQuery) {
                    $userQuery->whereNull('branch_id');
                });
            }
        });
    }

    public function supportMessage()
    {
        return $this->belongsTo(SupportMessage::class,'support_message_id');
    }

    public function encryptedId(): Attribute
    {
        return new Attribute(
            get: fn () => encrypt($this->attributes['id']),
        );
    }
}
