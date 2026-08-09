@extends('admin.layouts.app')
@php
    /* Grab the location arrays once */
    $loc = getBangladeshLocationData();

    $divisionName = collect($loc['divisions'])->firstWhere('id', $order->division_id)['name'] ?? '-';

    $districtName = collect($loc['districts'])->firstWhere('id', $order->district_id)['name'] ?? '-';
@endphp
@section('panel')
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-end">
            <h6>@lang('Order Items')</h6>
            <div>
                <h5 class="order-details-id mb-1 d-flex align-items-center flex-wrap">
                    <span class="order-details-id">@lang('Order ID'):</span> {{ $order->order_number }}
                    <span>
                        @php echo $order->paymentBadge() @endphp
                        @php echo $order->statusBadge() @endphp
                    </span>
                </h5>

                <span> {{ showDateTime($order->created_at, 'F d, Y') }} @lang('at')
                    {{ showDateTime($order->created_at, 'h:i A') }} </span>
            </div>
        </div>
        <div class="card-body">

            <div class="order-details-products mb-3">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <th>@lang('Price')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Total Price')</th>
                                <th>@lang('Discount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subtotal = $order->orderDetail->sum(function ($detail) {
                                    return $detail->price * $detail->quantity;
                                });
                                $totalDiscount = $order->orderDetail->sum('discount');
                            @endphp

                            @foreach ($order->orderDetail as $data)
                                @php
                                    $mainImage =
                                        $data->productVariant && @$data->productVariant->main_image_id
                                            ? $data->productVariant->mainImage(false)
                                            : @$data->product->mainImage(false);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="single-product-item">
                                            <div class="thumb">
                                                <img class="lazyload" src="{{ $mainImage }}" alt="product-image">
                                            </div>

                                            <div class="content">
                                                <div class="content-top">
                                                    <div class="content-top-left">
                                                        <span class="title d-block fw-normal">
                                                            {{ strLimit(@$data->product->name, 60) }}

                                                            @if ($data->productVariant)
                                                                - {{ @$data->productVariant->name }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>


                                    <td>
                                        {{ showAmount($data->price) }}
                                    </td>
                                    <td>{{ $data->quantity }}</td>
                                    <td class="text-end">{{ showAmount($data->price * $data->quantity) }}</td>
                                    <td class="text-end">
                                        @if ($data->discount > 0)
                                            <span style="color:#f59e0b;font-weight:600;">-{{ showAmount($data->discount) }}</span>
                                        @else
                                            <span style="color:#cbd5e1;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-3 flex-md-row-reverse">
                <div class="col-md-6">
                    <div class="details-info-list">
                        <h6 class="mb-3">@lang('Order Summary')</h6>
                        <ul>
                            <li>
                                <span>@lang('Subtotal')</span>
                                <span class="fw-semibold">{{ showAmount($subtotal, 2) }}</span>
                            </li>
                            @if ($totalDiscount > 0)
                                <li>
                                    <span>(<i class="la la-minus"></i>) @lang('Discount')</span>
                                    <span style="color:#f59e0b;font-weight:600;">{{ showAmount($totalDiscount, 2) }}</span>
                                </li>
                            @endif
                            @if ($order->appliedCoupon)
                                <li>
                                    <span>(<i class="la la-minus"></i>) @lang('Coupon')
                                        ({{ $order->appliedCoupon->coupon->coupon_code }})</span>
                                    <span> {{ showAmount($order->appliedCoupon->amount, 2) }}</span>
                                </li>
                            @endif

                            <li>
                                <span>(<i class="la la-plus"></i>) @lang('Shipping')</span>
                                <span>{{ @gs('cur_sym') . getAmount($order->shipping_charge, 2) }}</span>
                            </li>

                            <li class="total">
                                <span>@lang('Total')</span>
                                <span>{{ showAmount($order->total_amount) }}</span>
                            </li>
                        </ul>
                    </div>

                    @if (isset($order->deposit) && $order->deposit->status != 0)
                        <div class="details-info-list">
                            <h6 class="mb-3">@lang('Payment Details')</h6>
                            <ul>
                                <li>
                                    <span>@lang('Payment Method')</span>
                                    <span>
                                        @if ($order->deposit->method_code == 0)
                                            @lang('Cash On Delivery')
                                        @else
                                            {{ __($order->deposit->gateway->name) }}
                                        @endif
                                    </span>
                                </li>

                                <li>
                                    <span>@lang('Total Bill')</span>
                                    <span>{{ showAmount($order->total_amount) }}</span>
                                </li>

                                @if (@$order->deposit->charge > 0)
                                    <li>
                                        <span>@lang('Gateway Charge')</span>
                                        <span>{{ gs('cur_sym') . getAmount(@$order->deposit->charge) }}</span>
                                    </li>
                                @endif

                                <li class="total">
                                    <span>@lang('Total Payable Amount') </span>
                                    <span>{{ gs('cur_sym') . getAmount($order->deposit->amount + @$order->deposit->charge) }}</span>
                                </li>

                            </ul>
                        </div>
                    @endif
                </div>
                @php
                    $shippingAddress = $order->shipping_address ? json_decode($order->shipping_address) : null;
                @endphp
                <div class="col-md-6">
                    @if ($shippingAddress)
                        <div class="details-info-address">

                            <h6 class="mb-3">@lang('Shipping Details')</h6>
                            <ul class="info-address-list">
                                <li>
                                    <span class="title">@lang('Name')</span>
                                    <span>
                                        <span class="devide-colon">:</span>

                                        @if ($order->user)
                                            {{-- Registered customer --}}
                                            {{ $order->user->firstname }} {{ $order->user->lastname }}
                                        @elseif(!empty($shippingAddress->name))
                                            {{-- Guest – name came with the shipping form --}}
                                            {{ $shippingAddress->name }}
                                        @elseif($order->guest_name)
                                            {{-- Older records that still use guest_name column --}}
                                            {{ $order->guest_name }}
                                        @else
                                            @lang('Guest User')
                                        @endif
                                    </span>
                                </li>
                                <li>
                                    <span class="title">@lang('Address')</span>
                                    <span>
                                        <span class="devide-colon">:</span>
                                        {{ $shippingAddress->address }}
                                    </span>
                                </li>
                                <li>
                                    <span class="title">@lang(key: 'Phone')</span>
                                    <span>
                                        <span class="devide-colon">:</span>
                                        {{ $shippingAddress->phone }}
                                    </span>
                                </li>

                                <li>
                                    <span class="title">@lang('Area')</span>
                                    <span><span class="devide-colon">:</span> {{ $order->area_name ?? '-' }}</span>
                                </li>

                                <li>
                                    <span class="title">@lang('District')</span>
                                    <span><span class="devide-colon">:</span> {{ $districtName }}</span>
                                </li>

                                <li>
                                    <span class="title">@lang('Division')</span>
                                    <span><span class="devide-colon">:</span> {{ $divisionName }}</span>
                                </li> 

                                <li>
                                    <span class="title">@lang('Postcode')</span>
                                    <span><span class="devide-colon">:</span> {{ $order->postcode ?? '-' }}</span>
                                </li>



                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- RETURNED PRODUCTS SECTION -->
            @php
                $returnedProducts = \App\Models\ProductReturn::where('order_id', $order->id)->with('product')->get();
            @endphp

            @if($returnedProducts->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="la la-undo" style="color: #dc2626;"></i>
                                Returned Products ({{ $returnedProducts->count() }})
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive--sm table-responsive">
                                <table class="table table--light style--two mb-0">
                                    <thead>
                                        <tr>
                                            <th>@lang('Product')</th>
                                            <th style="text-align:center;">@lang('Quantity Returned')</th>
                                            <th style="text-align:right;">@lang('Unit Price')</th>
                                            <th style="text-align:right;">@lang('Refund Amount')</th>
                                            <th style="text-align:center;">@lang('Return Date')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalReturnedAmount = 0; @endphp
                                        @foreach($returnedProducts as $returnedProduct)
                                            @php $totalReturnedAmount += $returnedProduct->refund_amount; @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ optional($returnedProduct->product)->name ?? 'N/A' }}</strong>
                                                </td>
                                                <td style="text-align:center;">
                                                    <span class="badge badge--warning">{{ $returnedProduct->quantity_returned }}</span>
                                                </td>
                                                <td style="text-align:right;">
                                                    {{ showAmount($returnedProduct->refund_amount / $returnedProduct->quantity_returned) }}
                                                </td>
                                                <td style="text-align:right; color: #dc2626; font-weight: 600;">
                                                    {{ showAmount($returnedProduct->refund_amount) }}
                                                </td>
                                                <td style="text-align:center; font-size: 0.875rem;">
                                                    {{ $returnedProduct->created_at->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f8f9fa; font-weight: 600;">
                                            <td colspan="3" style="text-align:right;">@lang('Total Refunded Amount'):</td>
                                            <td style="text-align:right; color: #dc2626;">
                                                {{ showAmount($totalReturnedAmount) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="text-end mt-3">
                <a href="{{ route('admin.print.invoice', $order->id) }}" target=blank class="btn btn-dark m-1"> <i
                        class="fa fa-print"></i>@lang('Print')</a>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .single-product-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .single-product-item .thumb {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 5px;
            overflow: hidden;
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }

        .order-details-top {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
        }

        .order-details-product {
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }


        .details-info-list {
            padding-block: 16px;
            border: 1px solid #ebebeb;
            border-radius: 6px;
        }

        .details-info-list:not(:last-child) {
            margin-bottom: 16px;
        }

        .details-info-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-inline: 16px;
        }

        .details-info-list h6 {
            padding-inline: 16px;
        }

        .details-info-list li span:first-child {
            font-weight: 500;
        }

        .details-info-list li span:last-child {
            font-weight: 500;
        }

        .details-info-list li:not(:last-child) {
            margin-bottom: 12px;
        }

        .details-info-list li.total {
            border-top: 1px solid #ebebeb;
            padding-top: 12px;
            font-size: 1rem;
        }

        .details-info-address {
            border: 1px solid #ebebeb;
            border-radius: 6px;
            padding: 16px;
        }

        .info-address-list li {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 24px;
        }

        .info-address-list li:not(:last-child) {
            margin-bottom: 12px;
        }

        .info-address-list li .title {
            min-width: 81px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .info-address-list .devide-colon {
            margin-right: 24px;
        }

        .info-address-list li .title~span {
            display: flex;
            align-items: flex-start;
        }

        .order-details-id {
            gap: 0 6px;
        }

        @media (max-width: 767px) {
            .info-address-list li {
                gap: 12px;
            }

            .info-address-list .devide-colon {
                margin-right: 12px;
            }

            .order-details-products .single-product-item {
                flex-direction: column;
                align-items: flex-end;
            }

            .order-details-products .content-top {
                margin-bottom: 0;
            }

            .order-details-top {
                flex-direction: column-reverse;
                align-items: flex-start;
            }
        }

        @media (max-width: 1399px) {

            .details-info-list,
            .details-info-address {
                max-width: 100%;
            }
        }
    </style>
@endpush
