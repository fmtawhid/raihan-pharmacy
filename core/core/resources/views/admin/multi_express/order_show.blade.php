@extends('admin.layouts.app')

@section('panel')
<div class="container">
    <div class="order-details">

        <!-- Order Top Info -->
        <div class="order-details-top mb-3">
            <h6>@lang('Order Items')</h6>
            <div>
                <h5 class="order-details-id mb-1 d-flex align-items-center flex-wrap gap-3">
                    <span>@lang('Order ID'):</span> {{ $order->id }}
                    <span>
                        <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                        <span class="badge {{ $order->delivery_status == 'delivered' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($order->delivery_status) }}
                        </span>
                    </span>
                </h5>
                <span> {{ showDateTime($order->created_at, 'F d, Y') }} @lang('at') {{ showDateTime($order->created_at, 'h:i A') }} </span>
            </div>
        </div>

        <!-- Products Table -->
        <div class="order-details-products mb-3">
            <div class="table-responsive">
                <table class="table table-bordered table--responsive--md">
                    <thead>
                        <tr>
                            <th>@lang('Product')</th>
                            <th>@lang('Price')</th>
                            <th>@lang('Quantity')</th>
                            <th>@lang('Total Price')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $subtotal = ($order->price_per_item ?? 0) * ($order->quantity ?? 0); 
                            $totalPaid = $order->totalPaid() ?? 0;
                            $remaining = max(0, ($order->total_price ?? 0) - $totalPaid);
                        @endphp
                        <tr>
                            <td>
                                <div class="single-product-item align-items-center d-flex gap-2">
                                    <div class="thumb">
                                        <img class="lazyload" src="{{ getImage($deal->feature_image) }}" alt="product-image" width="60">
                                    </div>
                                    <div class="content d-flex flex-column">{{ $deal->title ?? '-' }}</div>
                                </div>
                            </td>
                            <td> {{ showAmount($order->price_per_item ?? 0) }}</td>
                            <td> {{ $order->quantity ?? 0 }}</td>
                            <td class="text-end"> {{ showAmount($subtotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="row g-3 flex-md-row-reverse">
            <div class="col-md-6">
                <div class="details-info-list mb-3">
                    <h6 class="mb-3">@lang('Order Summary')</h6>
                    <ul>
                        <li><span>@lang('Subtotal')</span><span class="fw-semibold"> {{ showAmount($subtotal) }}</span></li>
                        <li><span>@lang('Delivery Charge')</span><span> {{ showAmount(($order->total_price ?? 0) - $subtotal) }}</span></li>
                        <li class="total"><span>@lang('Total')</span><span> {{ showAmount($order->total_price ?? 0) }}</span></li>
                        <li><span>@lang('Total Paid')</span><span> {{ showAmount($totalPaid) }}</span></li>
                        <li><span>@lang('Remaining Amount')</span><span> {{ showAmount($remaining) }}</span></li>
                        <li><span>@lang('Payment Type')</span><span>{{ ucfirst($order->payment_type) }}</span></li>
                        <li>
                            <span>@lang('Payment Status')</span>
                            <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                            @if($remaining > 0)
                                <a href="{{ route('admin.admin.multi_express.order.payment.page', $order->id) }}" 
                                   class="btn btn-sm btn-outline-success ms-2">
                                   @lang('Add Payment')
                                </a>
                            @endif
                        </li>
                        <li><span>@lang('Delivery Type')</span><span>{{ ucfirst($order->delivery_type) }}</span></li>
                        <li>
                            <span>@lang('Delivery Status')</span>
                            <form action="{{ route('admin.admin.multi_express.order.status', ['deal_id'=>$deal->id,'order_id'=>$order->id]) }}" method="POST" class="d-inline">
                                @csrf
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="pending" {{ $order->delivery_status=='pending'?'selected':'' }}>Pending</option>
                                    <option value="processing" {{ $order->delivery_status=='processing'?'selected':'' }}>Processing</option>
                                    <option value="delivered" {{ $order->delivery_status=='delivered'?'selected':'' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->delivery_status=='cancelled'?'selected':'' }}>Cancelled</option>
                                </select>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-3 text-end">
            <a href="{{ route('multi_express.deal.show', $deal->id) }}" class="btn btn-lg btn-outline-dark">
                <i class="la la-arrow-left"></i> @lang('Back to Deal')
            </a>
        </div>
    </div>
</div>
@endsection
