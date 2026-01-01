<?php

namespace App\Models;

use Appp\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class FollowUpLog extends Model
{
    //
    protected $fillable = [
        'user_id',
        'contact_date',
        'customers_contacted',
        'potential_customers',
        'notes',
        'division_id',
        'district_id',
        'thana_id',
    ];

    protected $casts = ['contact_date' => 'date'];

    /* Relationships */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
