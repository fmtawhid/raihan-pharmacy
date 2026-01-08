<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Lib\ProductManager;
use App\Models\Deposit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class PosController extends Controller
{
    // POS main page
    public function Pos()
    {
        $pageTitle = 'Point of Sale';
        return view('admin.pos', compact('pageTitle'));
    }

    // AJAX: Search products
    
    public function searchProducts(Request $request)
    {
        $query = trim($request->query('query',''));

        if(strlen($query) < 2){
            return response()->json([]);
        }

        $products = Product::withoutGlobalScopes() // 🔥 THIS IS THE FIX
            ->where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'price' => $p->regular_price ?? $p->sale_price ?? 0,
                ];
            });

        return response()->json($products);
    }

    // AJAX: Add product to cart session
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('pos_cart', []);

        if(isset($cart[$product->id])){
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->regular_price ?? $product->sale_price ?? 0,
                'quantity' => 1,
            ];
        }

        session()->put('pos_cart', $cart);
        return response()->json(['cart' => $cart]);
    }

    // AJAX: Remove product from cart
    public function removeFromCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        if(isset($cart[$request->product_id])){
            unset($cart[$request->product_id]);
            session()->put('pos_cart', $cart);
        }
        return response()->json(['cart' => $cart]);
    }

    // AJAX: Get current cart
    public function getCart()
    {
        $cart = session()->get('pos_cart', []);
        return response()->json(['cart' => $cart]);
    }

    // AJAX: Clear cart
    public function clearCart()
    {
        session()->forget('pos_cart');
        return response()->json(['cart' => []]);
    }

    // AJAX: Get selected customer (if any)
    public function getSelectedCustomer()
    {
        $id = session('pos_customer');
        if (!$id) {
            return response()->json(['customer' => null]);
        }
        $customer = \App\Models\User::find($id);
        return response()->json(['customer' => $customer ? $customer->only(['id', 'name', 'email', 'mobile']) : null]);
    }

    // AJAX: Search customers (robust to different schema variants)
    public function searchCustomers(Request $request)
    {
        $q = trim($request->input('query', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        try {
            $hasName = \Illuminate\Support\Facades\Schema::hasColumn('users', 'name');
            $hasFirstname = \Illuminate\Support\Facades\Schema::hasColumn('users', 'firstname');
            $hasLastname = \Illuminate\Support\Facades\Schema::hasColumn('users', 'lastname');
            $hasUsername = \Illuminate\Support\Facades\Schema::hasColumn('users', 'username');
            $mobileColumn = null;
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'mobile')) {
                $mobileColumn = 'mobile';
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                $mobileColumn = 'phone';
            }

            $qb = \App\Models\User::query();

            $firstWhereAdded = false;
            if ($hasName) {
                $qb->where('name', 'like', "%{$q}%");
                $firstWhereAdded = true;
            } elseif ($hasFirstname && $hasLastname) {
                $qb->whereRaw("CONCAT(firstname, ' ', lastname) like ?", ["%{$q}%"]);
                $firstWhereAdded = true;
            } elseif ($hasUsername) {
                $qb->where('username', 'like', "%{$q}%");
                $firstWhereAdded = true;
            }

            // Always allow email and mobile searches
            if ($firstWhereAdded) {
                $qb->orWhere('email', 'like', "%{$q}%");
            } else {
                $qb->where('email', 'like', "%{$q}%");
            }

            if ($mobileColumn) {
                $qb->orWhere($mobileColumn, 'like', "%{$q}%");
            }

            $users = $qb->limit(10)->get();

            $result = $users->map(function ($u) use ($hasName, $hasFirstname, $hasLastname, $hasUsername, $mobileColumn) {
                if ($hasName) {
                    $name = $u->name;
                } elseif ($hasFirstname && $hasLastname) {
                    $name = trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')) ?: ($u->email ?? '');
                } elseif ($hasUsername) {
                    $name = $u->username ?? $u->email;
                } else {
                    $name = $u->email ?? 'User';
                }
                return [
                    'id' => $u->id,
                    'name' => $name,
                    'email' => $u->email ?? null,
                    'mobile' => $mobileColumn ? ($u->{$mobileColumn} ?? null) : null
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            // Log and return empty set when DB schema unexpected
            \Log::error('Customer search error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    // AJAX: Create a quick customer and select
    public function createCustomer(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $data = [
            'name' => $request->name,
            'email' => $request->email ?? null,
            'mobile' => $request->mobile ?? null,
            'password' => bcrypt(\Illuminate\Support\Str::random(8)),
            'status' => Status::USER_ACTIVE ?? 1,
            'ev' => Status::VERIFIED ?? 1,
            'sv' => Status::VERIFIED ?? 1,
        ];

        $user = \App\Models\User::create($data);
        session()->put('pos_customer', $user->id);

        return response()->json(['customer' => [
            'id' => $user->id,
            'name' => $user->name ?? ($user->firstname . ' ' . $user->lastname ?? $user->email),
            'email' => $user->email ?? null,
            'mobile' => $user->mobile ?? null
        ]]);
    }

    // AJAX: Select customer (store in session)
    public function selectCustomer(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->user_id);
        session()->put('pos_customer', $user->id);
        return response()->json(['customer' => $user->only(['id', 'name', 'email', 'mobile'])]);
    }

    // AJAX: Clear selected customer
    public function clearSelectedCustomer()
    {
        session()->forget('pos_customer');
        return response()->json(['customer' => null]);
    }

    // AJAX: Confirm order
    public function confirmOrder(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        if(empty($cart)){
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty'
            ]);
        }

        DB::transaction(function() use ($cart, &$order){
            $total = 0;
            foreach($cart as $item){
                $total += $item['price'] * $item['quantity'];
            }

            // Attach customer if selected
            $customerId = session('pos_customer');

            // Create order (delivered by default)
            $order = Order::create([
                'order_number'   => 'POS-' . time(),
                'user_id'        => $customerId ?? null,
                'status'         => Status::ORDER_DELIVERED,
                'total_amount'   => $total,
                'payment_status' => Status::PAYMENT_SUCCESS,
                'is_cod'         => true,
            ]);

            // Create order items and update stock
            foreach($cart as $productId => $item){
                OrderDetail::create([
                    'order_id'           => $order->id,
                    'product_id'         => $productId,
                    'product_variant_id' => 0,
                    'quantity'           => $item['quantity'],
                    'price'              => $item['price'],
                    'discount'           => 0,
                ]);

                // adjust stock if product tracks inventory
                try {
                    $product = Product::find($productId);
                    if ($product && $product->track_inventory) {
                        $product->in_stock = max(0, $product->in_stock - $item['quantity']);
                        $product->save();

                        $description = "Sold $item[quantity] " . Str::plural('product', $item['quantity']) . " (POS)";
                        $productManager = new ProductManager();
                        $productManager->createStockLog($product, $item['quantity'], $description, null, '-', $order->id);
                    }
                } catch (\Throwable $e) {
                    \Log::error('POS stock update error: ' . $e->getMessage());
                }
            }

            // Create a deposit record so payments show up in dashboards/reports
            try {
                $deposit = new Deposit();
                $deposit->user_id = $customerId ?? null;
                $deposit->order_id = $order->id;
                $deposit->amount = $total;
                $deposit->method_code = 0; // POS/manual
                $deposit->method_currency = gs('cur_text');
                $deposit->trx = 'POS-' . $order->id . '-' . time();
                $deposit->status = Status::PAYMENT_SUCCESS;
                $deposit->save();
            } catch (\Throwable $e) {
                \Log::error('POS deposit creation error: ' . $e->getMessage());
            }

            // Clear cart + selected customer
            session()->forget('pos_cart');
            session()->forget('pos_customer');
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'POS order confirmed successfully',
            'order_id'=> $order->id
        ]);
    }
}
