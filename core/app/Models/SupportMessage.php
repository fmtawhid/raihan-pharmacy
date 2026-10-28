<?php

namespace App\Models;

use App\Support\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('ecommerce_manager_website_tickets', function (Builder $query) {
            if (BranchContext::isEcommerceManagerRequest()) {
                $query->whereHas('ticket.user', function (Builder $userQuery) {
                    $userQuery->whereNull('branch_id');
                });
            }
        });
    }


    public function ticket(){
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    public function admin(){
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(SupportAttachment::class,'support_message_id','id');
    }
}
