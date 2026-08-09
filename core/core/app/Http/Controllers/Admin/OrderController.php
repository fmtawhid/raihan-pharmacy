<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\ProductManager;
use App\Models\DigitalFile;
use App\Models\Order;
use App\Models\ProductReturn;
use App\Models\UserNotification;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function ordered(Request $request)
    {
        /** -----------------------------------------------------------------
         * 1.  Build a fresh query (NOT paginated yet)
         * ----------------------------------------------------------------*/
        $query = Order::isValidOrder();

        /* status scope ---------------------------------------------------*/
        if ($status = $request->get('status')) {
            $query->$status();          // pending(), processing() … cod()
        }

        /* quick-date presets --------------------------------------------*/
        switch ($request->get('date')) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'last7':
                $query->whereDate('created_at', '>=', today()->subDays(6));
                break;
            case 'last30':
                $query->whereDate('created_at', '>=', today()->subDays(29));
                break;
        }

        /* custom range ---------------------------------------------------*/
        if ($request->filled(['start', 'end'])) {
            $query->whereBetween('created_at', [
                $request->start . ' 00:00:00',
                $request->end   . ' 23:59:59'
            ]);
        }

        /* keyword filters ------------------------------------------------*/
        if ($request->order) {
            $query->where('order_number', 'like', "%{$request->order}%");
        }
        if ($request->user) {
            $query->whereHas(
                'user',
                fn($q) => $q->where('username', 'like', "%{$request->user}%")
            );
        }

        /** -----------------------------------------------------------------
         * 2.  Finish the query (searchable + relations) and paginate once
         * ----------------------------------------------------------------*/
        $orders = $query
            ->searchable(['order_number', 'user:username'], false)
            ->with([
                'user',
                'deposit',
                'deposit.gateway',
                'afterSaleDownloadableProducts:id,name,is_downloadable,delivery_type',
            ])
            ->orderByDesc('id')
            ->paginate(getPaginate(5));

        /** -----------------------------------------------------------------
         * 3.  Return partial HTML for AJAX, full view otherwise
         * ----------------------------------------------------------------*/
        if ($request->ajax()) {
            return view(
                'admin.order.partials.order_table',
                compact('orders')
            )->render();
        }

        $statusesCount = $this->badgeCounts();

        return view('admin.order.all', [
            'orders'    => $orders,
            'pageTitle' => 'All Orders',
            'statusesCount' => $this->badgeCounts(),
        ]);
    }

    private function badgeCounts(): array
    {
        return [
            'all'        => Order::count(),
            'pending'    => Order::pending()->count(),
            'processing' => Order::processing()->count(),
            'dispatched' => Order::dispatched()->count(),
            'delivered'  => Order::delivered()->count(),
            'canceled'   => Order::canceled()->count(),
            'returned'   => Order::returned()->count(),
            'cod'        => Order::cod()->count(),   // scope you already have
        ];
    }

    public function statusCounts()
    {
        return response()->json($this->badgeCounts());
    }


    public function codOrders()
    {
        $pageTitle = "COD Orders";
        $orders    = $this->orderData('cod');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function pending()
    {
        $pageTitle = "Pending Orders";
        $orders    = $this->orderData('pending');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function onProcessing()
    {
        $pageTitle = "Orders on Processing";
        $orders    = $this->orderData('processing');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function dispatched()
    {
        $pageTitle = "Orders Dispatched";
        $orders    = $this->orderData('dispatched');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function canceledOrders()
    {
        $pageTitle = "Canceled Orders";
        $orders    = $this->orderData('canceled');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function returned()
    {
        $pageTitle = "Returned Orders";
        $orders    = $this->orderData('returned');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    public function deliveredOrders()
    {
        $pageTitle = "Delivered Orders";
        $orders    = $this->orderData('delivered');
        return view('admin.order.all', compact('pageTitle', 'orders'));
    }

    private function orderData($scope = null)
    {
        $orders = Order::isValidOrder();

        if ($scope) {
            $orders->$scope();
        }
        return $orders->searchable(['order_number', 'user:username'], false)
            ->with([
                'user',
                'deposit',
                'deposit.gateway',
                'afterSaleDownloadableProducts:id,name,is_downloadable,delivery_type'
            ])
            ->orderBy('id', 'DESC')
            ->paginate(getPaginate());
    }

    public function orderDetails($id)
    {
        $pageTitle = 'Order Details';
        $order     = Order::isValidOrder()->where('id', $id)->with('user', 'deposit', 'deposit.gateway', 'orderDetail.product', 'orderDetail.productVariant', 'appliedCoupon')->firstOrFail();

        return view('admin.order.detail', compact('order', 'pageTitle'));
    }

    public function changeStatus(Request $request, $id)
    {
        $order = Order::isValidOrder()->with('afterSaleDownloadableProducts', 'orderDetail', 'user', 'deposit')->findOrFail($id);
        if ($order->status == Status::ORDER_DELIVERED) {
            $notify[] = ['error', 'This order has already been delivered'];
            return back()->withNotify($notify);
        }

        // if order has downloadable product, then admin have to upload file
        if ($order->status == Status::ORDER_PENDING && $order->afterSaleDownloadableProducts->count()) {
            $this->validateAfterSaleDownloadableFiles($request, $order->afterSaleDownloadableProducts);
        }

        if ($order->status == Status::ORDER_PENDING && !$order->hasPhysicalProduct()) {
            $order->status = Status::ORDER_DELIVERED;
            if ($order->afterSaleDownloadableProducts->count()) {
                $this->saveAfterSaleDownloadableFiles($request, $order); //while physical product not exist in the order
            }
        } else {
            $order->status += 1;
        }

        $order->save();

        if ($order->status == Status::ORDER_PROCESSING) {
            $action = 'Processing';
        } elseif ($order->status == Status::ORDER_DISPATCHED) {
            $action = 'Dispatched';
        } elseif ($order->status == Status::ORDER_DELIVERED) {
            $action = 'Delivered';

            if ($order->is_cod) {
                $order->payment_status = Status::PAYMENT_SUCCESS;
                $order->save();

                $deposit = $order->deposit;
                $deposit->status = Status::PAYMENT_SUCCESS;
                $deposit->save();
            }
        }

        $this->sendOrderMail($order);

        // while physical product and after sale downloadable product are together
        if ($order->status == Status::ORDER_PROCESSING  && $order->afterSaleDownloadableProducts->count() && $order->hasPhysicalProduct()) {
            $this->saveAfterSaleDownloadableFiles($request, $order);
        }

        if ($order->hasDownloadableProduct() && $order->status == Status::ORDER_DELIVERED) {
            notify($order->user, 'DOWNLOAD_DIGITAL_PRODUCT');
        }

        $notify[] = ['success', 'Order status changed to ' . strtolower($action)];
        return back()->withNotify($notify);
    }

    private function validateAfterSaleDownloadableFiles($request, $products)
    {
        $rules = ['download_file' => 'required|array'];
        foreach ($products as $product) {
            $rules["download_file.$product->id"] = ['required', new FileTypeValidate(['zip'])];
        }

        $messages = [
            'download_file.required' => 'The downloadable file field is required',
            'download_file.*required' => 'The downloadable file field is required'
        ];

        $request->validate($rules, $messages);
    }

    private function saveAfterSaleDownloadableFiles($request, $order)
    {
        foreach ($order->afterSaleDownloadableProducts as $key => $downloadProduct) {
            try {
                $orderDetail = $order->orderDetail->where('product_id', $downloadProduct->id)->first();
                $file = $request->download_file[$downloadProduct->id];

                // store downloadable file
                $digitalFile = $orderDetail->digitalFile ?? new DigitalFile();
                $digitalFile->name = fileUploader($file, getFilePath('digitalProductFile'), old: @$orderDetail->digitalFile->name);
                $orderDetail->digitalFile()->save($digitalFile);
            } catch (\Throwable $th) {
                Log::error("Error in saveAfterSaleDownloadableFiles: " . $th->getMessage());
            }
        }
    }

    public function cancelStatus($id)
    {
        $order = Order::isValidOrder()->with('orderDetail', 'orderDetail.product', 'orderDetail.productVariant', 'appliedCoupon')->findOrFail($id);
        if ($order->status != Status::ORDER_PENDING && $order->status != Status::ORDER_PROCESSING) {
            $notify[] = ['error', 'You can\'t cancel the order'];
            return back()->withNotify($notify);
        }

        // update stock
        $productManager = new ProductManager();
        foreach ($order->orderDetail as $key => $orderDetail) {
            $description = "Canceled order of $orderDetail->quantity " . Str::plural('product', $orderDetail->quantity);
            $productManager->createStockLog($orderDetail->product, $orderDetail->quantity, $description, $orderDetail->productVariant, '+');
        }

        if ($order->appliedCoupon) {
            $order->appliedCoupon->delete();
        }

        $order->status = Status::ORDER_CANCELED;
        $order->save();

        $this->sendOrderMail($order);

        $notify[] = ['success', 'Order status changed to canceled'];
        return back()->withNotify($notify);
    }

    public function addReturn()
    {
        $pageTitle = 'Add Return Order';
        return view('admin.order.add_return', compact('pageTitle'));
    }

    // ── SEARCH ORDERS FOR RETURN ────────────────────
    public function searchOrdersForReturn(Request $request)
    {
        $query = $request->input('query', '');
        
        if (strlen($query) < 2) {
            return response()->json(['orders' => []]);
        }

        $orders = Order::where(function($q) use ($query) {
                    $q->where('order_number', 'like', "%{$query}%")
                      ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$query}%"));
                })
                ->with('user')
                ->limit(10)
                ->get()
                ->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_name' => $order->user ? $order->user->name : 'Guest',
                        'total_amount' => $order->total_amount,
                        'status_text' => $order->statusText() ?? 'Completed',
                    ];
                });

        return response()->json(['orders' => $orders]);
    }

    // ── GET ORDER DETAILS FOR RETURN ────────────────
    public function getReturnDetails(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $order = Order::isValidOrder()->with('orderDetail.product', 'user')->findOrFail($orderId);

            // Prepare items with return tracking
            $items = $order->orderDetail->map(function($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->price,
                    'already_returned' => $this->getAlreadyReturnedQty($detail->id),
                ];
            });

            return response()->json([
                'status' => 'success',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => $order->user ? $order->user->name : 'Guest',
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->format('d M Y'),
                    'items' => $items,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }
    }

    // ── GET ALREADY RETURNED QUANTITY ───────────────
    private function getAlreadyReturnedQty($orderDetailId)
    {
        $totalReturned = ProductReturn::where('order_detail_id', $orderDetailId)->sum('quantity_returned');
        return $totalReturned ?? 0;
    }

    // ── SAVE RETURN ─────────────────────────────────
    public function saveReturn(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $items = $request->input('items', []);
            $totalRefund = $request->input('total_refund', 0);

            if (empty($items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No items to return'
                ], 422);
            }

            $order = Order::isValidOrder()->with('orderDetail.product', 'orderDetail.productVariant')->findOrFail($orderId);
            $productManager = new ProductManager();
            $totalQtyReturned = 0;

            // Process each return item
            foreach ($items as $item) {
                $orderDetail = $order->orderDetail()->find($item['order_detail_id']);
                if (!$orderDetail) {
                    continue;
                }

                $returnQty = (int) $item['return_qty'];
                $refundAmount = (float) $item['refund_amount'];

                // 1. Update stock - ADD BACK to inventory
                $product = $orderDetail->product;
                $productVariant = $orderDetail->productVariant;

                if ($productVariant && $productVariant->manage_stock && $productVariant->track_inventory) {
                    $productVariant->in_stock += $returnQty;
                    $productVariant->save();
                } elseif ($product->track_inventory) {
                    $product->in_stock += $returnQty;
                    $product->save();
                }

                // 2. Create stock log for return
                $description = "Return: $returnQty " . Str::plural('unit',  $returnQty) . " from Order #" . $order->order_number;
                $productManager->createStockLog($product, $returnQty, $description, $productVariant, '+');

                // 3. Save return record to returns table
                ProductReturn::create([
                    'order_id' => $orderId,
                    'order_detail_id' => $orderDetail->id,
                    'product_id' => $product->id,
                    'quantity_returned' => $returnQty,
                    'refund_amount' => $refundAmount,
                ]);

                $totalQtyReturned += $returnQty;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Return processed successfully',
                'data' => [
                    'total_items' => count($items),
                    'total_qty' => $totalQtyReturned,
                    'total_refund' => $totalRefund,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Return processing error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }

    public function return($id)
    {
        $order = Order::isValidOrder()->dispatched()->with('orderDetail', 'orderDetail.product', 'orderDetail.productVariant')->findOrFail($id);

        foreach ($order->orderDetail as $orderDetail) {
            $product = $orderDetail->product;
            $productVariant = $orderDetail->productVariant;
            if ($productVariant) {
                if ($productVariant->manage_stock && $productVariant->track_inventory) {
                    $productVariant->in_stock += $orderDetail->quantity;
                    $productVariant->save();
                } elseif ($product->track_inventory) {
                    $product->in_stock += $orderDetail->quantity;
                    $product->save();
                }
            } else {
                if ($product->track_inventory) {
                    $product->in_stock += $orderDetail->quantity;
                    $product->save();
                }
            }
        }

        $order->status = Status::ORDER_RETURNED;
        $order->save();

        $notify[] = ['success', 'Order return processed successfully'];
        return back()->withNotify($notify);
    }

    // private function sendOrderMail($order)
    // {
    //     $shortCode = [
    //         'site_name' => gs('sitename'),
    //         'order_id'  => $order->order_number,
    //     ];

    //     $userNotification = new UserNotification();
    //     $userNotification->user_id = $order->id;
    //     $title = 'Order #' . $order->order_number;

    //     if ($order->status == Status::ORDER_PROCESSING) {
    //         $template = 'ORDER_ON_PROCESSING_CONFIRMATION';
    //         $title .= ' is processing';
    //     } elseif ($order->status == Status::ORDER_DISPATCHED) {
    //         $template = 'ORDER_DISPATCHED_CONFIRMATION';
    //         $title .= ' has been dispatched';
    //     } elseif ($order->status == Status::ORDER_DELIVERED) {
    //         $template = 'ORDER_DELIVERY_CONFIRMATION';
    //         $title .= ' has been delivered';
    //     } elseif ($order->status == Status::ORDER_CANCELED) {
    //         $template = 'ORDER_CANCELLATION_CONFIRMATION';
    //         $title .= ' has been cancelled';
    //     }

    //     $userNotification->title = $title;
    //     $userNotification->click_url = urlPath('user.order', $order->order_number);
    //     $userNotification->save();

    //     notify($order->user, $template, $shortCode);
    // }
    /**
     * Send status-change mail to registered users **or** guests.
     */
    private function sendOrderMail(Order $order)
    {
        /* -------------------------------------------------
     | 1.  Work out the template + e-mail subject
     |------------------------------------------------*/
        $templateMap = [
            Status::ORDER_PROCESSING => 'ORDER_ON_PROCESSING_CONFIRMATION',
            Status::ORDER_DISPATCHED => 'ORDER_DISPATCHED_CONFIRMATION',
            Status::ORDER_DELIVERED  => 'ORDER_DELIVERY_CONFIRMATION',
            Status::ORDER_CANCELED   => 'ORDER_CANCELLATION_CONFIRMATION',
        ];
        $template = $templateMap[$order->status] ?? null;

        $title = 'Order #' . $order->order_number . ' ';
        $title .= match ($order->status) {
            Status::ORDER_PROCESSING => 'is processing',
            Status::ORDER_DISPATCHED => 'has been dispatched',
            Status::ORDER_DELIVERED  => 'has been delivered',
            Status::ORDER_CANCELED   => 'has been cancelled',
            default                  => '',
        };

        /* Short-codes available to the e-mail template */
        $shortCode = [
            'site_name'   => gs('site_name'),
            'order_id'    => $order->order_number,
            'order_url'   => url(
                $order->user_id
                    ? route('user.order',   $order->order_number, false)      // logged-in user route
                    : '/guest/order/' . $order->order_number                  // public guest route
            ),
            'amount'      => showAmount($order->total_amount),
            'currency'    => gs('cur_text'),
            'status'      => ucfirst(strtolower($order->statusText() ?? '')),
        ];

        /* -------------------------------------------------
     | 2.  If it’s a registered user  → normal flow
     |------------------------------------------------*/
        if ($order->user_id) {
            // dashboard toast / db notification
            $userNotification          = new UserNotification();
            $userNotification->user_id = $order->user_id;
            $userNotification->title   = $title;
            $userNotification->click_url =
                urlPath('user.order', $order->order_number);
            $userNotification->save();

            // e-mail / sms / push – whatever is enabled
            notify($order->user, $template, $shortCode);
            return;
        }

        /* -------------------------------------------------
     | 3.  Otherwise it’s a GUEST order
     |------------------------------------------------*/
        if ($order->guest_email) {

            notify(
                [
                    'email'    => $order->guest_email,
                    'username' => $order->guest_email,                 // ← add this
                    'fullname' => optional(json_decode($order->shipping_address))->name
                        ?? 'Guest User',
                ],
                $template,
                $shortCode,
                ['email'],
                false   // send immediately; not queued
            );
        }
    }
    // ── RETURNS LIST ─────────────────────────────────
    public function returnsList(Request $request)
    {
        $pageTitle = 'Returned Products';
        
        $query = ProductReturn::with('order', 'product');

        // Search by order number or product name
        if ($keyword = $request->input('search')) {
            $query->whereHas('order', fn($q) => $q->where('order_number', 'like', "%{$keyword}%"))
                  ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        // Date range filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->input('start_date') . ' 00:00:00';
            $endDate = $request->input('end_date') . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $returns = $query->orderByDesc('id')->paginate(getPaginate());

        return view('admin.order.returns_list', compact('returns', 'pageTitle'));
    }

    // ── EXPORT RETURNS ───────────────────────────────
    public function exportsReturns(Request $request)
    {
        $returns = ProductReturn::with('order', 'product')->get();

        $fileName = 'returns_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function () use ($returns) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, [
                'Return ID',
                'Order Number',
                'Product Name',
                'Quantity Returned',
                'Refund Amount',
                'Return Date'
            ]);

            // CSV Data
            foreach ($returns as $return) {
                fputcsv($handle, [
                    $return->id,
                    optional($return->order)->order_number,
                    optional($return->product)->name,
                    $return->quantity_returned,
                    $return->refund_amount,
                    $return->created_at->format('d M Y'),
                ]);
            }

            fclose($handle);
        }, $fileName);
    }

    // ── DELETE RETURN ────────────────────────────────
    public function deleteReturn($id)
    {
        $return = ProductReturn::findOrFail($id);
        $return->delete();

        $notify[] = ['success', 'Return record deleted successfully'];
        return back()->withNotify($notify);
    }

    // Add this method in App\Http\Controllers\Admin\OrderController
    public function delete($id)
    {
        $order = Order::isValidOrder()->with(['orderDetail', 'orderDetail.digitalFile', 'appliedCoupon'])->findOrFail($id);

        // Only allow deletion of canceled or returned orders
        if ($order->status != Status::ORDER_CANCELED && $order->status != Status::ORDER_RETURNED) {
            $notify[] = ['error', 'You can only delete canceled or returned orders.'];
            return back()->withNotify($notify);
        }

        // Delete digital files and their physical files
        foreach ($order->orderDetail as $orderDetail) {
            if ($digitalFile = $orderDetail->digitalFile) {
                $path = getFilePath('digitalProductFile') . '/' . $digitalFile->name;
                if (file_exists($path)) {
                    unlink($path);
                }
                $digitalFile->delete();
            }
        }

        // Delete related records
        if ($order->appliedCoupon) {
            $order->appliedCoupon->delete();
        }
        $order->orderDetail()->delete();
        $order->delete();

        $notify[] = ['success', 'Order deleted successfully'];
        return back()->withNotify($notify);
    }
}
 