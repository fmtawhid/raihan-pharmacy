@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12 d-flex flex-column">
            <div class="card mb-4 order-1">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg--primary text-white fw-bold"
                        style="width: 64px; height: 64px; font-size: 24px; flex: 0 0 64px;">
                        {{ strtoupper(substr($user->firstname ?: $user->username, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1">{{ $user->fullname }}</h3>
                        <div class="text-muted">{{ $user->username }} · {{ $user->email }}</div>
                        <div class="text-muted">{{ $user->mobileNumber }} @if($user->address) · {{ $user->address }} @endif</div>
                    </div>
                    <a href="#updateCustomerInfo" class="btn btn-sm btn-outline--primary">
                        <i class="las la-user-edit"></i> @lang('Update Info')
                    </a>
                </div>
            </div>

            <div class="row gy-4 order-1 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" color="primary" icon="la la-receipt"
                        value="{{ $posOrders->count() }}" title="POS Bills" icon_style="solid" overlay_icon="0" />
                </div>
                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" color="info" icon="la la-shopping-bag"
                        value="{{ showAmount($posTotalAmount) }}" title="POS Total" icon_style="solid" overlay_icon="0" />
                </div>
                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" color="success" icon="la la-wallet"
                        value="{{ showAmount($posPaidAmount) }}" title="POS Paid" icon_style="solid" overlay_icon="0" />
                </div>
                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" color="warning" icon="la la-money-bill"
                        value="{{ showAmount($posDueAmount) }}" title="POS Due" icon_style="solid" overlay_icon="0" />
                </div>
            </div>

            <div class="row gy-4 order-3 mt-2">

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="primary" icon="la la-shopping-cart"
                        link="{{ route('admin.order.index') }}?search={{ $user->username }}"
                        value="{{ $widget['totalOrders'] }}" title="Total Orders" icon_style="solid" overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="danger" icon="la la-times-circle"
                        link="{{ route('admin.order.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['canceledOrders'] }}" title="Canceled Orders" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="warning" icon="la la-pause-circle"
                        link="{{ route('admin.order.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['pendingOrders'] }}" title="Pending Orders" icon_style="solid" overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="info" icon="la la-play"
                        link="{{ route('admin.order.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['processingOrders'] }}" title="On Processing Orders" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="green" icon="la la-truck"
                        link="{{ route('admin.order.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['dispatchedOrders'] }}" title="Dispatched Orders" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="red" icon="la la-undo"
                        link="{{ route('admin.order.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['returnedOrders'] }}" title="Returned Orders" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="success" icon="la la-check-circle"
                        link="{{ route('admin.order.delivered') }}?search={{ $user->username }}"
                        value="{{ $widget['deliveredOrders'] }}" title="Delivered Orders" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" link="{{ route('admin.deposit.successful') }}?search={{ $user->username }}"
                        cover_cursor="1" color="success" icon="la la-wallet" value="{{ $widget['successfulPayment'] }}"
                        title="Successful Payment" icon_style="solid" overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="warning" icon="la la-wallet"
                        link="{{ route('admin.deposit.pending') }}?search={{ $user->username }}"
                        value="{{ $widget['pendingPayments'] }}" title="Pending Payments" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" color="dark" icon="la la-money-bill"
                        value="{{ showAmount($widget['totalPaymentCharge']) }}" title="Total Payment Charge"
                        icon_style="solid" overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="warning" icon="la la-ticket"
                        link="{{ route('admin.ticket.pending') }}?user={{ $user->username }}"
                        value="{{ $widget['openedTickets'] }}" title="Opened Tickets" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="dark" icon="la la-ticket"
                        link="{{ route('admin.ticket.answered') }}?user={{ $user->username }}"
                        value="{{ $widget['answeredTickets'] }}" title="Answered Tickets" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="success" icon="la la-ticket"
                        link="{{ route('admin.ticket.closed') }}?user={{ $user->username }}"
                        value="{{ $widget['closedTickets'] }}" title="Closed Tickets" icon_style="solid"
                        overlay_icon="0" />
                </div>

                <div class="col-xl-3 col-sm-6">
                    <x-widget style="2" cover_cursor="1" color="info" icon="la la-ticket"
                        link="{{ route('admin.ticket.index') }}?user={{ $user->username }}"
                        value="{{ $widget['openedTickets'] + $widget['answeredTickets'] + $widget['closedTickets'] }}"
                        title="Total Tickets" icon_style="solid" overlay_icon="0" />
                </div>
                <!-- dashboard-w1 end -->
            </div>

            <div class="mt-4 mb-4 order-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h4 class="mb-0">@lang('POS Orders')</h4>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($posOrders->isNotEmpty())
                            <form id="pos-order-print-form" action="{{ route('admin.users.pos.invoice', $user->id) }}"
                                method="GET" target="_blank"></form>
                            <button type="submit" form="pos-order-print-form" id="print-selected-pos-orders"
                                class="btn btn--primary" disabled>
                                <i class="las la-print"></i> @lang('Print Selected')
                                (<span id="selected-pos-order-count" style="color: #ffffff;">0</span>)
                            </button>
                            <a href="{{ route('admin.users.pos.invoice', $user->id) }}" target="_blank"
                                class="btn btn--secondary">
                                <i class="las la-print"></i> @lang('Print All')
                            </a>
                        @endif
                        @if ($posDueAmount > 0)
                            <form action="{{ route('admin.users.pos.payment.all', $user->id) }}" method="POST"
                                onsubmit="return confirm('@lang('Pay all POS invoice dues separately?')')">
                                @csrf
                                <button type="submit" class="btn btn--success">
                                    <i class="las la-money-bill-wave"></i>
                                    @lang('Pay All Due') ({{ showAmount($posDueAmount) }})
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table--light style--two mb-0">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all-pos-orders"
                                                aria-label="@lang('Select all POS orders')"
                                                @disabled($posOrders->isEmpty())>
                                        </th>
                                        <th>@lang('Date')</th>
                                        <th>@lang('POS Order')</th>
                                        <th>@lang('Items')</th>
                                        <th>@lang('Total')</th>
                                        <th>@lang('Paid')</th>
                                        <th>@lang('Due')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($posOrders as $posOrder)
                                        @php
                                            $posPaidAmount = (float) $posOrder->deposits
                                                ->where('status', Status::PAYMENT_SUCCESS)
                                                ->sum('amount');
                                            if ($posPaidAmount <= 0 && $posOrder->payment_status == Status::PAYMENT_SUCCESS) {
                                                $posPaidAmount = (float) $posOrder->total_amount;
                                            }
                                            $posPaidAmount = min($posPaidAmount, (float) $posOrder->total_amount);
                                            $posDueAmount = max((float) $posOrder->total_amount - $posPaidAmount, 0);
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" form="pos-order-print-form"
                                                    class="pos-order-checkbox" name="order_ids[]"
                                                    value="{{ $posOrder->id }}"
                                                    aria-label="@lang('Select POS order') {{ $posOrder->order_number }}">
                                            </td>
                                            <td>{{ showDateTime($posOrder->created_at) }}</td>
                                            <td>{{ $posOrder->order_number }}</td>
                                            <td>
                                                @foreach ($posOrder->orderDetail as $item)
                                                    <div>{{ $item->product->name ?? __('Product removed') }} x {{ $item->quantity }}</div>
                                                @endforeach
                                            </td>
                                            <td>{{ showAmount($posOrder->total_amount) }}</td>
                                            <td>{{ showAmount($posPaidAmount) }}</td>
                                            <td>{{ showAmount($posDueAmount) }}</td>
                                            <td>
                                                <div class="d-flex flex-column gap-2" style="min-width: 190px;">
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('admin.order.details', $posOrder->id) }}"
                                                            class="btn btn-sm btn-outline--primary" title="@lang('Order details')">
                                                            <i class="las la-eye"></i>
                                                        </a>
                                                    </div>
                                                    @if ($posDueAmount > 0)
                                                        <form action="{{ route('admin.users.pos.payment', [$user->id, $posOrder->id]) }}"
                                                            method="POST" class="input-group input-group-sm"
                                                            onsubmit="return confirm('@lang('Record payment for this invoice?')')">
                                                            @csrf
                                                            <input type="number" name="amount" class="form-control" min="0.01"
                                                                max="{{ $posDueAmount }}" step="0.01" value="{{ number_format($posDueAmount, 2, '.', '') }}"
                                                                aria-label="@lang('Payment amount')" required>
                                                            <button type="submit" class="btn btn--success" title="@lang('Pay this invoice')">
                                                                <i class="las la-money-bill-wave"></i> @lang('Pay')
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-3">@lang('No POS orders for this customer.')</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap-reverse justify-content-between align-items-end gap-3 mt-5 mb-3 order-4">
                <h4 id="updateCustomerInfo">@lang('Update Customer Info')</h4>
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <div>
                        <a href="{{ route('admin.report.login.history') }}?search={{ $user->username }}"
                            class="btn btn--primary btn--shadow btn-lg">
                            <i class="las la-list-alt"></i>@lang('View Login History')
                        </a>
                    </div>

                    <div>
                        <a href="{{ route('admin.users.notification.log', $user->id) }}"
                            class="btn btn--secondary btn--shadow btn-lg">
                            <i class="las la-bell"></i>@lang('View Notification Log')
                        </a>
                    </div>

                    <div>
                        @if ($user->status == Status::USER_ACTIVE)
                            <button type="button" class="btn btn--warning btn--shadow btn-lg userStatus"
                                data-bs-toggle="modal" data-bs-target="#userStatusModal">
                                <i class="las la-ban"></i>@lang('Ban This Customer')
                            </button>
                        @else
                            <button type="button" class="btn btn--success btn--shadow btn-lg userStatus"
                                data-bs-toggle="modal" data-bs-target="#userStatusModal">
                                <i class="las la-undo"></i>@lang('Unban This Customer')
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card order-4">
                <div class="card-body">
                    <form action="{{ route('admin.users.update', [$user->id]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('First Name')</label>
                                    <input class="form-control" type="text" name="firstname" required
                                        value="{{ $user->firstname }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">@lang('Last Name')</label>
                                    <input class="form-control" type="text" name="lastname" required
                                        value="{{ $user->lastname }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Email')</label>
                                    <input class="form-control" type="email" name="email"
                                        value="{{ $user->email }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Mobile Number')</label>
                                    <div class="input-group ">
                                        <span class="input-group-text mobile-code">+{{ $user->dial_code }}</span>
                                        <input type="number" name="mobile" value="{{ $user->mobile }}" id="mobile"
                                            class="form-control checkUser" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label>@lang('Address')</label>
                                    <input class="form-control" type="text" name="address"
                                        value="{{ @$user->address }}">
                                </div>
                            </div>

                            {{-- <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('City')</label>
                                    <input class="form-control" type="text" name="city"
                                        value="{{ @$user->city }}">
                                </div>
                            </div> --}}

                            {{-- <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('State')</label>
                                    <input class="form-control" type="text" name="state"
                                        value="{{ @$user->state }}">
                                </div>
                            </div> --}}

                            {{-- <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('Zip/Postal')</label>
                                    <input class="form-control" type="text" name="zip"
                                        value="{{ @$user->zip }}">
                                </div>
                            </div> --}}

                            {{-- Division --}}
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('Division')</label>
                                    <select name="division_id" id="division" class="form-control" required></select>
                                </div>
                            </div>

                            {{-- District --}}
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('District')</label>
                                    <select name="district_id" id="district" class="form-control" required></select>
                                </div>
                            </div>

                            {{-- Area / Upazila --}}
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('Area')</label>
                                    <select name="area_name" id="area" class="form-control" required></select>
                                </div>
                            </div>

                            {{-- Postcode --}}
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('Postcode')</label>
                                    <input type="text" name="postcode" id="postcode" class="form-control"
                                        value="{{ $user->postcode }}" readonly required>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-12">
                                <div class="form-group">
                                    <label>@lang('Country') <span class="text--danger">*</span></label>
                                    <select name="country" class="form-control select2">
                                        @foreach ($countries as $key => $country)
                                            <option data-mobile_code="{{ $country->dial_code }}"
                                                value="{{ $key }}" @selected($user->country_code == $key)>
                                                {{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Email Verification')</label>
                                    <input type="checkbox" data-width="100%" data-onstyle="-success"
                                        data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')"
                                        data-off="@lang('Unverified')" name="ev"
                                        @if ($user->ev) checked @endif>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Mobile Verification')</label>
                                    <input type="checkbox" data-width="100%" data-onstyle="-success"
                                        data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')"
                                        data-off="@lang('Unverified')" name="sv"
                                        @if ($user->sv) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Prescription Uploads ===== --}}
    <div class="row mt-4 order-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="las la-file-medical me-2"></i>@lang('Uploaded Prescriptions')</h5>
                    <span class="badge bg--primary fs-6">{{ $prescriptions->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($prescriptions->count())
                    <div class="table-responsive">
                        <table class="table table--light style--two mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('File Name')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Note')</th>
                                    <th>@lang('Uploaded At')</th>
                                    <th>@lang('View')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prescriptions as $index => $rx)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $rx->original_name }}</td>
                                    <td><span class="badge bg--info">{{ strtoupper($rx->file_extension) }}</span></td>
                                    <td>{{ $rx->note ?? '—' }}</td>
                                    <td>{{ $rx->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ asset('storage/' . $rx->file_path) }}" target="_blank"
                                            class="btn btn-sm btn--primary">
                                            <i class="las la-eye"></i> @lang('View')
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="las la-file-medical-alt fs-2"></i>
                        <p class="mt-1 mb-0">@lang('No prescriptions uploaded by this user.')</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- ===== End Prescription Uploads ===== --}}

    <div id="userStatusModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($user->status == Status::USER_ACTIVE)
                            @lang('Ban User')
                        @else
                            @lang('Unban User')
                        @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if ($user->status == Status::USER_ACTIVE)
                            <h6 class="mb-2">@lang('If you ban this user he/she won\'t able to access his/her dashboard.')</h6>
                            <div class="form-group">
                                <label>@lang('Reason')</label>
                                <textarea class="form-control" name="reason" rows="4" required></textarea>
                            </div>
                        @else
                            <p><span>@lang('Ban reason was'):</span></p>
                            <p>{{ $user->ban_reason }}</p>
                            <h4 class="text-center mt-3">@lang('Are you sure to unban this user?')</h4>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if ($user->status == Status::USER_ACTIVE)
                            <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                        @else
                            <button type="button" class="btn btn--dark"
                                data-bs-dismiss="modal">@lang('No')</button>
                            <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.login', $user->id) }}" target="_blank" class="btn btn-sm btn-outline--primary"><i
            class="las la-sign-in-alt"></i>@lang('Login as Customer')</a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let mobileElement = $('.mobile-code');
            $('select[name=country]').on('change', function() {
                mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
            });

        })(jQuery);
    </script>
@endpush

@push('script')
    <script>
        (function() {
            const selectAll = document.getElementById('select-all-pos-orders');
            const orderCheckboxes = Array.from(document.querySelectorAll('.pos-order-checkbox'));
            const printButton = document.getElementById('print-selected-pos-orders');
            const selectedCount = document.getElementById('selected-pos-order-count');

            if (!selectAll || !printButton || !selectedCount) {
                return;
            }

            function updateSelection() {
                const selectedOrders = orderCheckboxes.filter((checkbox) => checkbox.checked).length;
                selectedCount.textContent = selectedOrders;
                printButton.disabled = selectedOrders === 0;
                selectAll.checked = orderCheckboxes.length > 0 && selectedOrders === orderCheckboxes.length;
                selectAll.indeterminate = selectedOrders > 0 && selectedOrders < orderCheckboxes.length;
            }

            selectAll.addEventListener('change', function() {
                orderCheckboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                updateSelection();
            });

            orderCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', updateSelection);
            });
        })();
    </script>
@endpush

@push('script')
<script>
(function ($) {
    "use strict";

    /* ─── 1. raw JSON from controller ─────────────────────────────── */
    const divisions = @json($bdData['divisions']);
    const districts = @json($bdData['districts']);
    const upazilas  = @json($bdData['upazilas']);
    const dhakaCity = @json($bdData['dhaka']);
    const postcodes = @json(json_decode(file_get_contents(resource_path('data/bd-postcodes.json')), true)['postcodes']);

    /* ─── 2. currently-saved values (so we can pre-select) ────────── */
    const selectedDivision = '{{ $user->division_id }}';
    const selectedDistrict = '{{ $user->district_id }}';
    const selectedArea     = '{{ $user->area_name }}';

    /* ─── 3. helpers ──────────────────────────────────────────────── */
    function fill($sel, items, label = 'name', value = 'id', selected = null) {
        $sel.empty().append('<option value="">@lang("Select")</option>');
        items.forEach(i => {
            $sel.append(
                `<option value="${i[value]}" ${i[value] == selected ? 'selected' : ''}>${i[label]}</option>`
            );
        });
    }

    /* ─── 4. change-handlers ─────────────────────────────────────── */
    $('#division').on('change', function () {
        const id = $(this).val();
        fill($('#district'),
             districts.filter(d => d.division_id === id));
        $('#area').empty(); $('#postcode').val('');
    });

    $('#district').on('change', function () {
        const id   = $(this).val();
        const area = id === '1'
            ? dhakaCity.map(a => ({name: a.name}))
            : upazilas.filter(u => u.district_id === id);
        area.sort((a,b)=>a.name.localeCompare(b.name));
        fill($('#area'), area, 'name', 'name');
        $('#postcode').val('');
    });

    $('#area').on('change', function () {
        const area = $(this).val();
        const did  = $('#district').val();
        const hits = postcodes.filter(p =>
            p.district_id === did &&
            (p.upazila.toLowerCase() === area.toLowerCase() ||
             p.postOffice.toLowerCase() === area.toLowerCase())
        );

        if (hits.length === 1) {
            $('#postcode').val(hits[0].postCode).prop('readonly', true);
        } else if (hits.length > 1) {
            const $sel = $('<select name="postcode" id="postcode" class="form-control" required>');
            $sel.append('<option value="">@lang("Select")</option>');
            hits.forEach(p =>
                $sel.append(`<option value="${p.postCode}">${p.postOffice} (${p.postCode})</option>`)
            );
            $('#postcode').replaceWith($sel);
        } else {
            $('#postcode').replaceWith(
                '<input type="text" name="postcode" id="postcode" class="form-control" required>'
            );
        }
    });

    /* ─── 5. initial render with existing data ───────────────────── */
    fill($('#division'), divisions, 'name', 'id', selectedDivision);
    if (selectedDivision) $('#division').trigger('change');
    if (selectedDistrict) {
        $('#district').val(selectedDistrict).trigger('change');
    }
    if (selectedArea) {
        $('#area').val(selectedArea).trigger('change');
    }

})(jQuery);
</script>
@endpush
