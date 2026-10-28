@extends('admin.layouts.app')

@section('panel')
    

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-end gap-2">
            <span class="text-muted me-1 pb-2">Sales period</span>
            <a class="btn btn-sm {{ $filters['date_filter'] === 'today' ? 'btn--primary' : 'btn-outline--dark' }}" href="{{ route('admin.dashboard', ['date_filter' => 'today']) }}">Today</a>
            <a class="btn btn-sm {{ $filters['date_filter'] === 'last_7_days' ? 'btn--primary' : 'btn-outline--dark' }}" href="{{ route('admin.dashboard', ['date_filter' => 'last_7_days']) }}">Last 7 days</a>
            <a class="btn btn-sm {{ $filters['date_filter'] === 'last_30_days' ? 'btn--primary' : 'btn-outline--dark' }}" href="{{ route('admin.dashboard', ['date_filter' => 'last_30_days']) }}">Last 30 days</a>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex flex-wrap align-items-end gap-2">
                <input type="hidden" name="date_filter" value="custom">
                <div>
                    <label for="start_date" class="form-label mb-1">From</label>
                    <input id="start_date" type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}" required>
                </div>
                <div>
                    <label for="end_date" class="form-label mb-1">To</label>
                    <input id="end_date" type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}" required>
                </div>
                <button type="submit" class="btn btn--primary">Apply dates</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Total sales</small>
                <h4 class="mt-2 mb-1">{{ showAmount($periodOrders->amount) }}</h4>
                <small>Selected period</small>
            </div></div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Orders</small>
                <h4 class="mt-2 mb-1">{{ number_format($periodOrders->count) }}</h4>
                <small>Selected period</small>
            </div></div>
        </div>
        @if ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Stock added</small>
                    <h4 class="mt-2 mb-1">{{ number_format($stockAdded) }}</h4>
                    <small>Units added in selected period</small>
                </div></div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Units sold</small>
                    <h4 class="mt-2 mb-1">{{ number_format($unitsSold) }}</h4>
                    <small>Website orders in selected period</small>
                </div></div>
            </div>
        @endif
        @unless ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Total due</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($totalDue) }}</h4>
                    <small>Outstanding in selected period</small>
                </div></div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Current stock value</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($stock->value) }}</h4>
                    <small>At purchase cost</small>
                </div></div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Available stock</small>
                    <h4 class="mt-2 mb-1">{{ number_format($stock->quantity) }}</h4>
                    <small>Units across {{ number_format($stock->product_count) }} products</small>
                </div></div>
            </div>
        @endunless
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Open orders</small>
                <h4 class="mt-2 mb-1">{{ number_format($openOrders->count) }}</h4>
                <small>{{ showAmount($openOrders->amount) }} in selected period</small>
            </div></div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Total Paid</small>
                <h4 class="mt-2 mb-1">{{ showAmount($totalPaid) }}</h4>
                <small>Selected period</small>
            </div></div>
        </div>
        @unless ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Low stock products</small>
                    <h4 class="mt-2 mb-1">{{ number_format($lowStockProducts) }}</h4>
                    <small>At or below alert quantity</small>
                </div></div>
            </div>
        @endunless
    </div>

    <div class="card mt-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Recent orders</h6>
            <span class="text-muted">{{ $recentOrders->count() }} shown</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table--light style--two mb-0">
                    <thead><tr><th>Order number</th><th>Date</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ showDateTime($order->created_at, 'd M, Y H:i') }}</td>
                                <td>{{ $statusLabels[$order->status] ?? 'Unknown' }}</td>
                                <td class="text-end">{{ showAmount($order->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection