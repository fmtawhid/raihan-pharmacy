<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CartManager;
use App\Lib\WishlistManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offer;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ProductController extends Controller
{
    private $pageTitle;
    private $seoContents;

    public function products()
    {
        $this->pageTitle   = 'Products';
        return $this->getProducts('shopPageProducts');
    }

    private function productRelations()
    {
        return [
            'productVariants' => fn($q) => $q->published(),
            'brand:id,name,slug',
            'categories:id,parent_id,name',
            'activeOffer'
        ];
    }

    public function productByCategory($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $parents = $category->allParents();

        $breadcrumbLinks = [];

        foreach ($parents as $parent) {
            $breadcrumbLinks[$parent->name] = $parent->shopLink();
        }

        $breadcrumbLinks[$category->name] = null;

        $this->pageTitle   = $category->name;

        request()->merge(['category_id' => $category->id]);

        $scope = function ($query) use ($category) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('category_id', $category->id);
            });
        };

        $this->seoContents = getSeoContents($category);

        return $this->getProducts($scope, $breadcrumbLinks);
    }

    public function productsByBrand($slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();
        $this->pageTitle   = $brand->name;

        $breadcrumbLinks = [
            $brand->name => $brand->shopLink()
        ];

        request()->merge(['brand_id' => $brand->id]);

        $scope = function ($query) use ($brand) {
            $query->where('brand_id', $brand->id);
        };

        $this->seoContents = getSeoContents($brand);

        return $this->getProducts($scope, $breadcrumbLinks);
    }

    private function getProducts($scope = null, $breadcrumbLinks = [])
    {
        /** @var \Illuminate\Http\Request $request */
        $request      = request();

        /*--------------------------------------------------------------
    | 1. Build the base query
    *-------------------------------------------------------------*/
        $allProducts  = Product::published();               // only published
        if ($scope && !$request->search) {
            $scope instanceof Closure ? $scope($allProducts)
                : $allProducts->$scope();
        }

        /*--------------------------------------------------------------
    | 2. Search & basic filters
    *-------------------------------------------------------------*/
        if ($request->search) {
            $allProducts->searchable([
                'products.name',
                'products.summary',
                'products.description'
            ]);
        }

        if ($request->has('brand')) {
            $allProducts->whereHas('brand', function ($q) use ($request) {
                $q->whereIn('slug', explode(',', $request->brand));
            });
        }

        if ($request->has('attribute_values')) {
            $attributeValues = explode(',', $request->attribute_values);
            $allProducts->whereHas(
                'attributeValues',
                fn($q) => $q->whereIn('id', $attributeValues)
            );
        }

        /*--------------------------------------------------------------
    | 3. ALWAYS restrict to products whose offer is running NOW
    *-------------------------------------------------------------*/
        // $allProducts->hasRunningOffer();

        $offerFilter   = $request->discount_type
            || $request->has('min_discount')
            || $request->has('max_discount');

        if ($offerFilter) {
            $allProducts->whereHas('activeOffer', function ($q) use ($request) {
                $q->running();                         // keep only running offers

                /* discount‑type checkbox */
                if ($request->discount_type) {
                    $q->where('discount_type', $request->discount_type);
                }

                /* discount range slider */
                if ($request->has('min_discount') || $request->has('max_discount')) {
                    $min = $request->min_discount ?? 0;
                    $max = $request->max_discount ?? 999999;
                    $q->amountBetween($min, $max);
                }
            });
        }

        /*--------------------------------------------------------------
    | 4. Rating filter
    *-------------------------------------------------------------*/
        if ($request->rating) {
            $allProducts->having('reviews_avg_rating', '>=', $request->rating);
        }

        /*--------------------------------------------------------------
    | 5. Offer‑specific filters (type & amount range)
    *-------------------------------------------------------------*/
        if ($request->discount_type) {              // fixed / percent
            $allProducts->whereHas(
                'activeOffer',
                fn($q) => $q->where('discount_type', $request->discount_type)
            );
        }

        if ($request->has('min_discount') || $request->has('max_discount')) {
            $min = $request->min_discount ?? 0;
            $max = $request->max_discount ?? 999999;
            $allProducts->whereHas(
                'activeOffer',
                fn($q) => $q->amountBetween($min, $max)
            );
        }

        /*--------------------------------------------------------------
    | 6. Price filter
    *-------------------------------------------------------------*/
        $minPrice = $maxPrice = null;

        if ($request->has('min_price') || $request->has('max_price')) {
            $minPrice = $request->min_price ?? 0;
            $maxPrice = $request->max_price ?? Product::max('regular_price');

            $allProducts->where(function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('regular_price', [$minPrice, $maxPrice])
                    ->orWhereNull('regular_price');
            });
        }

        /*--------------------------------------------------------------
    | 7. Data only needed on first (non‑Ajax) load
    *-------------------------------------------------------------*/
        if (!$request->ajax()) {

            // Fallback min/max price if not supplied
            $minPrice ??= (clone $allProducts)->min('regular_price') ?? 0;
            $maxPrice ??= (clone $allProducts)->max('regular_price')
                ?? Product::max('regular_price');

            if ($minPrice == $maxPrice) {
                $maxPrice = Product::max('regular_price');
            }

            // Max fixed discount (for JS slider upper bound)
            $maxFixed = Offer::running()
                ->where('discount_type', Status::DISCOUNT_FIXED)
                ->max('amount') ?? 0;

            // Brand list (skip when on “by‑brand” route)
            $brands = collect();
            if (!Route::is('product.by.brand')) {
                $brands = (clone $allProducts)
                    ->join('brands', 'brands.id', '=', 'products.brand_id')
                    ->select('brands.*')
                    ->distinct()
                    ->get();
            }

            // Category list
            $categories = Route::is('product.by.category')
                ? Category::where('parent_id', $request->category_id)
                ->whereHas('products')
                ->orderBy('name')
                ->get()
                : $this->productCategories(clone $allProducts);

            // Attribute list
            $attributes = $this->productAttributes(clone $allProducts);

            $pageTitle  = $this->pageTitle;
        }

        /*--------------------------------------------------------------
    | 8. Sorting
    *-------------------------------------------------------------*/
        if ($request->sort_by) {
            match ($request->sort_by) {
                'price_htl' => $allProducts->orderBy('regular_price', 'DESC'),
                'price_lth' => $allProducts->orderBy('regular_price', 'ASC'),
                'oldest'    => $allProducts->orderBy('id',            'ASC'),
                'latest'    => $allProducts->orderBy('id',            'DESC'),
                default     => null,
            };
        } else {
            $allProducts->orderBy('id', 'DESC');
        }

        /*--------------------------------------------------------------
    | 9. Pagination + eager loads
    *-------------------------------------------------------------*/
        $products = $allProducts
            ->with($this->productRelations())
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->paginate(getPaginate($request->per_page ?? 24));

        /*--------------------------------------------------------------
    | 10. Ajax response
    *-------------------------------------------------------------*/
        if ($request->ajax()) {
            return response()->json([
                'html'           => view(
                    'Template::partials.products_filter',
                    compact('products')
                )->render(),
                'total_products' => $products->total(),
            ]);
        }

        /*--------------------------------------------------------------
    | 11. Full page response
    *-------------------------------------------------------------*/
        $minPrice   = floor($minPrice);
        $maxPrice   = ceil($maxPrice);
        $seoContents = $this->seoContents;

        $breadcrumbs = Route::is('product.all')
            ? ['Home' => route('home'), 'Products' => null]
            : array_merge(['Home' => route('home')], $breadcrumbLinks);

        return view('Template::products', compact(
            'products',
            'minPrice',
            'maxPrice',
            'pageTitle',
            'brands',
            'attributes',
            'categories',
            'seoContents',
            'breadcrumbs',
            'maxFixed'        // <= used by JS discount slider
        ));
    }


    private function productCategories($productsQuery)
    {
        return $productsQuery->selectRaw('products_categories.category_id, categories.name, categories.slug')
            ->join('products_categories', 'products.id', '=', 'products_categories.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products_categories.category_id')
            ->distinct()
            ->get();
    }

    private function productAttributes($productsQuery)
    {
        $attributeData = $productsQuery->selectRaw('attributes.id as attribute_id, attributes.name as attribute_name, attributes.type as attribute_type, attribute_values.name as attribute_value_name, attribute_values.id as attribute_value_id, attribute_values.value as attribute_value')
            ->leftJoin('attribute_value_product', 'products.id', '=', 'attribute_value_product.product_id')
            ->join('attribute_values', 'attribute_value_product.attribute_value_id', '=', 'attribute_values.id')
            ->join('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
            ->distinct('attribute_values.name')
            ->get();


        $attributes = [];

        foreach ($attributeData->groupBy('attribute_name') as $attributeItems) {
            $attributeData = $attributeItems->first();

            $data['name'] = $attributeData->attribute_name;
            $data['id'] = $attributeData->attribute_id;
            $data['type'] = $attributeData->attribute_type;

            $attributeValue = [];
            foreach ($attributeItems as $attribute) {
                $attributeValue[] = (object) [
                    "name" => $attribute->attribute_value_name,
                    "id" => $attribute->attribute_value_id,
                    "value" => $attribute->attribute_value,
                ];
            }
            $data['values'] = (object) $attributeValue;


            $attributes[] = (object) $data;
        }

        return $attributes;
    }

    public function productDetails($slug)
    {
        $product = Product::query();

        if (!auth()->guard('admin')->user()) {
            $product->published();
        }

        // $product = $product->with($this->productDetailsRelations())
        //     ->withCount('reviews')
        //     ->withAvg('reviews', 'rating')
        //     ->withCount('reviews')
        //     ->where('slug', $slug)
        //     ->firstOrFail();

        $product = $product->with($this->productDetailsRelations())
    ->withCount(['reviews as reviews_count' => function ($q) {
        $q->where('status', 'approve');
    }])
    ->withAvg(['reviews as reviews_avg_rating' => function ($q) {
        $q->where('status', 'approve'); // ✅
    }], 'rating')
    ->where('slug', $slug)
    ->firstOrFail();


        $quickView = request()->ajax();

        $images    = $product->galleryImages;

        if ($product->displayImage && !$images->contains($product->displayImage)) {
            $images = $images->prepend($product->displayImage);
        }

        if ($product->product_type == Status::PRODUCT_TYPE_VARIABLE && $product->attributes->count()) {
            $images = $this->sortGalleryImages($product, $images);
        }

        $data      = compact('product', 'images', 'quickView', 'images');

        if ($quickView) {
            return view('Template::partials.quick_view', $data);
        }

        list($otherProductsTitle, $otherProducts) = $this->getOtherProducts($product);

        $data['pageTitle'] = $product->meta_title ?? $product->name;
        $data['otherProductsTitle'] = $otherProductsTitle;
        $data['otherProducts']      = $otherProducts;
        $data['seoContents']        = getSeoContents($product);

        // Related products (same category, exclude current)
        $relatedProducts = Product::whereHas('categories', function ($q) use ($product) {
            $q->whereIn('categories.id', $product->categories->pluck('id'));
        })
        ->where('id', '!=', $product->id)
        ->latest()
        ->take(3)
        ->get();

        // Latest products (exclude current)
        $latestProducts = Product::where('id', '!=', $product->id)
            ->latest()
            ->take(3)
            ->get();

        $data['relatedProducts'] = $relatedProducts;
        $data['latestProducts']  = $latestProducts;

        return view('Template::product_details', $data);
    }

    private function sortGalleryImages($product, $images)
    {
        $mediaAttribute = $product->attributes->whereIn('type', [Status::ATTRIBUTE_TYPE_COLOR])->first();
        if (!$mediaAttribute) {
            return $images;
        }
        $mediaAttributeValues = $product->attributeValues->where('attribute_id', $mediaAttribute->id);
        $serialIds = $mediaAttributeValues->pluck('pivot.media_id');

        $sortedImages = $images->sortBy(function ($image) use ($serialIds) {
            $position = $serialIds->search($image->id);
            return $position !== false ? $position : PHP_INT_MAX;
        });

        return $sortedImages->values();
    }

    private function productDetailsRelations()
    {

        return [
            'brand:id,name,slug',
            'categories:id,parent_id,name,slug',
            'activeOffer',
            'productVariants' => function ($productVariant) {
                return $productVariant->published();
            },
            'attributes:id,name,type',
            'attributeValues:id,attribute_id,name,value',
            'galleryImages',
        ];
    }

    private function getOtherProducts($product, $limit = 6, $recentDays = 30)
    {
        $title = 'Similar Products';

        $query = Product::where('id', '!=', $product->id);

        if ($product->product_type_id) {
            $query->where('product_type_id', $product->product_type_id);
        } elseif ($product->categories) {
            $title = 'Related Products';

            $categoryIds = $product->categories->pluck('id');
            $leafCategoryIds = Category::whereIn('id', $categoryIds)
                ->whereNotIn('id', Category::pluck('parent_id')->filter())
                ->pluck('id');

            $query->whereHas('categories', function ($categoryQuery) use ($product, $leafCategoryIds) {
                $categoryQuery->whereIn('categories.id', $leafCategoryIds);
            });
        }

        $products = $query->with([
            'brand',
            'productVariants' => fn($q) => $q->published(),
        ])->withCount('reviews')
            ->take($limit)
            ->get();

        if ($products->isEmpty()) {
            $title = 'Latest Products';
            $products = Product::where('id', '!=', $product->id)
                ->where('created_at', '>=', now()->subDays($recentDays))
                ->latest()
                ->take($limit)
                ->get();

            if ($products->isEmpty()) {
                $title = 'Top Selling Products';
                $products = Product::topSales($limit);
            }
        }

        return [$title, $products];
    }

    public function geStockByVariant(Request $request, $slug)
    {
        $product         = Product::where('slug', $slug)->first();
        $attributeValues = prepareAttributeValues($request->variant);
        $variant         = ProductVariant::where('product_id', $product->id)
            ->published()
            ->with('product')
            ->where('attribute_values', $attributeValues)
            ->first();

        if (!$variant) {
            return errorResponse('This variants is not available now');
        }

        $stock = $variant->manage_stock ? $variant : $product;
        $trackInventory = $stock->track_inventory;

        $quantity       = $stock->in_stock;
        $showStock      = $stock->show_stock;
        $sku            = $variant->sku ?? $product->sku;

        $response = [
            'track_inventory' => $trackInventory,
            'show_stock'      => $showStock,
            'sku'             => $sku ?? trans('Not available'),
            'stock_quantity'  => $quantity,
            'price'           => $variant->salePrice(),
            'formatted_price' => $variant->formattedPrice($variant->product),
        ];

        if ($quantity <= 0 && $trackInventory) {
            return errorResponse('This variants is currently not available in our stock', $response);
        }

        return successResponse(null, $response);
    }

    public function getImagesByVariant(Request $request, $productId)
    {
        $attributeArray = json_decode($request->attribute_values);
        sort($attributeArray);
        $attributeArrayJson = json_encode($attributeArray);

        $product = Product::with('galleryImages', 'displayImage')->find($productId);
        $variant = ProductVariant::where('product_id', $product->id)->where('attribute_values', $attributeArrayJson)->with('galleryImages', 'displayImage')->first();


        $displayImage = $variant->displayImage ?? $product->displayImage;

        if ($variant && $variant->galleryImages->count()) {
            $images  = $variant->galleryImages;
        } else {
            $images = $product->galleryImages;
        }

        // add the main image to the collection
        if ($displayImage && !$images->contains($displayImage)) {
            $images = $images->prepend($displayImage);
        }

        if ($variant) {
            $images = $this->sortGalleryImages($product, $images);
        }

        $mediaIds = $images->pluck('id')->toArray();
        $view = view('Template::partials.product_images', compact('images', 'product'))->render();

        return successResponse('', ['images' => $view, 'media_ids' => $mediaIds]);
    }

    public function reviews(Request $request, $id)
    {
        $reviews   = ProductReview::with('user')->where('product_id', $id)->where('status', 'approve')->latest()->paginate(getPaginate(5));

        return view('Template::partials.product_review', compact('reviews'));
    }

    public function compareWishlistAndCartData()
    {
        try {
            // compare data
            if (!session('compare')) {
                $compareProductCount = 0;
            } else {
                $productIds   = array_keys(session('compare', []));
                $compareProductCount = Product::published()->whereIn('id', $productIds)->count();
            }


            // cart data
            $cartProductCount =  (new CartManager)->setCartCount();
            $cartSubtotal =  (new CartManager)->subtotal();

            // wishlist data
            $wishlistProductCount = (new WishlistManager)->getWishlistCount();

            return response()->json([
                'status' => true,
                'data' => [
                    'compare_products'  => $compareProductCount,
                    'cart_products'     => $cartProductCount,
                    'cart_subtotal'     => $cartSubtotal,
                    'wishlist_products' => $wishlistProductCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
