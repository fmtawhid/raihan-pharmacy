<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $pageTitle = 'Branches';
        $branches = Branch::withCount('admins')->orderBy('name')->paginate(20);

        return view('admin.branches.index', compact('pageTitle', 'branches'));
    }

    public function show(Branch $branch, Request $request)
    {
        $pageTitle = $branch->name . ' Details';
        $filters = $this->validatedFilters($request);
        $ordersQuery = $this->ordersQuery($branch, $filters);
        $inventoryQuery = $this->inventoryQuery($branch, $filters['product']);
        $summary = $this->summary($branch, $ordersQuery);
        $inventory = (clone $inventoryQuery)->paginate(10, ['*'], 'inventory_page')->appends($request->query());
        $orders = (clone $ordersQuery)
            ->select(['id', 'order_number', 'created_at', 'status', 'payment_status', 'is_cod', 'total_amount'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'orders_page')
            ->appends($request->query());
        $statusLabels = $this->statusLabels();
        $paymentLabels = [Status::PAYMENT_SUCCESS => 'Paid'];
        $managersCount = $branch->admins()->count();

        return view('admin.branches.show', compact('pageTitle', 'branch', 'filters', 'summary', 'inventory', 'orders', 'statusLabels', 'paymentLabels', 'managersCount'));
    }

    public function report(Branch $branch, Request $request)
    {
        $filters = $this->validatedFilters($request);
        $ordersQuery = $this->ordersQuery($branch, $filters);
        $inventoryQuery = $this->inventoryQuery($branch, $filters['product']);
        $summary = $this->summary($branch, $ordersQuery);
        $statusLabels = $this->statusLabels();
        $filename = 'branch-' . strtolower($branch->code) . '-report-' . $filters['from'] . '-to-' . $filters['to'] . '.csv';

        return response()->streamDownload(function () use ($branch, $filters, $summary, $statusLabels, $ordersQuery, $inventoryQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Branch Report', $branch->name, $branch->code]);
            fputcsv($handle, ['Date range', $filters['from'], $filters['to']]);
            fputcsv($handle, ['Generated at', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Products in stock', $summary['stock']->product_count]);
            fputcsv($handle, ['Units in stock', $summary['stock']->quantity]);
            fputcsv($handle, ['Inventory value at purchase cost', $summary['stock']->value]);
            fputcsv($handle, ['Filtered orders', $summary['periodOrders']->count]);
            fputcsv($handle, ['Filtered order value', $summary['periodOrders']->amount]);
            fputcsv($handle, ['Delivered order value', $summary['deliveredAmount']]);
            fputcsv($handle, ['Open order count', $summary['openOrders']->count]);
            fputcsv($handle, ['Open order value', $summary['openOrders']->amount]);
            fputcsv($handle, []);
            fputcsv($handle, ['Inventory']);
            fputcsv($handle, ['SKU', 'Product', 'Available units', 'Inventory value at purchase cost']);

            (clone $inventoryQuery)->orderBy('p.id')->chunk(500, function ($products) use ($handle) {
                foreach ($products as $product) {
                    fputcsv($handle, [$product->sku, $product->product_name, $product->quantity, $product->value]);
                }
            });

            fputcsv($handle, []);
            fputcsv($handle, ['Orders']);
            fputcsv($handle, ['Order number', 'Date', 'Status', 'Payment', 'Total']);
            (clone $ordersQuery)->orderByDesc('created_at')->orderByDesc('id')->chunk(500, function ($orders) use ($handle, $statusLabels) {
                foreach ($orders as $order) {
                    $payment = $order->is_cod
                        ? 'Cash on delivery'
                        : ((int) $order->payment_status === Status::PAYMENT_SUCCESS ? 'Paid' : 'Unpaid');
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at,
                        $statusLabels[$order->status] ?? 'Unknown',
                        $payment,
                        $order->total_amount,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $pageTitle = 'Create Branch';
        $branch = new Branch(['is_active' => true]);

        return view('admin.branches.form', compact('pageTitle', 'branch'));
    }

    public function store(Request $request)
    {
        Branch::create($this->validatedData($request));

        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        $pageTitle = 'Edit Branch';

        return view('admin.branches.form', compact('pageTitle', 'branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($this->validatedData($request, $branch));

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->code === 'MAIN') {
            return back()->withErrors(['branch' => 'The main branch cannot be deleted.']);
        }

        foreach (['admins', 'users', 'customers', 'purchasers', 'product_batches', 'orders', 'deposits', 'stock_logs'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'branch_id') && DB::table($table)->where('branch_id', $branch->id)->exists()) {
                return back()->withErrors(['branch' => 'This branch has assigned data. Deactivate it instead of deleting it.']);
            }
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date', 'required_with:to'],
            'to' => ['nullable', 'date', 'required_with:from', 'after_or_equal:from'],
            'status' => ['nullable', 'in:all,0,1,2,3,4,9'],
            'search' => ['nullable', 'string', 'max:100'],
            'product' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'from' => Carbon::parse($validated['from'] ?? now()->startOfWeek()->toDateString())->toDateString(),
            'to' => Carbon::parse($validated['to'] ?? now()->toDateString())->toDateString(),
            'status' => $validated['status'] ?? null,
            'search' => trim($validated['search'] ?? ''),
            'product' => trim($validated['product'] ?? ''),
        ];
    }

    private function ordersQuery(Branch $branch, array $filters)
    {
        $query = DB::table('orders')
            ->where('branch_id', $branch->id)
            ->whereBetween('created_at', [Carbon::parse($filters['from'])->startOfDay(), Carbon::parse($filters['to'])->endOfDay()]);

        if ($filters['status'] === 'all') {
            // Include every order status.
        } elseif ($filters['status'] !== null) {
            $query->where('status', (int) $filters['status']);
        } else {
            $query->whereNotIn('status', [Status::ORDER_CANCELED, Status::ORDER_RETURNED]);
        }

        if ($filters['search'] !== '') {
            $query->where('order_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }

    private function inventoryQuery(Branch $branch, string $search)
    {
        $query = DB::table('product_batches as pb')
            ->join('products as p', 'p.id', '=', 'pb.product_id')
            ->where('pb.branch_id', $branch->id)
            ->whereNull('p.deleted_at')
            ->whereRaw('pb.qty_received > pb.qty_sold')
            ->selectRaw('p.id as product_id, p.name as product_name, p.sku, SUM(pb.qty_received - pb.qty_sold) as quantity')
            ->selectRaw('COALESCE(SUM((pb.qty_received - pb.qty_sold) * COALESCE(pb.purchase_price, 0)), 0) as value')
            ->groupBy('p.id', 'p.name', 'p.sku')
            ->orderBy('p.name');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('p.name', 'like', '%' . $search . '%')
                    ->orWhere('p.sku', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function summary(Branch $branch, $ordersQuery): array
    {
        $stock = DB::table('product_batches')
            ->where('branch_id', $branch->id)
            ->selectRaw('COALESCE(SUM(CASE WHEN qty_received > qty_sold THEN qty_received - qty_sold ELSE 0 END), 0) as quantity')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty_received > qty_sold THEN (qty_received - qty_sold) * COALESCE(purchase_price, 0) ELSE 0 END), 0) as value')
            ->selectRaw('COUNT(DISTINCT CASE WHEN qty_received > qty_sold THEN product_id END) as product_count')
            ->first();
        $periodOrders = (clone $ordersQuery)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();
        $deliveredAmount = (clone $ordersQuery)->where('status', Status::ORDER_DELIVERED)->sum('total_amount') ?: 0;
        $openOrders = DB::table('orders')
            ->where('branch_id', $branch->id)
            ->whereIn('status', [Status::ORDER_PENDING, Status::ORDER_PROCESSING, Status::ORDER_DISPATCHED])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->first();

        return compact('stock', 'periodOrders', 'deliveredAmount', 'openOrders');
    }

    private function statusLabels(): array
    {
        return [
            Status::ORDER_PENDING => 'Pending',
            Status::ORDER_PROCESSING => 'Processing',
            Status::ORDER_DISPATCHED => 'Dispatched',
            Status::ORDER_DELIVERED => 'Delivered',
            Status::ORDER_CANCELED => 'Canceled',
            Status::ORDER_RETURNED => 'Returned',
        ];
    }

    private function validatedData(Request $request, ?Branch $branch = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->ignore($branch?->id)],
            'code' => ['required', 'string', 'max:32', 'alpha_dash', Rule::unique('branches', 'code')->ignore($branch?->id)],
            'is_active' => 'required|boolean',
            'allow_backorder_sales' => 'required|boolean',
        ]);

        $data['code'] = strtoupper($data['code']);
        if ($branch?->code === 'MAIN') {
            $data['code'] = 'MAIN';
            $data['is_active'] = true;
        }
        return $data;
    }
}