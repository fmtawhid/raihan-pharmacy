{{-- resources/views/admin/order/partials/order_table.blade.php --}}
@php
    $admin = auth()->guard('admin')->user();
@endphp
<tbody class="list">
    @forelse ($orders as $order)
        <tr>
            <td>{{ showDateTime($order->created_at, 'd M, Y') }}</td>

            {{-- Customer --}}
            <td>
                @if ($order->user)
                    <a href="{{ route('admin.users.detail', $order->user->id) }}">
                        {{ $order->user->username }}
                    </a>
                @else
                    @php $addr = json_decode($order->shipping_address); @endphp
                    <span>{{ $addr->name ?? ($order->guest_name ?? 'Guest') }}</span>
                @endif
            </td>

            {{-- Order number --}}
            <td>{{ $order->order_number }}</td>

            {{-- Payment via --}}
            <td>
                @if ($order->is_cod)
                    <span class="color--warning" title="@lang('Cash On Delivery')">
                        {{ @$deposit->gateway->name ?? trans('COD') }}
                    </span>
                @elseif ($order->deposit)
                    <strong class="text-primary">
                        {{ @$order->deposit->gateway->name }}
                    </strong>
                @endif
            </td>

            {{-- Amount --}}
            <td><b>{{ showAmount($order->total_amount) }}</b></td>

            {{-- Payment + order status badges --}}
            <td>{!! $order->paymentBadge() !!}</td>
            <td>{!! $order->statusBadge() !!}</td>

            {{-- Action buttons --}}
            @if ($admin->can('action_orders'))
            <td>
                {{-- Details --}}
                <a href="{{ route('admin.order.details', $order->id) }}" class="btn btn-outline--dark btn-sm">
                    <i class="la la-desktop"></i> @lang('Details')
                </a>

                @php
                    // set defaults
                    $question = null;
                    $canCancel = false;
                    $disabled = false;
                    $buttonText = '';

                    if ($order->status == Status::ORDER_PENDING) {
                        $canCancel = true;
                        $buttonText = 'Processing';
                        $question = 'Are you sure to mark the order as processing?';
                    } elseif ($order->status == Status::ORDER_PROCESSING) {
                        $canCancel = true;
                        $buttonText = 'Dispatch';
                        $question = 'Are you sure to mark the order as dispatched?';
                    } elseif ($order->status == Status::ORDER_DISPATCHED) {
                        $buttonText = 'Deliver';
                        $question = 'Are you sure to mark the order as delivered?';
                    } elseif ($order->status == Status::ORDER_DELIVERED) {
                        $disabled = true;
                        $buttonText = 'Deliver';
                    } elseif ($order->status == Status::ORDER_CANCELED) {
                        $disabled = true;
                        $buttonText = 'Canceled';
                    } elseif ($order->status == Status::ORDER_RETURNED) {
                        $disabled = true;
                        $buttonText = 'Returned';
                    }
                @endphp

                {{-- Main status-advance button --}}
                @if ($order->status == Status::ORDER_PENDING && $order->hasDownloadableProduct())
                    {{-- needs file upload modal --}}
                    <button type="button" class="btn btn-outline--success deliverDPBtn mx-1"
                        data-question="{{ __($question) }}"
                        data-action="{{ route('admin.order.status.change', $order->id) }}"
                        data-has_physical_product="{{ $order->hasPhysicalProduct(true) }}"
                        data-after_sale_downloadable_products="{{ $order->afterSaleDownloadableProducts }}"
                        @disabled($disabled)>
                        <i class="la la-check"></i> {{ __($buttonText) }}
                    </button>
                @else
                    {{-- normal confirmation button --}}
                    <button type="button" class="btn btn-outline--success confirmationBtn mx-1"
                        data-question="{{ __($question) }}"
                        data-action="{{ route('admin.order.status.change', $order->id) }}"
                        @disabled($disabled)>
                        <i class="la la-check"></i> {{ __($buttonText) }}
                    </button>
                @endif
 
                {{-- Cancel or Return --}}
                @if (Route::is('admin.order.dispatched'))
                    {{-- dispatched → show “Return” --}}
                    <button type="button" class="btn btn-outline--danger confirmationBtn"
                        data-question="@lang('Are you sure to change status as returned?')" data-action="{{ route('admin.order.return', $order->id) }}">
                        <i class="las la-undo-alt"></i> @lang('Return')
                    </button>
                @else
                    {{-- other scopes → show “Cancel” --}}
                    <button type="button" class="btn btn-outline--danger confirmationBtn"
                        data-question="@lang('Are you sure to cancel this order?')"
                        data-action="{{ route('admin.order.status.cancel', $order->id) }}"
                        @disabled(!$canCancel)>
                        <i class="la la-ban"></i> @lang('Cancel')
                    </button>
                @endif

                {{-- Add at the top of your Blade file --}}


                {{-- Inside the action column after other buttons --}}
                @if ($order->status == Status::ORDER_CANCELED || $order->status == Status::ORDER_RETURNED)
                    <button type="button" class="btn btn-outline--danger confirmationBtn" 
                        data-question="@lang('Are you sure to delete this order?')" 
                        data-action="{{ route('admin.order.delete', $order->id) }}">
                        <i class="las la-trash"></i> @lang('Delete')
                    </button>
                @endif


            </td>
            @endif
        </tr>
    @empty
        <tr>
            <td class="text-muted text-center" colspan="100%">
                @lang('No orders found')
            </td>
        </tr>
    @endforelse
</tbody>
