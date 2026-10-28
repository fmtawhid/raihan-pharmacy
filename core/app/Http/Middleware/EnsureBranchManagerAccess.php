<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Admin;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureBranchManagerAccess
{
    private const MANAGER_ROUTES = [
        'admin.login', 'admin.logout', 'admin.dashboard',
        'admin.profile', 'admin.profile.update', 'admin.password', 'admin.password.update',
        'admin.print.invoice',
        'admin.pos.*', 'admin.users.*', 'admin.customers.*', 'admin.purchasers.*',
        'admin.category.all', 'admin.brand.all', 'admin.attribute.all', 'admin.product.type.all',
        'admin.products.all', 'admin.products.add.stock', 'admin.products.get.products.stock',
        'admin.products.save.stock.purchase', 'admin.products.stock.log.list', 'admin.products.purchases.list',
        'admin.products.expiry.alerts',
        'admin.products.batch.*', 'admin.products.stock.log', 'admin.products.stock.log.variant',
        'admin.order.index', 'admin.order.pending', 'admin.order.processing', 'admin.order.dispatched',
        'admin.order.delivered', 'admin.order.canceled', 'admin.order.returned', 'admin.order.cod',
        'admin.order.details', 'admin.order.status.change', 'admin.order.status.cancel', 'admin.order.status_counts',
        'admin.order.add_return', 'admin.order.search', 'admin.order.return.details', 'admin.order.save.return',
        'admin.order.returns.list', 'admin.order.returns.export', 'admin.order.return.delete', 'admin.order.return',
        'admin.deposit.*',
        'admin.sms.panel.*',
        'admin.report.sales', 'admin.report.purchase', 'admin.report.return', 'admin.report.business',
        'admin.reports.sales.csv', 'admin.reports.business.csv',
        'admin.branch-salesmen.*',
    ];

    private const SALESMAN_ROUTES = [
        'admin.login', 'admin.logout', 'admin.dashboard',
        'admin.profile', 'admin.profile.update', 'admin.password', 'admin.password.update',
        'admin.pos.*', 'admin.order.index', 'admin.order.details', 'admin.order.status_counts',
        'admin.print.invoice',
    ];

    private const ECOMMERCE_MANAGER_ROUTES = [
        'admin.login', 'admin.logout', 'admin.dashboard',
        'admin.profile', 'admin.profile.update', 'admin.password', 'admin.password.update',
        'admin.print.invoice',
        'admin.notification.read',
        'admin.sms.panel.*',
        'admin.users.*',
        'admin.category.*', 'admin.brand.*', 'admin.attribute.*', 'admin.product.type.*',
        'admin.products.*', 'admin.media*',
        'admin.promo.banner.*', 'admin.coupon.*', 'admin.offer.*',
        'admin.order.*', 'admin.deposit.*', 'admin.ticket.*',
        'admin.setting.system', 'admin.setting.general', 'admin.setting.system.configuration',
        'admin.setting.notification.global.email*', 'admin.shipping.methods.*',
        'admin.frontend.*', 'admin.menu.builder.*', 'admin.seo',
        'admin.setting.logo.icon', 'admin.setting.socialite.credentials*',
        'admin.language.*', 'admin.maintenance.mode',
        'admin.setting.cookie', 'admin.setting.popup.banner', 'admin.setting.custom.css',
        'admin.setting.sitemap', 'admin.setting.robot',
        'admin.report.sales', 'admin.report.business',
        'admin.report.purchase', 'admin.report.return',
        'admin.reports.sales.csv', 'admin.reports.business.csv',
    ];

    private const ECOMMERCE_MANAGER_POST_PATHS = [
        'admin/general-setting',
        'admin/setting/system-configuration',
        'admin/frontend/frontend-element-seo/*/*',
        'admin/frontend/manage-seo/*',
        'admin/custom-css',
        'admin/sitemap',
        'admin/robot',
        'admin/cookie',
        'admin/popup-banner',
        'admin/maintenance-mode',
    ];

    public function handle($request, Closure $next)
    {
        if (!$request->is('admin') && !$request->is('admin/*')) {
            return $next($request);
        }

        $admin = Auth::guard('admin')->user();
        if (!($admin instanceof Admin)) {
            return $next($request);
        }

        $isBranchManager = $admin->hasRole('branch_manager');
        $isBranchSalesman = $admin->hasRole('branch_salesman');
        $isEcommerceManager = $admin->isEcommerceManager()
            && !$isBranchManager
            && !$isBranchSalesman;
        if (!$isBranchManager && !$isBranchSalesman && !$isEcommerceManager) {
            return $next($request);
        }

        if ($isEcommerceManager) {
            if ($request->routeIs('admin.users.login')) {
                abort(403, 'Ecommerce managers cannot sign in as customers.');
            }
            if ($request->routeIs('admin.users.due.bills*', 'admin.users.pos.*')) {
                abort(403, 'Ecommerce managers can only access website customer accounts.');
            }

            foreach (self::ECOMMERCE_MANAGER_ROUTES as $allowedRoute) {
                if ($request->routeIs($allowedRoute)) {
                    return $next($request);
                }
            }

            if ($request->isMethod('post')) {
                foreach (self::ECOMMERCE_MANAGER_POST_PATHS as $allowedPath) {
                    if ($request->is($allowedPath)) {
                        return $next($request);
                    }
                }
            }

            abort(403, 'This section is not available to ecommerce managers.');
        }

        $branch = $admin->branch_id ? Branch::find($admin->branch_id) : null;
        if (!$branch || !$branch->is_active) {
            abort(403, 'Your branch is inactive or not assigned.');
        }

        if ($isBranchManager && $request->routeIs('admin.users.login')) {
            abort(403, 'Branch managers cannot sign in as customers.');
        }

        $allowedRoutes = $isBranchManager ? self::MANAGER_ROUTES : self::SALESMAN_ROUTES;
        foreach ($allowedRoutes as $allowedRoute) {
            if ($request->routeIs($allowedRoute)) {
                return $next($request);
            }
        }

        abort(403, 'This section is not available to branch managers.');
    }
}