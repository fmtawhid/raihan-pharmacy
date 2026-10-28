@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs('admin.deposit.list') || request()->routeIs('admin.deposit.method'))
            <div class="col-12">
                @include('admin.deposit.widget')
            </div>
        @endif

        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Total')</th>
                                    <th>@lang('Paid')</th>
                                    <th>@lang('Due')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    @php
                                        $details = $deposit->detail ? json_encode($deposit->detail) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ((int) $deposit->method_code === 0)
                                                <span class="fw-bold">@lang('POS / Manual')</span>
                                            @else
                                                <span class="fw-bold">
                                                    <a href="{{ appendQuery('method', $deposit->method_code < 5000 ? @$deposit->gateway->alias : $deposit->method_code) }}">
                                                        @if ($deposit->method_code < 5000)
                                                            {{ __(@$deposit->gateway->name) }}
                                                        @else
                                                            @lang('Google Pay')
                                                        @endif
                                                    </a>
                                                </span>
                                            @endif
                                            <br>
                                            <small> {{ $deposit->trx }} </small>
                                        </td>

                                        <td>
                                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                        </td>
                                        <td>
                                            @if ($deposit->user)
                                                <span class="fw-bold">{{ $deposit->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    ID: <a href="{{ route('admin.deposit.list', $deposit->user_id) }}">#{{ $deposit->user_id }}</a>
                                                    <br>
                                                    <a
                                                        href="{{ appendQuery('search', $deposit->user->username) }}"><span>@</span>{{ $deposit->user->username }}</a>
                                                </span>
                                            @else
                                                @php $addr = json_decode($deposit->order->shipping_address ?? '') @endphp
                                                <span
                                                    class="fw-bold">{{ $deposit->order->guest_name ?? ($addr->name ?? 'Guest') }}</span>
                                                <br>
                                                <small>@lang('Guest Checkout')</small>
                                            @endif
                                        </td>
                                        @php
                                            $orderTotal = $deposit->order->total_amount ?? $deposit->amount;
                                            $dueAmount = $deposit->order
                                                ? max((float) $orderTotal - (float) ($deposit->order->paid_total ?? 0), 0)
                                                : 0;
                                        @endphp
                                        <td>{{ showAmount($orderTotal) }}</td>
                                        <td>
                                            @if ($deposit->status == \App\Constants\Status::PAYMENT_SUCCESS)
                                                <span class="text--success">@lang('Paid') {{ showAmount($deposit->amount) }}</span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($deposit->status == \App\Constants\Status::PAYMENT_PENDING && $deposit->order)
                                                <span class="text--warning">@lang('Due') {{ showAmount($dueAmount) }}</span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $deposit->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.deposit.details', $deposit->id) }}"
                                                class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="la la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($deposits) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' placeholder='Username / Email' />
@endpush
