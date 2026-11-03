@extends('admin.layouts.app')

@section('panel')
    <style>
        .dashboard-card-link .card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .dashboard-card-link:hover .card,
        .dashboard-card-link:focus-visible .card {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
        }
    </style>

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
            <a href="{{ $dashboardCardLinks['total_sales'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Total sales</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($periodOrders->amount) }}</h4>
                    <small>Selected period</small>
                </div></div>
            </a>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <a href="{{ $dashboardCardLinks['orders'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Orders</small>
                    <h4 class="mt-2 mb-1">{{ number_format($periodOrders->count) }}</h4>
                    <small>Selected period</small>
                </div></div>
            </a>
        </div>
        @if ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['stock_added'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                    <div class="card h-100"><div class="card-body">
                        <small class="text-muted">Stock added</small>
                        <h4 class="mt-2 mb-1">{{ number_format($stockAdded) }}</h4>
                        <small>Units added in selected period</small>
                    </div></div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['units_sold'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                    <div class="card h-100"><div class="card-body">
                        <small class="text-muted">Units sold</small>
                        <h4 class="mt-2 mb-1">{{ number_format($unitsSold) }}</h4>
                        <small>Website orders in selected period</small>
                    </div></div>
                </a>
            </div>
        @endif
        @unless ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['total_due'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Total due</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($totalDue) }}</h4>
                    <small>Outstanding in selected period</small>
                </div></div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['stock'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Current stock value</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($stock->value) }}</h4>
                    <small>At sale price, or regular price when not on sale</small>
                </div></div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['stock'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Available stock</small>
                    <h4 class="mt-2 mb-1">{{ number_format($stock->quantity) }}</h4>
                    <small>Units across {{ number_format($stock->product_count) }} products</small>
                </div></div>
                </a>
            </div>
        @endunless
        <div class="col-xl-3 col-sm-6 mb-3">
            <a href="{{ $dashboardCardLinks['open_orders'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Open orders</small>
                    <h4 class="mt-2 mb-1">{{ number_format($openOrders->count) }}</h4>
                    <small>{{ showAmount($openOrders->amount) }} in selected period</small>
                </div></div>
            </a>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <a href="{{ $dashboardCardLinks['total_paid'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Total Paid</small>
                    <h4 class="mt-2 mb-1">{{ showAmount($totalPaid) }}</h4>
                    <small>Selected period</small>
                </div></div>
            </a>
        </div>
        @unless ($ecommerce)
            <div class="col-xl-3 col-sm-6 mb-3">
                <a href="{{ $dashboardCardLinks['low_stock'] }}" class="dashboard-card-link d-block h-100 text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <small class="text-muted">Low stock products</small>
                    <h4 class="mt-2 mb-1">{{ number_format($lowStockProducts) }}</h4>
                    <small>At or below alert quantity</small>
                </div></div>
                </a>
            </div>
        @endunless
    </div>

    @unless ($ecommerce)
        <div class="card mt-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Due customers</h6>
                <a href="{{ route('admin.users.due.bills') }}" class="btn btn-sm btn--primary">Show all due customers</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two mb-0">
                        <thead>
                            <tr><th>Customer</th><th>Phone</th><th class="text-end">Due amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($dueCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->name ?: 'Customer' }}</td>
                                    <td>{{ $customer->mobile ?: '—' }}</td>
                                    <td class="text-end">{{ showAmount($customer->due_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No due customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endunless

    <div class="card mt-2">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0">Recent orders</h6>
            @if ($ecommerce)
                <span class="text-muted">
                    Total: {{ number_format($statusCounts['all']) }}
                    · Showing {{ min($recentOrders->count(), $recentOrdersMatchingCount) }}
                    of {{ number_format($recentOrdersMatchingCount) }} matching
                </span>
            @else
                <span class="text-muted">{{ $recentOrders->count() }} shown</span>
            @endif
        </div>
        @if ($ecommerce)
            <div class="card-body pb-0">
                <div class="d-flex flex-wrap gap-2" aria-label="Filter recent orders by status">
                    @foreach (['all' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'dispatched' => 'Dispatched', 'delivered' => 'Delivered', 'canceled' => 'Cancelled', 'returned' => 'Returned'] as $statusKey => $statusName)
                        <a
                            href="{{ route('admin.dashboard', array_merge($filters, ['order_status' => $statusKey])) }}"
                            class="btn btn-sm {{ $selectedRecentOrderStatus === $statusKey ? 'btn--primary' : 'btn-outline--dark' }}"
                            @if ($selectedRecentOrderStatus === $statusKey) aria-current="true" @endif
                        >
                            {{ $statusName }}
                            <span class="badge bg-light text-dark ms-1">{{ number_format($statusCounts[$statusKey]) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
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
                            <tr><td colspan="4" class="text-center">
                                {{ $ecommerce ? 'No orders match this status in the selected period.' : 'No orders in this period.' }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection