<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Response;
use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockLog;
use App\Models\UserLogin;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function loginHistory(Request $request)
    {
        $pageTitle = 'User Login History';
        $loginLogs = UserLogin::orderBy('id', 'desc')->searchable(['user:username'])->dateFilter()->with('user')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs'));
    }

    public function loginIpHistory($ip)
    {
        $pageTitle = 'Login by - ' . $ip;
        $loginLogs = UserLogin::where('user_ip', $ip)->orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs', 'ip'));
    }

    public function notificationHistory(Request $request)
    {
        $pageTitle = 'Notification History';
        $logs = NotificationLog::orderBy('id', 'desc')->searchable(['user:username'])->dateFilter()->with('user')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs'));
    }

    public function emailDetails($id)
    {
        $pageTitle = 'Email Details';
        $email = NotificationLog::findOrFail($id);
        return view('admin.reports.email_details', compact('pageTitle', 'email'));
    }

    public function  salesReport()
    {
        $pageTitle = 'Sales Report';
        
        // Build base query with filters
        $baseQuery = Order::isValidOrder()->delivered()->searchable(['order_number', 'user:username'])->dateFilter();
        
        // Get filtered results
        $logs = $baseQuery->orderBy('id', 'desc')->withSum('orderDetail as total_product', 'quantity')->with('user')->paginate(getPaginate());

        // Calculate totals from filtered data
        $filteredOrders = (clone $baseQuery)->with('orderDetail')->get();
        
        $totalSalesProduct = 0;
        $totalSalesAmount = 0;
        $totalShippingCharge = 0;
        $totalAmount = 0;
        
        foreach ($filteredOrders as $order) {
            $totalSalesProduct += $order->orderDetail->sum('quantity');
            $totalSalesAmount += $order->subtotal;
            $totalShippingCharge += $order->shipping_charge;
            $totalAmount += $order->total_amount;
        }

        return view('admin.reports.sales', compact('pageTitle', 'logs', 'totalSalesProduct', 'totalSalesAmount', 'totalShippingCharge', 'totalAmount'));
    }

    // public function businessReport(Request $request)
    // {
    //     $pageTitle = 'Business Report';

    //     $orders = Order::isValidOrder()
    //         ->when(
    //             $request->order_number,
    //             fn($q) =>
    //             $q->where('order_number', 'like', '%' . $request->order_number . '%')
    //         )
    //         ->when(
    //             !is_null($request->status),
    //             fn($q) =>
    //             $q->where('status', $request->status)
    //         )
    //         ->when($request->date, function ($q) use ($request) {
    //             $dateRange = explode(' - ', $request->date);
    //             if (count($dateRange) === 2) {
    //                 $start = trim($dateRange[0]);
    //                 $end = trim($dateRange[1]);
    //                 $q->whereBetween('created_at', [$start, $end]);
    //             }
    //         })
    //         ->whereHas('orderDetail', function ($q) use ($request) {
    //             $q->when($request->sku, function ($q) use ($request) {
    //                 $q->whereHas(
    //                     'productVariant',
    //                     fn($v) =>
    //                     $v->where('sku', 'like', '%' . $request->sku . '%')
    //                 )->orWhereHas(
    //                     'product',
    //                     fn($p) =>
    //                     $p->where('sku', 'like', '%' . $request->sku . '%')
    //                 );
    //             });

    //             $q->when(
    //                 $request->product,
    //                 fn($q) =>
    //                 $q->whereHas(
    //                     'product',
    //                     fn($p) =>
    //                     $p->where('name', 'like', '%' . $request->product . '%')
    //                 )
    //             );
    //         })
    //         ->with(['orderDetail.product', 'orderDetail.productVariant'])
    //         ->latest()
    //         ->paginate(getPaginate(10))
    //         ->appends($request->all());

    //     return view('admin.reports.business', compact('pageTitle', 'orders'));
    // }

    public function businessReport(Request $request)
    {
        $pageTitle = 'Business Report';

        // Base query with filters, but without pagination yet
        $baseQuery = Order::isValidOrder()
            ->when(
                $request->order_number,
                fn($q) =>
                $q->where('order_number', 'like', '%' . $request->order_number . '%')
            )
            ->when(
                !is_null($request->status),
                fn($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->date, function ($q) use ($request) {
                $dateRange = explode(' - ', $request->date);
                if (count($dateRange) === 2) {
                    $start = trim($dateRange[0]);
                    $end = trim($dateRange[1]);
                    $q->whereBetween('created_at', [$start, $end]);
                }
            })
            ->when($request->sku || $request->product, function ($q) use ($request) {
                $q->whereHas('orderDetail', function ($sq) use ($request) {
                    $sq->when($request->sku, function ($sq) use ($request) {
                        $sq->whereHas(
                            'productVariant',
                            fn($v) =>
                            $v->where('sku', 'like', '%' . $request->sku . '%')
                        )->orWhereHas(
                            'product',
                            fn($p) =>
                            $p->where('sku', 'like', '%' . $request->sku . '%')
                        );
                    });

                    $sq->when(
                        $request->product,
                        fn($sq) =>
                        $sq->whereHas(
                            'product',
                            fn($p) =>
                            $p->where('name', 'like', '%' . $request->product . '%')
                        )
                    );
                });
            });

        // Clone query before pagination for summary
        $summaryOrders = (clone $baseQuery)->with('orderDetail')->get();

        $totalProducts = 0;
        $totalQuantity = 0;
        $statusCounts = [
            'Pending' => 0,
            'Processing' => 0,
            'Dispatched' => 0,
            'Delivered' => 0,
            'Cancelled' => 0,
            'Returned' => 0,
        ];

        $uniqueProductIds = [];

        foreach ($summaryOrders as $order) {
            foreach ($order->orderDetail as $detail) {
                $totalQuantity += $detail->quantity;

                if ($detail->product_id) {
                    $uniqueProductIds[$detail->product_id] = true;
                }
            }

            $label = match ($order->status) {
                0 => 'Pending',
                1 => 'Processing',
                2 => 'Dispatched',
                3 => 'Delivered',
                4 => 'Cancelled',
                9 => 'Returned',
                default => 'Unknown',
            };

            if (isset($statusCounts[$label])) {
                $statusCounts[$label]++;
            }
        }

        $totalProducts = count($uniqueProductIds);

        // Paginate after stats
        $orders = $baseQuery
            ->with(['orderDetail.product', 'orderDetail.productVariant'])
            ->latest()
            ->paginate(getPaginate(10))
            ->appends($request->all());

        return view('admin.reports.business', compact(
            'pageTitle',
            'orders',
            'totalProducts',
            'totalQuantity',
            'statusCounts'
        ));
    }


    public function businessReportCsv(Request $request)
    {
        $orders = Order::isValidOrder()
            ->when($request->order_number, fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
            ->when(!is_null($request->status), fn($q) => $q->where('status', $request->status))
            ->when($request->date, function ($q) use ($request) {
                $dateRange = explode(' - ', $request->date);
                if (count($dateRange) === 2) {
                    $q->whereBetween('created_at', [trim($dateRange[0]), trim($dateRange[1])]);
                }
            })
            ->when($request->sku || $request->product, function ($q) use ($request) {
                $q->whereHas('orderDetail', function ($sq) use ($request) {
                    $sq->when($request->sku, function ($sq) use ($request) {
                        $sq->whereHas('productVariant', fn($v) => $v->where('sku', 'like', '%' . $request->sku . '%'))
                            ->orWhereHas('product', fn($p) => $p->where('sku', 'like', '%' . $request->sku . '%'));
                    });
                    $sq->when($request->product, fn($sq) => $sq->whereHas('product', fn($p) => $p->where('name', 'like', '%' . $request->product . '%')));
                });
            })
            ->with(['orderDetail.product', 'orderDetail.productVariant'])
            ->latest()
            ->get();

        $filename = 'business-report.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Product', 'Order Number', 'Quantity', 'Status']);

            foreach ($orders as $order) {
                foreach ($order->orderDetail as $detail) {
                    $product = $detail->product;
                    $variant = $detail->productVariant;
                    $sku = $variant->sku ?? ($product->sku ?? 'N/A');
                    $name = $product->name ?? 'N/A';
                    $variantName = $variant->name ?? '';
                    $quantity = $detail->quantity;
                    $status = strip_tags($order->statusBadge());

                    fputcsv($handle, [
                        $sku,
                        $variantName ? "$name ($variantName)" : $name,
                        $order->order_number,
                        $quantity,
                        $status,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function salesReportCsv(Request $request)
    {
        $orders = Order::isValidOrder()
            ->delivered()
            ->searchable(['order_number', 'user:username'])
            ->dateFilter()
            ->with('orderDetail')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate totals from filtered data
        $totalSalesProduct = $orders->sum(function ($order) {
            return $order->orderDetail->sum('quantity');
        });

        $totalSalesAmount = $orders->sum('subtotal');
        $totalShippingCharge = $orders->sum('shipping_charge');
        $totalAmount = $orders->sum('total_amount');

        $dateRange = $request->date ?? 'All Time';
        $generatedDate = now()->format('d M, Y');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report.csv"',
        ];

        $callback = function () use ($orders, $totalSalesProduct, $totalShippingCharge, $totalSalesAmount, $totalAmount, $dateRange, $generatedDate) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Rayhan Pharmacy']);
            fputcsv($handle, ['Sales Report']);
            fputcsv($handle, ['Date Range:', $dateRange]);
            fputcsv($handle, ['Generated On:', $generatedDate]);
            fputcsv($handle, []); // spacer

            // Summary
            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Sales Product', $totalSalesProduct]);
            fputcsv($handle, ['Total Shipping Charge', number_format($totalShippingCharge, 2)]);
            fputcsv($handle, ['Total Sales Amount', number_format($totalSalesAmount, 2)]);
            fputcsv($handle, ['Total Amount', number_format($totalAmount, 2)]);
            fputcsv($handle, []); // spacer

            // Table Header
            fputcsv($handle, ['Order No.', 'Customer', 'Date', 'Total Product', 'Shipping Charge', 'Subtotal', 'Total']);

            // Table rows
            foreach ($orders as $order) {
                $totalProduct = $order->orderDetail->sum('quantity');
                fputcsv($handle, [
                    $order->order_number,
                    optional($order->user)->username ?? 'Guest',
                    $order->created_at->format('d M, Y'),
                    $totalProduct,
                    number_format($order->shipping_charge, 2),
                    number_format($order->subtotal, 2),
                    number_format($order->total_amount, 2),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
