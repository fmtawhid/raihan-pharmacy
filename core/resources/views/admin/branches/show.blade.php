@extends('admin.layouts.app')

@section('panel')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">{{ $branch->name }} <span class="text-muted">({{ $branch->code }})</span></h4>
        <span class="badge badge--{{ $branch->is_active ? 'success' : 'warning' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
        <span class="text-muted ml-2">{{ number_format($managersCount) }} branch managers</span>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
        <a href="{{ route('admin.branches.report', array_merge(['branch' => $branch], request()->except(['inventory_page', 'orders_page']))) }}" class="btn btn-sm btn--primary">
            <i class="las la-download"></i> Download CSV
        </a>
        <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline--dark">Back to Branches</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="mb-0">Branch activity</h6>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.branches.show', ['branch' => $branch, 'from' => now()->toDateString(), 'to' => now()->toDateString()]) }}">Today</a>
            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.branches.show', ['branch' => $branch, 'from' => now()->startOfWeek()->toDateString(), 'to' => now()->toDateString()]) }}">This week</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.branches.show', $branch) }}">
            <div class="row align-items-end">
                <div class="col-xl-2 col-md-4 form-group">
                    <label for="from">From</label>
                    <input id="from" type="date" name="from" class="form-control" value="{{ $filters['from'] }}" required>
                </div>
                <div class="col-xl-2 col-md-4 form-group">
                    <label for="to">To</label>
                    <input id="to" type="date" name="to" class="form-control" value="{{ $filters['to'] }}" required>
                </div>
                <div class="col-xl-2 col-md-4 form-group">
                    <label for="status">Order status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All active orders</option>
                        <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
                        @foreach($statusLabels as $status => $label)
                            <option value="{{ $status }}" @selected((string) $filters['status'] === (string) $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 form-group">
                    <label for="search">Order number</label>
                    <input id="search" type="search" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search order">
                </div>
                <div class="col-xl-2 col-md-4 form-group">
                    <label for="product">Product or SKU</label>
                    <input id="product" type="search" name="product" class="form-control" value="{{ $filters['product'] }}" placeholder="Search stock">
                </div>
                <div class="col-xl-2 col-md-4 form-group d-flex gap-2">
                    <button type="submit" class="btn btn--primary">Apply</button>
                    <a href="{{ route('admin.branches.show', $branch) }}" class="btn btn-outline--dark">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted">Stock value at cost</small>
            <h4 class="mt-2 mb-1">{{ showAmount($summary['stock']->value) }}</h4>
            <small>{{ number_format($summary['stock']->quantity) }} units · {{ number_format($summary['stock']->product_count) }} products</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted">Order value in selected period</small>
            <h4 class="mt-2 mb-1">{{ showAmount($summary['periodOrders']->amount) }}</h4>
            <small>{{ number_format($summary['periodOrders']->count) }} matching orders</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted">Delivered in selected period</small>
            <h4 class="mt-2 mb-1">{{ showAmount($summary['deliveredAmount']) }}</h4>
            <small>Delivered order value</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <small class="text-muted">Open order backlog</small>
            <h4 class="mt-2 mb-1">{{ showAmount($summary['openOrders']->amount) }}</h4>
            <small>{{ number_format($summary['openOrders']->count) }} pending, processing, or dispatched</small>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Available inventory</h6>
        <span class="text-muted">{{ $inventory->total() }} products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table--light style--two mb-0">
                <thead><tr><th>SKU</th><th>Product</th><th>Available units</th><th class="text-end">Value at cost</th></tr></thead>
                <tbody>
                    @forelse($inventory as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ number_format($product->quantity) }}</td>
                            <td class="text-end">{{ showAmount($product->value) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No matching stock found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($inventory->hasPages())<div class="card-footer">{{ $inventory->links() }}</div>@endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Orders</h6>
        <span class="text-muted">{{ $orders->total() }} matching orders</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table--light style--two mb-0">
                <thead><tr><th>Order number</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ showDateTime($order->created_at, 'd M, Y H:i') }}</td>
                            <td>{{ $statusLabels[$order->status] ?? 'Unknown' }}</td>
                            <td>{{ $order->is_cod ? 'Cash on delivery' : ($paymentLabels[$order->payment_status] ?? 'Unpaid') }}</td>
                            <td class="text-end">{{ showAmount($order->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No orders match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())<div class="card-footer">{{ $orders->links() }}</div>@endif
</div>

<p class="text-muted mt-3 mb-0">Order value follows the selected order date and filters. Canceled and returned orders are excluded by default. Inventory value uses remaining units at purchase cost; open order backlog is current and independent of the selected date range.</p>
@endsection