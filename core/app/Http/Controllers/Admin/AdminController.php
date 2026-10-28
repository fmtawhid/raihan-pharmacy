<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Branch;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserLogin;
use App\Support\BranchContext;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard(Request $request)
    {
        $pageTitle = 'Dashboard';
        $admin = auth('admin')->user();
        if ($admin instanceof Admin && ($admin->isBranchManager() || $admin->isBranchSalesman())) {
            return $this->branchDashboard($request, (int) $admin->branch_id);
        }
        if ($admin instanceof Admin && $admin->isEcommerceManager()) {
            return $this->branchDashboard($request, null, true);
        }
        
        // Get date filter
        $dateFilter = $request->input('date_filter', 'today');
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();
        switch($dateFilter) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = now()->subDays(7)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfDay();
                break;
            case 'custom':
                // Custom date range
                $startDate = $request->input('start_date') ? Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay() : now()->startOfDay();
                $endDate = $request->input('end_date') ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay() : now()->endOfDay();
                break;
        }
        
        // Calculate proper summary data
        $deposits = $this->getSummaryData($startDate, $endDate);
        
        $widget['total_product'] = BranchContext::id() !== null
            ? Product::whereHas('batches')->count()
            : Product::whereHas('categories')->count();

        $widget['orders']['total_orders']      = ['color' => 'bg--dark', 'count' => Order::isValidOrder()->count(), 'route' => 'admin.order.index'];
        $widget['orders']['pending_orders']    = ['color' => 'bg--warning', 'count' => Order::isValidOrder()->pending()->count(), 'route' => 'admin.order.pending'];
        $widget['orders']['processing_orders'] = ['color' => 'bg--info', 'count' => Order::isValidOrder()->processing()->count(), 'route' => 'admin.order.processing'];
        $widget['orders']['dispatched_orders'] = ['color' => 'bg--primary', 'count' => Order::isValidOrder()->dispatched()->count(), 'route' => 'admin.order.dispatched'];
        $widget['orders']['delivered_orders']  = ['color' => 'bg--success', 'count' => Order::isValidOrder()->delivered()->count(), 'route' => 'admin.order.delivered'];
        $widget['orders']['canceled_orders']   = ['color' => 'bg--danger', 'count' => Order::isValidOrder()->canceled()->count(), 'route' => 'admin.order.canceled'];

        $widget['total_users']             = User::count();
        $widget['active_users']            = User::active()->count();
        $widget['profile_completed']       = User::profileCompleted()->count();
        $widget['banned_users']            = User::banned()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();

        $widget['pending_tickets'] = BranchContext::id() === null ? SupportTicket::pending()->count() : 0;
        if (BranchContext::id() !== null) {
            $branchInventory = ProductBatch::query()
                ->selectRaw('product_id, SUM(CASE WHEN qty_received > qty_sold THEN qty_received - qty_sold ELSE 0 END) as branch_stock')
                ->groupBy('product_id');
            $widget['low_stock_products'] = Product::query()
                ->leftJoinSub($branchInventory, 'branch_inventory', 'branch_inventory.product_id', '=', 'products.id')
                ->where('products.track_inventory', Status::YES)
                ->whereRaw('COALESCE(branch_inventory.branch_stock, 0) <= products.alert_quantity')
                ->count('products.id');
            $widget['out_of_stock_products'] = Product::query()
                ->leftJoinSub($branchInventory, 'branch_inventory', 'branch_inventory.product_id', '=', 'products.id')
                ->where('products.track_inventory', Status::YES)
                ->whereRaw('COALESCE(branch_inventory.branch_stock, 0) <= 0')
                ->count('products.id');
        } else {
            $widget['low_stock_products'] = Product::lowStock()->count();
            $widget['out_of_stock_products'] = Product::outOfStock()->count();
        }


        // user Browsing, Country, Operating Log
        $userLoginData = BranchContext::id() === null
            ? UserLogin::where('created_at', '>=', Carbon::now()->subDays(30))->get(['browser', 'os', 'country'])
            : collect();

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });

        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        $deposit = $deposits;

        $topSellingProducts    = Product::topSales(3);
        $latestUser            = User::with('orders')->orderBy('id', 'desc')->take(6)->get();
        $recentOrders          = Order::isValidOrder()->with('user')->orderBy('id', 'desc')->take(6)->get();

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'topSellingProducts', 'latestUser', 'recentOrders', 'dateFilter'));
    }

    private function branchDashboard(Request $request, ?int $branchId, bool $ecommerce = false)
    {
        $validated = $request->validate([
            'date_filter' => 'nullable|in:today,last_7_days,last_30_days,custom',
            'start_date' => 'nullable|required_if:date_filter,custom|date',
            'end_date' => 'nullable|required_if:date_filter,custom|date|after_or_equal:start_date',
        ]);

        $dateFilter = $validated['date_filter'] ?? 'today';
        switch ($dateFilter) {
            case 'last_7_days':
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'custom':
                $startDate = Carbon::parse($validated['start_date'])->startOfDay();
                $endDate = Carbon::parse($validated['end_date'])->endOfDay();
                break;
            default:
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
        }

        $ordersQuery = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', [Status::ORDER_CANCELED, Status::ORDER_RETURNED])
            ->where(function ($query) {
                $query->where('payment_status', Status::PAYMENT_SUCCESS)
                    ->orWhere('is_cod', Status::YES);
            });
        if ($branchId !== null) {
            $ordersQuery->where('branch_id', $branchId);
        }
        if ($ecommerce) {
            $ordersQuery->where('order_number', 'like', 'OID-%');
        }

        $periodOrders = (clone $ordersQuery)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();
        $successfulPayments = DB::table('deposits')
            ->where('status', Status::PAYMENT_SUCCESS)
            ->groupBy('order_id')
            ->select('order_id')
            ->selectRaw('SUM(amount) as amount');
        $totalDue = $ecommerce ? 0 : (clone $ordersQuery)
            ->leftJoinSub($successfulPayments, 'successful_payments', 'successful_payments.order_id', '=', 'orders.id')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN COALESCE(successful_payments.amount, 0) <= 0 AND orders.payment_status = ? THEN 0 ELSE GREATEST(orders.total_amount - COALESCE(successful_payments.amount, 0), 0) END), 0) as total_due',
                [Status::PAYMENT_SUCCESS]
            )
            ->value('total_due');
        $totalPaid = $ecommerce
            ? (clone $ordersQuery)
                ->leftJoinSub($successfulPayments, 'successful_payments', 'successful_payments.order_id', '=', 'orders.id')
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN COALESCE(successful_payments.amount, 0) > 0 THEN LEAST(successful_payments.amount, orders.total_amount) WHEN orders.payment_status = ? THEN orders.total_amount ELSE 0 END), 0) as total_paid',
                    [Status::PAYMENT_SUCCESS]
                )
                ->value('total_paid')
            : max((float) $periodOrders->amount - (float) $totalDue, 0);
        $openOrders = (clone $ordersQuery)
            ->whereIn('status', [Status::ORDER_PENDING, Status::ORDER_PROCESSING, Status::ORDER_DISPATCHED])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();

        $inventoryBranchId = $ecommerce ? BranchContext::ownerId() : $branchId;
        $stock = $ecommerce
            ? (object) ['quantity' => 0, 'value' => 0, 'product_count' => 0]
            : DB::table('product_batches')
                ->when($inventoryBranchId !== null, fn($query) => $query->where('branch_id', $inventoryBranchId))
                ->selectRaw('COALESCE(SUM(CASE WHEN qty_received > qty_sold THEN qty_received - qty_sold ELSE 0 END), 0) as quantity')
                ->selectRaw('COALESCE(SUM(CASE WHEN qty_received > qty_sold THEN (qty_received - qty_sold) * COALESCE(purchase_price, 0) ELSE 0 END), 0) as value')
                ->selectRaw('COUNT(DISTINCT CASE WHEN qty_received > qty_sold THEN product_id END) as product_count')
                ->first();

        $lowStockProducts = 0;
        if (!$ecommerce) {
            $branchInventory = DB::table('product_batches as pb')
                ->when($inventoryBranchId !== null, fn($query) => $query->where('pb.branch_id', $inventoryBranchId))
                ->groupBy('pb.product_id')
                ->select('pb.product_id')
                ->selectRaw('SUM(CASE WHEN pb.qty_received > pb.qty_sold THEN pb.qty_received - pb.qty_sold ELSE 0 END) as quantity');
            $lowStockProducts = DB::table('products as p')
                ->leftJoinSub($branchInventory, 'branch_inventory', 'branch_inventory.product_id', '=', 'p.id')
                ->whereNull('p.deleted_at')
                ->where('p.track_inventory', Status::YES)
                ->whereRaw('COALESCE(branch_inventory.quantity, 0) <= p.alert_quantity')
                ->count();
        }
        $stockAdded = $ecommerce
            ? DB::table('product_batches')
                ->where('branch_id', $inventoryBranchId)
                ->whereBetween('purchased_at', [$startDate, $endDate])
                ->sum('qty_received')
            : 0;
        $unitsSold = $ecommerce
            ? DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.order_number', 'like', 'OID-%')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->whereNotIn('orders.status', [Status::ORDER_CANCELED, Status::ORDER_RETURNED])
                ->where(function ($query) {
                    $query->where('orders.payment_status', Status::PAYMENT_SUCCESS)
                        ->orWhere('orders.is_cod', Status::YES);
                })
                ->sum('order_details.quantity')
            : 0;

        $recentOrders = (clone $ordersQuery)
            ->select(['order_number', 'created_at', 'status', 'total_amount'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
        $statusLabels = [
            Status::ORDER_PENDING => 'Pending',
            Status::ORDER_PROCESSING => 'Processing',
            Status::ORDER_DISPATCHED => 'Dispatched',
            Status::ORDER_DELIVERED => 'Delivered',
        ];
        $branch = $branchId !== null ? Branch::findOrFail($branchId) : null;
        $pageTitle = $branch ? $branch->name . ' Dashboard' : 'Ecommerce Manager Dashboard';
        $filters = [
            'date_filter' => $dateFilter,
            'start_date' => $validated['start_date'] ?? now()->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->toDateString(),
        ];

        return view('admin.branch-dashboard', compact(
            'pageTitle', 'branch', 'filters', 'periodOrders', 'totalDue', 'totalPaid', 'openOrders',
            'stock', 'lowStockProducts', 'recentOrders', 'statusLabels', 'ecommerce', 'stockAdded', 'unitsSold'
        ));
    }
    
    private function getSummaryData($startDate, $endDate)
    {
        // Total Sales - Sum of completed orders (without shipping)
        $totalSales = Order::isValidOrder()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->selectRaw('SUM(total_amount - shipping_charge) as total')
            ->first()
            ?->total ?? 0;
        
        // Total Shipping - Sum of all shipping charges from completed orders
        $totalShipping = Order::isValidOrder()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->selectRaw('SUM(shipping_charge) as total')
            ->first()
            ?->total ?? 0;
        
        // Total Purchases - Sum of all product batch quantities * purchase price
        $totalPurchases = ProductBatch::whereBetween('purchased_at', [$startDate, $endDate])
            ->selectRaw('SUM(qty_received * purchase_price) as total')
            ->first()
            ?->total ?? 0;
        
        // Total Returns - Sum of canceled orders (total_amount - shipping_charge)
        $totalReturns = Order::isValidOrder()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'canceled')
            ->selectRaw('SUM(total_amount - shipping_charge) as total')
            ->first()
            ?->total ?? 0;
        
        // Calculate Total Profit from Product.purchase_price
        // Profit = (Sale Price - Product Purchase Price) × Quantity
        $profitQuery = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'canceled')
            ->selectRaw('SUM((order_details.price - COALESCE(order_details.purchase_price, 0)) * order_details.quantity) as total');
        if ($branchId = BranchContext::id()) {
            $profitQuery->where('orders.branch_id', $branchId);
        }
        $totalProfit = $profitQuery->first()?->total ?? 0;
        
        return [
            'total_deposit_amount' => $totalSales + $totalShipping,
            'total_deposit_rejected' => $totalPurchases,
            'total_deposit_charge' => $totalReturns,
            'total_deposit_pending' => $totalProfit
        ];
    } 
    
    public function summaryData(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'today');
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();
        
        switch($dateFilter) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = now()->subDays(7)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfDay();
                break;
            case 'custom':
                // Custom date range
                $startDate = $request->input('start_date') ? Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay() : now()->startOfDay();
                $endDate = $request->input('end_date') ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay() : now()->endOfDay();
                break;
        }
        
        $depositRaw = $this->getSummaryData($startDate, $endDate);
        
        $deposit = [
            'total_sales' => $depositRaw['total_deposit_amount'],
            'total_sales_formatted' => showAmount($depositRaw['total_deposit_amount']),
            'total_purchases' => $depositRaw['total_deposit_rejected'],
            'total_purchases_formatted' => showAmount($depositRaw['total_deposit_rejected']),
            'total_returns' => $depositRaw['total_deposit_charge'],
            'total_returns_formatted' => showAmount($depositRaw['total_deposit_charge']),
            'total_profit' => $depositRaw['total_deposit_pending'],
            'total_profit_formatted' => showAmount($depositRaw['total_deposit_pending'])
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $deposit
        ]);
    }


    public function salesChart(Request $request)
    {
        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }
        $deposits = Deposit::successful()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'deposits' => getAmount($deposits->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        // Monthly Deposit Report Graph
        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'name' => 'Total Sale',
                'data' => $data->pluck('deposits')
            ]
        ];

        return response()->json($report);
    }


    private function getAllDates($startDate, $endDate)
    {
        $dates = [];
        $currentDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function  getAllMonths($startDate, $endDate)
    {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }


    public function profile()
    {
        $pageTitle = 'Profile';
        $admin = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }


    public function notificationRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $admin = auth('admin')->user();
        if (
            $admin instanceof Admin
            && $admin->isEcommerceManager()
            && !$admin->isBranchManager()
            && !$admin->isBranchSalesman()
            && !preg_match('/^New order #OID-/', $notification->title)
        ) {
            abort(403, 'Ecommerce managers can only open website order notifications.');
        }

        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport()
    {
        $pageTitle = 'Your Listed Report & Request';
        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response = CurlRequest::curlContent($url);
        $response = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type'] = $request->type;
        $arr['message'] = $request->message;
        $response = CurlRequest::curlPostContent($url, $arr);
        $response = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAllNotification()
    {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification()
    {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id)
    {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash)
    {
        $filePath = decrypt($fileHash);

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            dd($e->getMessage());
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function printInvoice($order)
    {
        $pageTitle = 'Print Invoice';
        $order = Order::isValidOrder()->with(['user', 'orderDetail.product', 'deposits.gateway'])->findOrFail($order);
        $paidAmount = (float) $order->deposits
            ->where('status', Status::PAYMENT_SUCCESS)
            ->sum('amount');

        if ($paidAmount <= 0 && $order->payment_status == Status::PAYMENT_SUCCESS) {
            $paidAmount = (float) $order->total_amount;
        }

        $paidAmount = min($paidAmount, (float) $order->total_amount);
        $dueAmount = max((float) $order->total_amount - $paidAmount, 0);

        $invoiceView = request()->query('format') === 'a4' ? 'invoice.print-a4' : 'invoice.print';
        return view($invoiceView, compact('pageTitle', 'order', 'paidAmount', 'dueAmount'));
    }
}
