<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function handle($request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();
        if (!($admin instanceof Admin) || ($admin->id !== 1 && !$admin->hasRole('super_admin'))) {
            abort(403, 'Only a super admin can manage branches.');
        }

        return $next($request);
    }
}