<?php

namespace App\Models;

use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use BranchScoped;

    protected $fillable = [
        'admin_id',
        'user_id',
        'recipient_number',
        'message',
        'status',
        'source',
        'response_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
