@extends($activeTemplate . 'layouts.user')

@section('content')
    <div class="container py-5">
        <div class="order-details">

            {{-- ─── Header ───────────────────────────────────────── --}}
            <div class="order-details-top d-flex justify-content-between flex-wrap gap-2">
                <h5 class="order-details-id mb-1">
                    @lang('Order ID'): {{ $order->order_number }}
                    {!! $order->paymentBadge() !!}
                    {!! $order->statusBadge() !!}
                </h5>
                <span> Order Placed: {{ showDateTime($order->created_at, 'F d, Y  h:i A') }}</span>
            </div>

            {{-- ─── Items table ──────────────────────────────────── --}}
            <div class="order-details-products my-3">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <th>@lang('Price')</th>
                                <th>@lang('Qty')</th>
                                <th class="text-end">@lang('Total')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subtotal = 0;
                            @endphp
                            @foreach ($order->orderDetail as $row)
                                @php
                                    $subtotal += $row->price * $row->quantity;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $row->product->name }}
                                        @if ($row->productVariant)
                                            - {{ $row->productVariant->name }}
                                        @endif
                                    </td>
                                    <td>{{ showAmount($row->price) }}</td>
                                    <td>{{ $row->quantity }}</td>
                                    <td class="text-end">{{ showAmount($row->price * $row->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ───  Summary + Shipping ──────────────────────────── --}}
            <div class="row gy-4 flex-md-row-reverse">
                {{-- Summary --}}
                <div class="col-md-6">
                    <div class="details-info-list">
                        <h6 class="mb-3">@lang('Order Summary')</h6>
                        <ul>
                            <li><span>@lang('Subtotal')</span><span>{{ showAmount($subtotal) }}</span></li>

                            @if ($order->appliedCoupon)
                                <li>
                                    <span>(<i class="la la-minus"></i>) @lang('Coupon')
                                        ({{ $order->appliedCoupon->coupon->coupon_code }})</span>
                                    <span>{{ showAmount($order->appliedCoupon->amount) }}</span>
                                </li>
                            @endif

                            <li>
                                <span>(<i class="la la-plus"></i>) @lang('Shipping')</span>
                                <span>{{ showAmount($order->shipping_charge) }}</span>
                            </li>

                            <li class="total">
                                <span>@lang('Total')</span>
                                <span>{{ showAmount($order->total_amount) }}</span>
                            </li>
                        </ul>
                    </div>

                    {{-- payment details for paid orders --}}
                    @if ($order->deposit && $order->deposit->status != 0)
                        <div class="details-info-list">
                            <h6 class="mb-3">@lang('Payment Details')</h6>
                            <ul>
                                <li>
                                    <span>@lang('Payment Method')</span>
                                    <span>
                                        {{ $order->deposit->method_code == 0 ? __('Cash On Delivery') : __($order->deposit->gateway->name) }}
                                    </span>
                                </li>
                                <li><span>@lang('Total Bill')</span><span>{{ showAmount($order->total_amount) }}</span></li>
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Shipping & guest contact --}}
                <div class="col-md-6">
                    @php $addr = $order->shipping_address ? json_decode($order->shipping_address) : null; @endphp
                    <div class="details-info-address">
                        <h6 class="mb-3">@lang('Shipping Details')</h6>
                        <ul class="info-address-list">
                            <li><span class="title">@lang('Name')</span><span class="devide-colon">:</span>
                                {{ $order->guest_name ?? $addr?->name }}</li>
                            <li><span class="title">@lang('Phone')</span><span class="devide-colon">:</span>
                                {{ $addr?->phone }}</li>
                            <li><span class="title">@lang('Address')</span><span class="devide-colon">:</span>
                                {{ $addr?->address }}</li>
                            <li><span class="title">@lang('E-mail')</span><span class="devide-colon">:</span>
                                {{ $order->guest_email }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            {{-- copy-link --}}
            <div class="mt-3 text-end d-print-none">
                <button id="copyLinkBtn" class="btn btn--primary me-2">
                    <i class="la la-link"></i> @lang('Copy this page link')
                </button>

                <button onclick="window.print()" class="btn btn-outline--light">
                    <i class="la la-print"></i> @lang('Print')
                </button>
            </div>

            {{-- print --}}
            
        </div>
    </div>
@endsection

@push('style')
<style>
/* ───── Print-only rules ─────────────────────────── */
@media print {

    /* hide absolutely everything first … */
    body *           { visibility: hidden !important; }
    /* …then reveal the panel we care about */
    .order-details,
    .order-details * { visibility: visible !important; }

    /* ensure it starts at top-left of printed page */
    .order-details  { position: absolute; left: 0; top: 0; width: 100%; }

    /* optional: remove box-shadows / borders on print */
    .order-details table { border: 1px solid #999 !important; }
    .order-details table th,
    .order-details table td { border: 1px solid #999 !important; }
}
</style>
@endpush

@push('script')
<script>
(function () {
    const btn   = document.getElementById('copyLinkBtn');
    const toast = (msg) =>
        $('<div class="alert alert-success position-fixed top-0 end-0 m-3" role="alert">'+msg+'</div>')
            .appendTo('body')
            .delay(1500).fadeOut(400, function(){ $(this).remove(); });

    btn?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(window.location.href);
            toast("{{ __('Link copied!') }}");
        } catch (_) {
            // fallback for old browsers
            const helper = document.createElement('input');
            helper.value = window.location.href;
            document.body.appendChild(helper);
            helper.select();
            document.execCommand('copy');
            document.body.removeChild(helper);
            toast("{{ __('Link copied!') }}");
        }
    });
})();
</script>
@endpush