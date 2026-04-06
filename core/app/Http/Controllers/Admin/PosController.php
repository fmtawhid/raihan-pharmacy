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
        return view('admin.pos.index', compact('pageTitle'));
    }

    // Debug: Check if data exists
    public function debugData()
    {
        return response()->json([
            'products_count' => Product::count(),
            'users_count' => \App\Models\User::count(),
            'sample_products' => Product::limit(3)->get(['id', 'name', 'regular_price', 'sale_price']),
            'sample_users' => \App\Models\User::limit(3)->get(['id', 'name', 'email'])
        ]);
    }

    // AJAX: Get paginated products
    public function getProducts(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $totalProducts = Product::count();
        $totalPages = ceil($totalProducts / $perPage);

        // Fetch products with pagination (include brand relationship)
        $products = Product::with('brand')
            ->offset($offset)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'sku'   => $p->sku,
                    'brand_name' => $p->brand?->name ?? null,
                    'regular_price' => $p->regular_price ?? null,
                    'sale_price' => $p->sale_price ?? null,
                    'wholesale_price' => $p->wholesale_price ?? null,
                    'in_stock' => $p->in_stock ?? 0,
                    'stock' => $p->in_stock ?? 0, // For compatibility with frontend
                ];
            });

        return response()->json([
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalProducts,
                'per_page' => $perPage
            ]
        ]);
    }

    // AJAX: Get product by ID
    public function getProductById($id)
    {
        try {
            $product = Product::with('brand')->find($id);
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'product' => [
                    'id'    => $product->id,
                    'name'  => $product->name,
                    'brand_name' => $product->brand?->name ?? null,
                    'regular_price' => $product->regular_price ?? null,
                    'sale_price' => $product->sale_price ?? null,
                    'wholesale_price' => $product->wholesale_price ?? null,
                    'in_stock' => $product->in_stock ?? 0,
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('getProductById error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching product: ' . $e->getMessage()
            ], 500);
        }
    }

    // AJAX: Search products
    public function searchProducts(Request $request)
    {
        $query = trim($request->input('query', $request->query('query', '')));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with('brand')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orderBy('id', 'desc')
            ->limit(120)
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'sku'   => $p->sku,
                    'brand_name' => $p->brand?->name ?? null,
                    'regular_price' => $p->regular_price ?? null,
                    'sale_price' => $p->sale_price ?? null,
                    'wholesale_price' => $p->wholesale_price ?? null,
                    'in_stock' => $p->in_stock ?? 0,
                    'stock' => $p->in_stock ?? 0, // For compatibility with frontend
                ];
            });

        return response()->json($products);
    }

    // AJAX: Add product to cart session
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('pos_cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->sale_price ?? $product->regular_price ?? 0,
                'wholesale_price' => $product->wholesale_price ?? null,
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
        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            session()->put('pos_cart', $cart);
        }
        return response()->json(['cart' => $cart]);
    }

    // AJAX: Update product quantity
    public function updateQty(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        $productId = $request->product_id;
        $action = $request->action;

        if (isset($cart[$productId])) {
            if ($action === 'increment') {
                $cart[$productId]['quantity']++;
            } elseif ($action === 'decrement') {
                if ($cart[$productId]['quantity'] > 1) {
                    $cart[$productId]['quantity']--;
                } else {
                    // Remove if quantity becomes 0
                    unset($cart[$productId]);
                }
            }
            session()->put('pos_cart', $cart);
        }

        return response()->json(['cart' => $cart]);
    }

    public function updateQtyDirect(Request $request)
    {
        $request->validate([
            'product_id' => 'required|numeric',
            'quantity' => 'required|numeric|min:1|max:9999'
        ]);

        $cart = session()->get('pos_cart', []);
        $productId = $request->product_id;
        $newQuantity = (int) $request->quantity;

        if (isset($cart[$productId])) {
            if ($newQuantity < 1) {
                // Remove if quantity is less than 1
                unset($cart[$productId]);
            } else {
                // Set the quantity directly
                $cart[$productId]['quantity'] = $newQuantity;
            }
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
        if (!$customer) {
            return response()->json(['customer' => null]);
        }

        $customerName = $customer->name;
        if (!$customerName && isset($customer->firstname, $customer->lastname)) {
            $customerName = trim($customer->firstname . ' ' . $customer->lastname);
        }
        if (!$customerName) {
            $customerName = $customer->email ?? 'Customer';
        }

        return response()->json(['customer' => [
            'id' => $customer->id,
            'name' => $customerName,
            'email' => $customer->email ?? null,
            'mobile' => $customer->mobile ?? null
        ]]);
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
        try {
            $request->validate(['name' => 'required|string|max:255']);

            // Split name into firstname and lastname
            $fullName = trim($request->name);
            $nameParts = explode(' ', $fullName, 2);
            $firstname = $nameParts[0] ?? '';
            $lastname = isset($nameParts[1]) ? $nameParts[1] : '';

            // Set password: use phone number if provided, otherwise random
            $password = $request->mobile ? bcrypt($request->mobile) : bcrypt(\Illuminate\Support\Str::random(8));

            $data = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $request->email ?? null,
                'mobile' => $request->mobile ?? null,
                'address' => $request->address ?? null,
                'password' => $password,
                'status' => Status::USER_ACTIVE ?? 1,
                'ev' => Status::VERIFIED ?? 1,
                'sv' => Status::VERIFIED ?? 1,
            ];

            $user = \App\Models\User::create($data);
            session()->put('pos_customer', $user->id);

            $customerName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
            if (!$customerName) {
                $customerName = $user->email ?? 'Customer';
            }

            return response()->json(['customer' => [
                'id' => $user->id,
                'name' => $customerName,
                'email' => $user->email ?? null,
                'mobile' => $user->mobile ?? null
            ]]);
        } catch (\Exception $e) {
            \Log::error('POS Create Customer Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating customer: ' . $e->getMessage()], 422);
        }
    }

    // AJAX: Select customer (store in session)
    public function selectCustomer(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->user_id);
        session()->put('pos_customer', $user->id);

        $customerName = $user->name;
        if (!$customerName && isset($user->firstname, $user->lastname)) {
            $customerName = trim($user->firstname . ' ' . $user->lastname);
        }
        if (!$customerName) {
            $customerName = $user->email ?? 'Customer';
        }

        return response()->json(['customer' => [
            'id' => $user->id,
            'name' => $customerName,
            'email' => $user->email ?? null,
            'mobile' => $user->mobile ?? null
        ]]);
    }

    // AJAX: Clear selected customer
    public function clearSelectedCustomer()
    {
        session()->forget('pos_customer');
        return response()->json(['customer' => null]);
    }

    // AJAX: Confirm order (uses cart from frontend)
    public function confirmOrder(Request $request)
    {
        \Log::info('POS confirmOrder called', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'all_input' => $request->all()
        ]);

        // Get cart from frontend request (not from session)
        $cartData = $request->input('cart', []);
        
        // If cart is empty, get from session (fallback)
        if (empty($cartData)) {
            $cartData = session()->get('pos_cart', []);
        }
        
        \Log::info('Cart data received:', ['cart' => $cartData]);
        
        if (empty($cartData)) {
            \Log::warning('POS order failed: empty cart');
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty'
            ], 400);
        }

        // Normalize cart data (convert from frontend format if needed)
        $cart = [];
        foreach ($cartData as $id => $item) {
            if (is_array($item)) {
                $cart[$id] = [
                    'name' => $item['name'] ?? '',
                    'price' => (float)($item['price'] ?? 0),
                    'wholesale_price' => isset($item['wholesale_price']) ? (float)$item['wholesale_price'] : null,
                    'quantity' => (int)($item['quantity'] ?? 1),
                ];
            }
        }

        \Log::info('Normalized cart:', ['cart' => $cart]);
        
        if (empty($cart)) {
            \Log::warning('POS order failed: no valid items in cart');
            return response()->json([
                'status' => 'error',
                'message' => 'No valid items in cart'
            ], 400);
        }

        // Snapshot cart items + customer
        $cartSnapshot = $cart;
        $customerId = session('pos_customer');
        $customerName = 'Walk-in Customer';
        if ($customerId) {
            $customer = \App\Models\User::find($customerId);
            if ($customer) {
                $customerName = $customer->name;
                if (!$customerName && isset($customer->firstname, $customer->lastname)) {
                    $customerName = trim($customer->firstname . ' ' . $customer->lastname);
                }
                if (!$customerName) {
                    $customerName = $customer->email ?? 'Walk-in Customer';
                }
            }
        }

        $priceType = $request->input('price_type', 'regular'); // 'regular' or 'wholesale'
        $discountType = $request->input('discount_type'); // 'percentage' or 'fixed'
        $discountAmount = floatval($request->input('discount_amount', 0));

        $order = null;
        
        try {
            DB::transaction(function () use ($cart, &$order, $priceType, $discountType, $discountAmount) {
                $subtotal = 0;
                foreach ($cart as $item) {
                    $useWholesale = ($priceType === 'wholesale') && !empty($item['wholesale_price']);
                    $unitPrice    = $useWholesale ? (float)$item['wholesale_price'] : (float)$item['price'];
                    $subtotal += $unitPrice * $item['quantity'];
                }

                // Calculate discount
                $actualDiscount = 0;
                if ($discountType === 'percentage' && $discountAmount > 0) {
                    $actualDiscount = ($subtotal * $discountAmount) / 100;
            } elseif ($discountType === 'fixed' && $discountAmount > 0) {
                $actualDiscount = min($discountAmount, $subtotal);
            }

            $total = $subtotal - $actualDiscount;

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
            foreach ($cart as $productId => $item) {
                $useWholesale = ($priceType === 'wholesale') && !empty($item['wholesale_price']);
                $unitPrice    = $useWholesale ? (float)$item['wholesale_price'] : (float)$item['price'];

                // Distribute discount proportionally across items
                $itemSubtotal = $unitPrice * $item['quantity'];
                $itemDiscount = ($itemSubtotal / $subtotal) * $actualDiscount;

                OrderDetail::create([
                    'order_id'           => $order->id,
                    'product_id'         => $productId,
                    'product_variant_id' => 0,
                    'quantity'           => $item['quantity'],
                    'price'              => $unitPrice,
                    'discount'           => round($itemDiscount, 2),
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
        } catch (\Throwable $e) {
            \Log::error('POS order creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'cart' => $cart,
                'price_type' => $priceType,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
        
        // If order was not created, return error
        if (!$order) {
            \Log::error('POS order creation failed: order object is null');
            return response()->json([
                'status' => 'error',
                'message' => 'Order creation failed - unknown error'
            ], 500);
        }

        // Build invoice items array
        $invoiceItems = [];
        $totalQty = 0;
        $subtotal = 0;
        foreach ($cartSnapshot as $productId => $item) {
            $useWholesale = ($priceType === 'wholesale') && !empty($item['wholesale_price']);
            $unitPrice    = $useWholesale ? (float)$item['wholesale_price'] : (float)$item['price'];
            $itemTotal = $unitPrice * $item['quantity'];
            $subtotal += $itemTotal;
            $invoiceItems[] = [
                'name'     => $item['name'],
                'qty'      => $item['quantity'],
                'price'    => $unitPrice,
                'total'    => $itemTotal,
            ];
            $totalQty += $item['quantity'];
        }

        // Calculate final discount for display
        $displayDiscount = 0;
        if ($discountType === 'percentage' && $discountAmount > 0) {
            $displayDiscount = ($subtotal * $discountAmount) / 100;
        } elseif ($discountType === 'fixed' && $discountAmount > 0) {
            $displayDiscount = min($discountAmount, $subtotal);
        }

        \Log::info('POS order created successfully', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'subtotal' => $subtotal,
            'discount' => $displayDiscount,
            'total' => $order->total_amount
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'POS Order Confirmed Successfully! Order #' . $order->order_number,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
            'invoice' => [
                'store_name'    => gs('site_name') ?? 'Store',
                'store_address' => gs('address') ?? gs('support_address') ?? 'Address not set',
                'store_phone'   => gs('phone_number') ?? gs('support_phone') ?? 'Phone not set',
                'currency_sym'  => gs('cur_sym') ?? '$',
                'order_number'  => $order->order_number,
                'date'          => now()->format('d-m-Y H:i:s'),
                'customer_name' => $customerName,
                'items'         => $invoiceItems,
                'subtotal'      => $subtotal,
                'discount'      => $displayDiscount,
                'total_qty'     => $totalQty,
                'grand_total'   => $order->total_amount,
                'sold_by'       => auth()->guard('admin')->user()->name ?? 'Admin',
            ],
        ]);
    }
}
