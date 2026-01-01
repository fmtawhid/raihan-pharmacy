<?php

namespace App\Http\Controllers;
use App\Models\Order; 
use Illuminate\Http\Request;

class GuestOrderController extends Controller
{
    //

    public function show($orderNumber)
    {
        $order = Order::with(
                    'orderDetail.product',
                    'orderDetail.productVariant',
                    'deposit',
                    'appliedCoupon'
                 )
                 ->where('order_number', $orderNumber)
                 ->whereNull('user_id')      // be sure it’s a guest order
                 ->firstOrFail();

        $pageTitle = 'Guest Order Summary';

        return view('Template::guest.order_summary', compact('order', 'pageTitle'));
    }
}
