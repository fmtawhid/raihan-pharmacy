<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\BranchContext;
use Illuminate\Support\Facades\DB;

class ProductListController extends Controller
{

    public function index()
    {
        $pageTitle = "All Products";
        $trashed   = false;
        $products  = Product::select('id', 'name', 'slug', 'brand_id', 'is_published', 'is_showable', 'is_downloadable', 'product_type_id', 'product_type', 'regular_price', 'sale_price', 'in_stock', 'track_inventory', 'show_stock', 'deleted_at')
            ->searchable(['name', 'slug'])
            ->filter(['is_published', 'brand_id', 'is_showable', 'is_downloadable', 'product_type_id', 'product_type', 'is_published']);

        if (request()->has('category')) {
            $products = $products->whereHas('categories', function ($query) {
                $query->where('categories.id', request()->category);
            });
        }

        if (request()->sort_by) {
            if (request()->sort_by == 'price_htl') {
                $products->orderBy('regular_price', 'DESC');
            } else if (request()->sort_by == 'price_lth') {
                $products->orderBy('regular_price', 'ASC');
            } else if (request()->sort_by == 'oldest') {
                $products->orderBy('id', 'ASC');
            } else if (request()->sort_by == 'latest') {
                $products->orderBy('id', 'DESC');
            }
        } else {
            $products->orderBy('id', 'desc');
        }

        $products = $products->with(['categories', 'brand', 'productVariants'])->paginate(getPaginate());

        return view('admin.product.index', compact('pageTitle', 'products', 'trashed'));
    }

    public function trashed()
    {
        $pageTitle = "Trashed Products";
        $trashed   = true;
        $products  = Product::select('id', 'name', 'slug', 'brand_id', 'is_published', 'is_showable', 'is_downloadable', 'product_type_id', 'product_type', 'regular_price', 'sale_price', 'in_stock', 'track_inventory', 'show_stock', 'deleted_at')
            ->searchable(['name'])
            ->with(['brand', 'categories'])
            ->onlyTrashed()
            ->orderByDesc('id')
            ->paginate(getPaginate());

        return view('admin.product.index', compact('pageTitle', 'products', 'trashed'));
    }
    public function lowStock()
    {
        $pageTitle = "Low Stock Products";
        $trashed   = false;
        $products = Product::query();
        if ($branchId = BranchContext::id()) {
            $branchInventory = DB::table('product_batches')
                ->where('branch_id', $branchId)
                ->groupBy('product_id')
                ->select('product_id')
                ->selectRaw('SUM(CASE WHEN qty_received > qty_sold THEN qty_received - qty_sold ELSE 0 END) as quantity');
            $products->leftJoinSub($branchInventory, 'branch_inventory', 'branch_inventory.product_id', '=', 'products.id')
                ->where('products.track_inventory', Status::YES)
                ->whereRaw('COALESCE(branch_inventory.quantity, 0) <= products.alert_quantity');
        } else {
            $products->lowStock();
        }

        $products = $products->select(
            'products.id',
            'products.name',
            'products.slug',
            'products.brand_id',
            'products.is_published',
            'products.is_showable',
            'products.is_downloadable',
            'products.product_type_id',
            'products.product_type',
            'products.regular_price',
            'products.sale_price',
            'products.in_stock',
            'products.track_inventory',
            'products.show_stock',
            'products.alert_quantity'
        )
            ->searchable(['name'])
            ->with(['brand', 'categories'])
            ->orderByDesc('id')
            ->paginate(getPaginate());

        return view('admin.product.index', compact('pageTitle', 'products', 'trashed'));
    }

    public function outOfStock()
    {
        $pageTitle = "Out Of Stock Products";
        $trashed   = false;
        $products  = Product::select('id', 'name', 'slug', 'brand_id', 'is_published', 'is_showable', 'is_downloadable', 'product_type_id', 'product_type', 'regular_price', 'sale_price', 'in_stock', 'track_inventory', 'show_stock')
            ->outOfStock()
            ->searchable(['name'])
            ->with(['brand', 'categories'])
            ->orderByDesc('id')
            ->paginate(getPaginate());

        return view('admin.product.index', compact('pageTitle', 'products', 'trashed'));
    }

    public function topSelling()
    {
        $pageTitle = 'Top Selling Products';
        $trashed = false;
        $products = Product::topSales(paginate: true);
        return view('admin.product.index', compact('pageTitle', 'products', 'trashed'));
    }
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();
        $notify[] = ['success', 'Product permanently deleted'];
        return back()->withNotify($notify);
    }
    
    
}
