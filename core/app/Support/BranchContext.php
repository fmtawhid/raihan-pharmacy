<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchContext
{
    public static function isEcommerceManagerRequest(): bool
    {
        $request = request();
        if (!$request->is('admin') && !$request->is('admin/*')) {
            return false;
        }

        $admin = Auth::guard('admin')->user();

        return $admin instanceof Admin
            && $admin->isEcommerceManager()
            && !$admin->isBranchManager()
            && !$admin->isBranchSalesman();
    }

    public static function id(): ?int
    {
        $request = request();
        if ($request->attributes->has('branch_context_id')) {
            return $request->attributes->get('branch_context_id');
        }

        if (!$request->is('admin') && !$request->is('admin/*')) {
            $request->attributes->set('branch_context_id', null);
            return null;
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->branch_id) {
            $request->attributes->set('branch_context_id', null);
            return null;
        }

        $branchId = (int) $admin->branch_id;
        $request->attributes->set('branch_context_id', $branchId);
        return $branchId;
    }

    public static function ownerId(): ?int
    {
        $request = request();
        if ($request->attributes->has('branch_owner_id')) {
            return $request->attributes->get('branch_owner_id');
        }

        $branchId = self::id();
        if ($branchId !== null) {
            $request->attributes->set('branch_owner_id', $branchId);
            return $branchId;
        }

        if (($request->is('admin') || $request->is('admin/*')) && DB::getSchemaBuilder()->hasTable('branches')) {
            $mainBranchId = DB::table('branches')->where('code', 'MAIN')->value('id');
            $ownerId = $mainBranchId ? (int) $mainBranchId : null;
            $request->attributes->set('branch_owner_id', $ownerId);
            return $ownerId;
        }

        $request->attributes->set('branch_owner_id', null);
        return null;
    }
}