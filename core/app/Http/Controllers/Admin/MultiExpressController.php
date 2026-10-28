<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\MultiExpressCategory;
use App\Models\MultiExpressDeal;
use App\Models\MultiExpressFeature;
use App\Models\MultiExpressOrder;
class MultiExpressController extends Controller
{


    /* ---------------- Category CRUD ---------------- */

    public function categoryIndex()
    {
        $pageTitle = 'All Multi Express Categories';
        $categories = MultiExpressCategory::latest()->paginate(20);
        return view('admin.multi_express.category_index', compact('pageTitle','categories'));
    }

    public function categoryCreate()
    {
        $pageTitle = 'Create Category';
        return view('admin.multi_express.category_create', compact('pageTitle'));
    }

    public function categorySave(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $category = $id ? MultiExpressCategory::findOrFail($id) : new MultiExpressCategory();
        $category->name = $request->name;
        $category->slug = \Str::slug($request->name);
        $category->status = $request->status;
        $category->save();

        $notify = $id ? ['success','Category updated successfully'] : ['success','Category created successfully'];
        return back()->withNotify([$notify]);
    }

    public function categoryEdit($id)
    {
        $pageTitle = 'Edit Category';
        $category = MultiExpressCategory::findOrFail($id);
        return view('admin.multi_express.category_create', compact('pageTitle','category'));
    }

    public function categoryDelete($id)
    {
        $category = MultiExpressCategory::findOrFail($id);
        $category->delete();
        return back()->withNotify([['success','Category deleted successfully']]);
    }

    /* ---------------- Deal CRUD ---------------- */

    public function dealIndex()
    {
        $pageTitle = 'All Deals';

        // Load paginated deals with category
        $deals = MultiExpressDeal::with('category')->latest()->paginate(20);

        // Aggregate stats for dashboard cards
        $totalDeals = MultiExpressDeal::count();
        $totalOrders = MultiExpressDeal::with('orders')->get()->sum(fn($deal) => $deal->orders->count());
        $totalProductBookings = MultiExpressDeal::with('orders')->get()->sum(fn($deal) => $deal->orders->sum('quantity'));
        $totalPayments = MultiExpressDeal::with(['orders.payments'])->get()->sum(fn($deal) => 
            $deal->orders->sum(fn($order) => $order->payments->where('status','paid')->sum('amount'))
        );
        $remainingPayments = MultiExpressDeal::with(['orders.payments'])->get()->sum(fn($deal) => 
            $deal->orders->sum(fn($order) => $order->total_price - $order->payments->where('status','paid')->sum('amount'))
        );

        return view('admin.multi_express.deal_index', compact(
            'pageTitle', 'deals', 
            'totalDeals', 'totalOrders', 'totalProductBookings', 'totalPayments', 'remainingPayments'
        ));
    }


    public function dealCreate()
    {
        $pageTitle = 'Create Deal';
        $categories = MultiExpressCategory::where('status','active')->get();
        $deal = new MultiExpressDeal();
        return view('admin.multi_express.deal_create', compact('pageTitle','categories','deal'));
    }

public function dealSave(Request $request, $id = 0)
{
    $request->validate([
        'category_id' => 'required|exists:multi_express_categories,id',
        'title' => 'required|string|max:150',
        'deal_price' => 'required|numeric',
        'regular_price' => 'nullable|numeric',
        'purchase_limit_per_user' => 'nullable|integer|min:1',
        'delivery_start_date' => 'nullable|date',
        'delivery_end_date' => 'nullable|date|after:delivery_start_date',
        'features.*.title' => 'nullable|string|max:150',
        'pricing_tiers.*.min_quantity' => 'nullable|integer|min:1',
        'pricing_tiers.*.max_quantity' => 'nullable|integer|min:1',
        'pricing_tiers.*.price_per_item' => 'nullable|numeric|min:0',
        'delivery_options.*.type' => 'nullable|string|max:50',
        'delivery_options.*.label' => 'nullable|string|max:50',
        'delivery_options.*.charge_per_item' => 'nullable|numeric|min:0',
        'feature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',  // 10 MB
        'status' => 'required|in:active,upcoming,closed',
        'stock_status' => 'required|in:ready,upcoming,sold_out',
        'deal_start_time' => 'nullable|date',
        'deal_end_time' => 'nullable|date|after:deal_start_time',



    ]);

    $deal = $id ? MultiExpressDeal::findOrFail($id) : new MultiExpressDeal();

    // Basic Fields
    $deal->category_id = $request->category_id;
    $deal->title = $request->title;
    $deal->slug = \Str::slug($request->title);
    $deal->short_description = $request->short_description;
    $deal->description = $request->description;
    $deal->deal_price = $request->deal_price;
    $deal->regular_price = $request->regular_price ?? $request->deal_price;
    $deal->discount_percent = $request->discount_percent ?? 0;
    $deal->min_required = $request->min_required;
    $deal->max_capacity = $request->max_capacity;
    $deal->purchase_limit_per_user = $request->purchase_limit_per_user ?? null;

    $deal->delivery_start_date = $request->delivery_start_date;
    $deal->delivery_end_date = $request->delivery_end_date;
    $deal->delivery_note = $request->delivery_note;
    $deal->status = $request->status;
    $deal->stock_status = $request->stock_status;
    $deal->deal_start_time = $request->deal_start_time;
    $deal->deal_end_time = $request->deal_end_time;



    // Feature Image Upload (safe)

    if ($request->hasFile('feature_image')) {
        $file = $request->file('feature_image');
        if ($file->isValid()) {
            $filename = time() . '_' . $file->getClientOriginalName();

            // Absolute path outside core/public
            $destinationPath = realpath(base_path('../assets/images/multiexpress')) ?: base_path('../assets/images/multiexpress');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            // URL save করর জন্য relative path
            $deal->feature_image = 'assets/images/multiexpress/'.$filename;
        }
    }








    // Gallery removed entirely to prevent "Path cannot be empty" error

    $deal->save();


    // Save Pricing Tiers
    if($request->pricing_tiers){
        $deal->pricingTiers()->delete();
        foreach($request->pricing_tiers as $tier){
            if(!empty($tier['min_quantity'])){
                $deal->pricingTiers()->create($tier);
            }
        }
    }

    // Save Delivery Options
    if($request->delivery_options){
        $deal->deliveryOptions()->delete();
        foreach($request->delivery_options as $delivery){
            if(!empty($delivery['type'])){
                $deal->deliveryOptions()->create($delivery);
            }
        }
    }

    $notify = $id ? ['success','Deal updated successfully'] : ['success','Deal created successfully'];
    return redirect()->route('admin.multi_express.deal.index')->withNotify([$notify]);
}



