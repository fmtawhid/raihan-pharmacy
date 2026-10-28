<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyFollowUpSummary extends Model
{
    protected $fillable = [
        'month', 'admin_id', 'contacted_total', 'potential_total','summary_note'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
