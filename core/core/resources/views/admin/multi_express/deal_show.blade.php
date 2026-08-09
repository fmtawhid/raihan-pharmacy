@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">

<div class="row g-4 mb-4">

    <!-- Left Column: Deal Info -->
    <div class="col-md-6">
        <div class="card b-radius--10">
            <div class="card-body">
                <h5 class="mb-3">{{ $deal->title }}</h5>
                <p><strong>@lang('Category:')</strong> {{ $deal->category->name ?? '-' }}</p>
                <p><strong>@lang('Deal Price:')</strong> ৳{{ showAmount($deal->deal_price) }}</p>
                <p><strong>@lang('Stock Status:')</strong> {{ ucfirst($deal->stock_status) }}</p>
                <p><strong>@lang('Min Required (Target Booking):')</strong> {{ $deal->min_required }}</p>
                <p><strong>@lang('Max Capacity:')</strong> {{ $deal->max_capacity }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Stats -->
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <div class="card b-radius--10 mb-4">
                    <div class="card-body text-center">
                        <h6>@lang('Total Orders')</h6>
                        <p class="fs-4 fw-bold">{{ $deal->orders()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card b-radius--10 mb-4">
                    <div class="card-body text-center">
                        <h6>@lang('Product Bookings')</h6>
                        <p class="fs-4 fw-bold">{{ $deal->orders()->sum('quantity') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card b-radius--10 mb-4">
                    <div class="card-body text-center">
                        <h6>@lang('Total Payment')</h6>
                        <p class="fs-4 fw-bold">
                            {{ showAmount($deal->orders()->with('payments')->get()->sum(fn($o)=> $o->payments()->where('status','paid')->sum('amount'))) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card b-radius--10 mb-4">
                    <div class="card-body text-center">
                        <h6>@lang('Remaining Payment')</h6>
                        <p class="fs-4 fw-bold">
                            {{ showAmount($deal->orders()->with('payments')->get()->sum(fn($o)=> $o->total_price - $o->payments()->where('status','paid')->sum('amount'))) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>



<div class="card b-radius--10">
    <div class="card-header">
        <h5>@lang('Orders for this Deal')</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table--light style--two">
            <thead>
                <tr>
                    <th>@lang('User')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Price')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Delivery Option')</th>
                    <th>@lang('Total')</th>
                    <th>@lang('Payment Status')</th>
                    <th>@lang('Delivery Status')</th>
                    <th>@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deal->orders as $order)
<tr>
    <td>
        @if($order->user)
            <a href="{{ route('admin.users.detail', $order->user->id) }}">
                {{ $order->user->email }}
            </a>
        @else
            Guest
        @endif
    </td>

    <td>{{ $order->name }}</td>
    <td>{{ showAmount($order->price_per_item) }}</td>
    <td>{{ $order->quantity }}</td>
    <td>{{ $order->deliveryOption->label ?? '-' }} - {{ showAmount($order->deliveryOption->charge_per_item ?? 0) }}</td>
    <td>{{ showAmount($order->total_price) }}</td>

    <!-- Payment Status -->
    <td>
        <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
            {{ ucfirst($order->payment_status) }}
        </span>
    </td>

    <!-- Delivery Status -->
    <td>
        <form action="{{ route('admin.admin.multi_express.order.status', ['deal_id' => $deal->id, 'order_id' => $order->id]) }}" method="POST">
            @csrf
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" {{ $order->delivery_status == 'delivered' ? 'disabled' : '' }}>
                <option value="pending" {{ $order->delivery_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->delivery_status == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="delivered" {{ $order->delivery_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->delivery_status == 'cancelled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </form>
    </td>

    <td>
        <a href="{{ route('admin.admin.multi_express.order.show', ['deal_id' => $deal->id, 'order_id' => $order->id]) }}" class="btn btn-sm btn-outline--primary">
            <i class="la la-desktop"></i> @lang('Details')
        </a>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted">@lang('No orders found')</td>
</tr>
@endforelse

            </tbody>
        </table>
    </div>
</div>


    </div>
</div>
@endsection