    public function dealEdit($id)
    {
        $pageTitle = 'Edit Deal';
        $deal = MultiExpressDeal::findOrFail($id);
        $categories = MultiExpressCategory::where('status','active')->get();
        return view('admin.multi_express.deal_create', compact('pageTitle','deal','categories'));
    }

    public function dealDelete($id)
    {
        $deal = MultiExpressDeal::findOrFail($id);
        $deal->delete();
        return back()->withNotify([['success','Deal deleted successfully']]);
    }

    public function show($id)
    {
        $deal = MultiExpressDeal::with(['orders.user', 'orders.deliveryOption'])->findOrFail($id);
        
        $pageTitle = "Deal Details - " . $deal->title;

        return view('admin.multi_express.deal_show', compact('deal', 'pageTitle'));
    }
   
    /* ---------------- Deal Order CRUD ---------------- */

    public function orderIndex($deal_id)
    {
        $deal = MultiExpressDeal::findOrFail($deal_id);
        $pageTitle = 'Orders for: ' . $deal->title;
        $orders = $deal->orders()->latest()->paginate(20);
        return view('admin.multi_express.order_index', compact('pageTitle','deal','orders'));
    }

public function orderShow($deal_id, $order_id)
{
    // Deal load করা হচ্ছে
    $deal = MultiExpressDeal::findOrFail($deal_id);

    // Order load করা চ্ে, user & deliveryOption eager load করা হলো
    $order = $deal->orders()
                 ->with(['user', 'deliveryOption'])
                 ->findOrFail($order_id);

    $pageTitle = 'Order Details: ' . $order->name;
    // dd($deal, $order) ;
    return view('admin.multi_express.order_show', compact('pageTitle', 'deal', 'order'));
}


public function orderStatusUpdate(Request $request, $deal_id, $order_id)
{
    $deal = MultiExpressDeal::findOrFail($deal_id);
    $order = $deal->orders()->findOrFail($order_id);

    $request->validate([
        'status' => 'required|in:pending,processing,delivered,cancelled'
    ]);

    // Use delivery_status instead of status
    $order->delivery_status = $request->status;
    $order->save();

    return back()->withNotify([['success','Order status updated successfully']]);
}


    public function orderDelete($deal_id, $order_id)
    {
        $deal = MultiExpressDeal::findOrFail($deal_id);
        $order = $deal->orders()->findOrFail($order_id);
        $order->delete();
        return back()->withNotify([['success','Order deleted successfully']]);
    }

    // OrderController.php
    public function showPaymentPage($orderId)
    {
        $order = MultiExpressOrder::findOrFail($orderId);
        $pageTitle = 'Add Payment for Order #' . $order->id;
        return view('admin.multi_express.payment_page', compact('order', 'pageTitle'));
    }

    // public function addPayment(Request $request)
    // {
    //     $request->validate([
    //         'order_id' => 'required|exists:multi_express_orders,id',
    //         'amount' => 'required|numeric|min:0.01',
    //         'payment_type' => 'required|in:full,partial'
    //     ]);

    //     $order = MultiExpressOrder::findOrFail($request->order_id);

    //     $payment = $order->payments()->create([
    //         'user_id' => $order->user_id,
    //         'amount' => $request->amount,
    //         'payment_type' => $request->payment_type,
    //         'status' => 'paid',
    //         'note' => $request->note
    //     ]);

    //     return back()->with('success', 'Payment added successfully.');
    // }
public function addPayment(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:multi_express_orders,id',
        'amount' => 'required|numeric|min:0.01',
        'payment_type' => 'required|in:full,partial'
    ]);

    $order = MultiExpressOrder::findOrFail($request->order_id);

    // Create payment
    $payment = $order->payments()->create([
        'user_id' => $order->user_id,
        'amount' => $request->amount,
        'payment_type' => $request->payment_type,
        'status' => 'paid',
        'note' => $request->note
    ]);

    // Update order payment_status if full payment made and total paid >= total_price
    $totalPaid = $order->totalPaid();

    if ($request->payment_type === 'full' && $totalPaid >= $order->total_price) {
        $order->payment_status = 'paid';
        $order->save();
    }

    // Redirect to order show page
    return redirect()->route(
        'admin.admin.multi_express.order.show',
        ['deal_id' => $order->deal_id, 'order_id' => $order->id]
    )->with('success', 'Payment added successfully.');
}



    public function paymentIndex()
    {
        $pageTitle = 'All Order Payments';

        // Load payments with order and user info
        $payments = \App\Models\MultiExpressOrderPayment::with(['order', 'user'])->latest()->paginate(20);

        return view('admin.multi_express.payment_index', compact('pageTitle','payments'));
    }



}
