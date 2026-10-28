<?php

namespace App\Traits;

use App\Support\BranchContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BranchScoped
{
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch', function (Builder $query) {
            $branchId = BranchContext::id();
            if ($branchId !== null) {
                $query->where($query->getModel()->qualifyColumn('branch_id'), $branchId);
            }
        });

        static::creating(function (Model $model) {
            $branchId = BranchContext::ownerId();
            if ($branchId !== null && !$model->getAttribute('branch_id')) {
                $model->setAttribute('branch_id', $branchId);
            }
        });
    }
}