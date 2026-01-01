<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CartManager;
use App\Lib\ProductManager;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\AppliedCoupon;
use App\Models\GatewayCurrency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $cartManager;

    public function __construct(CartManager $cartManager)
    {
        parent::__construct();
        $this->cartManager = $cartManager;
    }

    public function paymentMethods()
    {
        $pageTitle = 'Payment Methods';
        $gatewayCurrencies = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code', 'desc')->get();

        $shippingMethod = ShippingMethod::active()->where('id', @session('checkout_data')['shipping_method_id'])->first();

        $hasPhysicalProduct = $this->cartManager->checkPhysicalProductExistence();

        $subtotal = $this->cartManager->subtotal();
        $coupon = session('coupon');

        return view('Template::user.checkout_steps.payment_methods', compact('pageTitle', 'gatewayCurrencies', 'shippingMethod', 'subtotal', 'coupon', 'hasPhysicalProduct'));
    }


    public function completeCheckout(Request $request)
    {
        $checkoutData = session('checkout_data', []);
        $checkoutData['shipping_address'] = $request->only([
            'name',
            'phone',
            'address',

        ]);
        $checkoutData['division_id'] = $request->division_id;
        $checkoutData['district_id'] = $request->district_id;
        $checkoutData['area_name']   = $request->area_name;
        $checkoutData['postcode']    = $request->postcode;

        if (!auth()->check()) {
            $checkoutData['guest_email'] = $request->guest_email;
        }
        session()->put('checkout_data', $checkoutData);


        // shipping method validation

        $ids = ShippingMethod::active()->pluck('id')->toArray();

        $request->validate([
            'shipping_method_id' => 'required|in:' . implode(',', $ids)
        ], [
            'shipping_method_id.required' => 'Delivery type field is required',
            'shipping_method_id.in'       => 'Invalid delivery type selected'
        ]);

        $checkoutData = session('checkout_data');
        $checkoutData['shipping_method_id'] = $request->shipping_method_id;

        session()->put('checkout_data', $checkoutData);


        // dd('completeCheckout');

        $request->validate([
            'division_id' => 'required',
            'district_id' => 'required',
            'area_name'   => 'required',
            'postcode'    => 'required',
        ]);

        $this->validation($request);

        $gatewayCurrency = $this->getGatewayCurrency($request);

        $hasPhysicalProduct = $this->cartManager->checkPhysicalProductExistence();

        // If there is no physical product in the cart COD can not be selected
        if (!$hasPhysicalProduct && $gatewayCurrency->id == 0) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }


        $cartData = $this->cartManager->getCart();

        if (blank($cartData)) {
            $notify[] = ['error', 'No product found to place order'];
            return to_route('cart.page')->withNotify($notify);
        }

        $checkStock = $this->checkStock($cartData);

        if ($checkStock instanceof RedirectResponse) {
            return $checkStock;
        }

        $checkPrice = $this->cartManager->checkProductsPrice($cartData);

        if (!$checkPrice['status']) {
            $notify[] = ['error', $checkPrice['message']];
            return to_route('cart.page')->withNotify($notify);
        }

        $subtotal = $this->cartManager->subtotal();
        $coupon = $this->appliedCoupon($cartData, $subtotal);

        if (isset($coupon['error'])) {
            $notify[] = ['error', $coupon['error']];
            return back()->withNotify($notify);
        }

        $orderId = session()->get('order_id');
        $order = null;
        if ($orderId) {
            $order = Order::where('user_id', auth()->id())->find($orderId);
            session()->forget('order_id');
        }

        if (!$order) {
            $order = $this->saveOrder($subtotal, $coupon, $gatewayCurrency, $cartData, $hasPhysicalProduct);
        }

        if ($coupon) {
            $this->saveAppliedCoupon($coupon, $order);
        }

        $this->sendAdminNotification($order);

        Log::info('[GuestMail] calling notify', ['order_id' => $order->id]);

        // Prepare notification data
        $shortCode = [
            'fullname'     => $checkoutData['shipping_address']['name'] ?? ($order->user->fullname ?? 'Customer'),
            'site_name'    => gs('site_name'),
            'order_number' => $order->order_number,
            'amount'       => $order->total_amount,
            'currency'     => gs('cur_text'),
            'order_url'    => $order->user_id
                ? route('user.order', $order->order_number, false)
                : url('/guest/order/' . $order->order_number),
        ];

        // if ($order->guest_email) {
        //     notify(
        //         [
        //             'email'    => $order->guest_email,
        //             'fullname' => $shortCode['fullname'],
        //         ],
        //         'ORDER_CONFIRMATION',
        //         $shortCode,
        //         ['email'],
        //         false // send immediately, not queued
        //     );
        // } elseif ($order->user_id) {
        //     notify(
        //         $order->user,
        //         'ORDER_CONFIRMATION',
        //         $shortCode,
        //         ['email'],
        //         false // send immediately, not queued
        //     );
        // }

        Log::info('[ORDER_CONFIRMATION] Preparing to send notification', [
            'email' => $order->guest_email,
            'shortCode' => [
                'fullname' => $checkoutData['shipping_address']['name'] ?? 'Guest User',
                'site_name' => gs('site_name'),
                'order_number' => $order->order_number,
                'amount' => $order->total_amount,
                'currency' => gs('cur_text'),
                'order_url' => url('/guest/order/' . $order->order_number),
            ]
        ]);

        notify(
            [
                'email' => $order->guest_email,
                'fullname' => $checkoutData['shipping_address']['name'] ?? 'Guest User'
            ],
            'ORDER_CONFIRMATION',
            [
                'fullname'     => $checkoutData['shipping_address']['name'] ?? 'Guest User',
                'site_name'    => gs('site_name'),
                'order_number' => $order->order_number,
                'amount'       => $order->total_amount,
                'currency'     => gs('cur_text'),
                'order_url'    => url('/guest/order/' . $order->order_number),
            ],
            ['email'],
            false
        );

        // dd('Notification sent. Check logs for more detail.');
        // dd(session('mail_error'));

        // dd('mail_error', session('mail_error'));

        $trx = $order->initiatePayment($gatewayCurrency);

        if (!$order->is_cod) {
            session()->put('Track', $trx);
            session()->put('order_id', $order->id);
            return to_route('user.deposit.confirm');
        } else {
            $this->cartManager->clearUserCart($order->user_id);
            session()->forget('checkout_data');
        }

        $notify[] = ['success', 'Your order has submitted successfully'];
        return redirect()->route('user.checkout.confirmation', $order->order_number)->withNotify($notify);
    }

    // private function sendAdminNotification($order)
    // {
    //     $adminNotification = new AdminNotification();
    //     $adminNotification->user_id = $order->user_id;
    //     $adminNotification->title = 'New order #' . $order->order_number . ' has been created';
    //     $adminNotification->click_url = urlPath('admin.order.index') . '?search=' . $order->order_number;
    //     $adminNotification->save();
    // }

    private function sendAdminNotification($order)
    {
        $adminNotification = new AdminNotification();

        // store 0 (or null) when guest
        $adminNotification->user_id = $order->user_id ?? 0;  // <-- change here

        $adminNotification->title    = 'New order #' . $order->order_number . ' has been created';
        $adminNotification->click_url = urlPath('admin.order.index') . '?search=' . $order->order_number;

        $adminNotification->save();
    }

    private function validation($request)
    {
        $request->validate([
            'gateway' => 'required',
            'currency' => 'required',
        ]);
    }

    private function getGatewayCurrency($request)
    {
        $gatewayCurrency = null;

        if ($request->gateway != 0) {
            $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gatewayCurrency) {
                $gatewayCurrency->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();
        } else {
            // COD is selected
            if (!gs('cod')) {
                $gatewayCurrency = null;
            } else {
                $gatewayCurrency = (new GatewayCurrency())->codMethod();
            }
        }

        if (!$gatewayCurrency) {
            throw ValidationException::withMessages(['error' => 'Invalid gateway selected']);
        }

        return $gatewayCurrency;
    }

    private function getCheckoutData($hasPhysicalProduct)
    {
        $checkoutData = session('checkout_data');


        // dd($checkoutData);

        if (!$checkoutData && $hasPhysicalProduct) {
            throw ValidationException::withMessages(['error' => 'Invalid session data']);
        }

        return $checkoutData;
    }


    private function getShippingAddress($hasPhysicalProduct, $checkoutData)
    {
        $shippingAddress = null;


        if ($hasPhysicalProduct) {
            $shippingAddress = ShippingAddress::where('user_id', auth()->id())->find($checkoutData['shipping_address_id']);

            if (!$shippingAddress) {
                throw ValidationException::withMessages(['error' => 'Invalid session data']);
            }
        }

        return $shippingAddress;
    }

    private function getShippingMethod($hasPhysicalProduct, $checkoutData)
    {
        $shippingMethod = null;

        if ($hasPhysicalProduct) {
            $shippingMethod = ShippingMethod::active()->find($checkoutData['shipping_method_id']);

            if (!$shippingMethod) {
                throw ValidationException::withMessages(['error' => 'Invalid session data']);
            }
        }

        return $shippingMethod;
    }

    private function saveOrder($subtotal, $coupon, $gatewayCurrency, $cartData, $hasPhysicalProduct)
    {
        $checkoutData = $this->getCheckoutData($hasPhysicalProduct);

        $shippingAddress = session('checkout_data.shipping_address');
        $shippingMethod = $this->getShippingMethod($hasPhysicalProduct, $checkoutData);

        $couponAmount = $coupon->discount_amount ?? 0;
        $couponAmount = $couponAmount > $subtotal ? $subtotal : $couponAmount;

        $order = new Order();
        $order->order_number = $this->getOrderNumber();
        $order->user_id = auth()->id();
        $order->guest_email = $checkoutData['guest_email'] ?? null;
        $order->shipping_address =  $shippingAddress ? json_encode($shippingAddress) : null;;

        if (!auth()->check()) {
            $order->guest_name    = $checkoutData['shipping_address']['name'] ?? null;
            $order->guest_phone   = $checkoutData['shipping_address']['phone'] ?? null;
            $order->guest_address = $checkoutData['shipping_address']['address'] ?? null;

            // ⬇ NEW – copy the location fields
            $order->division_id = $checkoutData['division_id'] ?? null;
            $order->district_id = $checkoutData['district_id'] ?? null;
            $order->area_name   = $checkoutData['area_name']   ?? null;
            $order->postcode    = $checkoutData['postcode']    ?? null;
        }

        $order->shipping_method_id = $shippingMethod->id ?? 0;
        $order->shipping_charge = $shippingMethod->charge ?? 0;
        $order->is_cod = $gatewayCurrency->id ? 0 : 1;
        $order->payment_status = Status::PAYMENT_INITIATE;
        $order->subtotal = $subtotal;
        $order->total_amount = getAmount($subtotal + ($shippingMethod->charge ?? 0) - $couponAmount);
        $order->save();

        $this->saveOrderDetails($cartData, $order->id);

        return $order;
    }



    private function getOrderNumber($digit = 5)
    {
        $prefix = 'OID-';
        $last = Order::max('id') + 1;
        $formattedLast = str_pad($last, $digit, '0', STR_PAD_LEFT);
        return $prefix . $formattedLast;
    }

    private function checkStock($cartData)
    {
        foreach ($cartData as $cart) {
            if ($cart->product->track_inventory) {
                $stockQuantity = $cart->product->inStock($cart->productVariant);

                if ($cart->quantity > $stockQuantity) {
                    $notify[] = ['error', 'Some products are stocked out'];
                    return to_route('cart.page')->withNotify($notify);
                }
            }
        }
    }

    private function setShippingAddress(ShippingAddress $address)
    {
        return [
            'firstname' => $address->firstname,
            'lastname' => $address->lastname,
            'mobile' => $address->mobile,
            'country' => $address->country,
            'city' => $address->city,
            'state' => $address->state,
            'zip' => $address->zip,
            'address' => $address->address,
        ];
    }

    private function saveOrderDetails($cartData, $orderId)
    {
        foreach ($cartData as $cartItem) {
            $prices = $cartItem->product->prices($cartItem->productVariant);
            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $orderId;
            $orderDetail->product_id = $cartItem->product_id;
            $orderDetail->product_variant_id = $cartItem->product_variant_id ?? 0;
            $orderDetail->quantity = $cartItem->quantity;
            $orderDetail->price = $prices->sale_price;
            $orderDetail->discount = $prices->regular_price - $prices->sale_price;
            $orderDetail->save();
            $this->updateStock($cartItem, $orderId);
        }
    }

    private function appliedCoupon($cartData, $subtotal)
    {
        $coupon = session('coupon');

        if (!$coupon) {
            return null;
        }

        // Match the coupon code with database and check is exists
        $coupon = $this->cartManager->getCouponByCode($coupon['code']);

        if (!$coupon) {
            return ['error' => "Applied coupon is invalid or expired"];
        }


        $checkCoupon = $this->cartManager->isValidCoupon($coupon, $subtotal, $cartData);

        if (isset($checkCoupon['error'])) {
            return $checkCoupon;
        }

        $coupon->discount_amount = $coupon->discountAmount($subtotal);

        return $coupon;
    }

    private function updateStock($cartItem, $orderId)
    {
        if ($cartItem->productVariant) {
            $item = $cartItem->productVariant;
        } else {
            $item = $cartItem->product;
        }

        if ($item->track_inventory) {
            $item->in_stock -= $cartItem->quantity;
            $item->save();

            $description = "Sold $cartItem->quantity " . Str::plural('product', $cartItem->quantity);
            $productManager = new ProductManager();
            $productManager->createStockLog($cartItem->product, $cartItem->quantity, $description, $cartItem->productVariant, '-', $orderId);
        }
    }

    private function saveAppliedCoupon($coupon, $order)
    {
        $appliedCoupon = new AppliedCoupon();
        $appliedCoupon->user_id = auth()->id();
        $appliedCoupon->coupon_id = $coupon->id;
        $appliedCoupon->order_id = $order->id;
        $appliedCoupon->amount = $coupon->discount_amount > $order->subtotal ? $order->subtotal : $coupon->discount_amount;
        $appliedCoupon->save();
        session()->forget('coupon');
    }
}
