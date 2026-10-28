<?php

namespace App\Models;

use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BranchScoped;

    protected $fillable = [
        'name', 'company', 'contact_number', 'email',
        'division_id', 'district_id', 'thana_id','area_name', 'postcode','remarks', 'branch_id'
    ];

    /** Accessor for "Division > District > Thana" */
    // public function getAddressAttribute(): string
    // {
    //     return "{$this->division->name} > {$this->district->name} > {$this->thana->name}";
    // }

    // If you’ve got tables for divisions/districts/thanas already:
    // public function division() { return $this->belongsTo(Division::class); }
    // public function district() { return $this->belongsTo(District::class); }
    // public function thana()    { return $this->belongsTo(Thana::class); }

}
