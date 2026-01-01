{{-- resources/views/admin/order/all.blade.php --}}
@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    {{-- ───── Tabs ───── --}}
                    <ul class="nav nav-tabs mb-3" id="orderTabs">
                        @foreach (['all', 'pending', 'processing', 'dispatched', 'delivered', 'canceled', 'returned', 'cod'] as $type)
                            <li class="nav-item">
                                <a class="nav-link @if ($loop->first) active @endif" href="#"
                                    data-status="{{ $type }}">
                                    {{ ucfirst($type) }}
                                    @if (!empty($statusesCount[$type]))
                                        <span class="badge bg-primary">{{ $statusesCount[$type] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="d-flex flex-wrap align-items-center gap-2 px-3 mb-3" id="quickDateBar">
                        @php
                            $dateOptions = [
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'last7' => 'Last 7 days',
                                'last30' => 'Last 30 days',
                            ];
                        @endphp

                        @foreach ($dateOptions as $key => $label)
                            <button class="btn btn-sm btn-outline--primary quick-date-btn"
                                data-range="{{ $key }}">{{ $label }}</button>
                        @endforeach

                        {{-- custom range --}}
                        {{-- <div class="input-group input-daterange ms-auto" style="max-width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Start" id="dateStart">
                            <span class="input-group-text px-1">–</span>
                            <input type="text" class="form-control form-control-sm" placeholder="End" id="dateEnd">
                        </div> --}}
                        {{-- add this instead --}}
                        <div class="input-group ms-auto" style="max-width:250px;">
                            <input type="text" id="orderDateRange" class="form-control form-control-sm date-range-picker"
                                placeholder="Start Date – End Date" autocomplete="off">
                            <span class="input-group-text p-1"><i class="las la-search"></i></span>
                        </div>

                    </div>

                    {{-- ── Collapsible search row ────────────────────────── --}}
                    <button class="btn btn-sm btn-light border mb-2 ms-3" data-bs-toggle="collapse"
                        data-bs-target="#advancedFilters">
                        <i class="las la-filter"></i> More
                    </button>

                    <div class="collapse mb-3" id="advancedFilters">
                        <div class="row g-2 px-3">
                            <div class="col-md-3">
                                <label class="form-label mb-0 small">@lang('Order ID')</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" id="filterOrderNo">
                                    <span class="input-group-text p-1"><i class="las la-search"></i></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label mb-0 small">@lang('Customer')</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" id="filterCustomer">
                                    <span class="input-group-text p-1"><i class="las la-search"></i></span>
                                </div>
                            </div>

                            {{-- room for SKU, phone, etc. --}}
                        </div>
                    </div>

                    {{-- ──── Orders table ──── --}}
                    <div id="orderTableContainer">
                        <div class="table-responsive--md table-responsive">
                            <table class="table table--light style--two">
                                <thead>
                                    <tr>
                                        <th>@lang('Order Date')</th>
                                        <th>@lang('Customer')</th>
                                        <th>@lang('Order ID')</th>
                                        <th>@lang('Payment Via')</th>
                                        <th>@lang('Amount')</th>
                                        <th>@lang('Payment Status')</th>
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>

                                {{-- tbody is loaded from a partial so that AJAX can swap it out --}}
                                @include('admin.order.partials.order_table')
                            </table>
                        </div>
                    </div>

                    {{-- pagination (first page load only) --}}
                    @if ($orders->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($orders) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- deliver-file upload modal --}}
    <div id="deliverModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Deliver Product')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            @lang('Submit')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection


@push('breadcrumb-plugins')
    <x-search-form />
@endpush
@push('style')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush


@push('script')
    {{-- ────────────────────────────────────────────────────────────────────────
 | 1. Deliver-Product modal  (unchanged)
 ─────────────────────────────────────────────────────────────────────── --}}
    <script>
        (function($) {
            'use strict';

            $('#orderTableContainer').on('click', '.deliverDPBtn', function() {
                const data = $(this).data();
                const modal = $('#deliverModal');

                let html = `<h6 class="question">${data.question}</h6>`;

                if (data.after_sale_downloadable_products.length) {
                    html += `<div class="alert alert-info p-3 my-2">
                       <small class="text--info">
                         @lang('This order has an after sale downloadable product. So, if you want to mark the order as delivered you need to submit the file here.')
                       </small>
                     </div>`;

                    $.each(data.after_sale_downloadable_products, function(_, p) {
                        html += `<div class="form-group">
                           <label class="required">${nameToTitle(p.name)}</label>
                           <input  type="file"
                                   name="download_file[${p.id}]"
                                   accept=".zip"
                                   class="form-control" required>
                         </div>`;
                    });
                } else if (data.has_physical_product == 0) {
                    html += `<small class="text--info">
                       @lang('Note: This order contains only instant downloadable products. Once processing is complete, the order status will automatically be updated to "Delivered."')
                     </small>`;
                }

                modal.find('form').attr('action', data.action);
                modal.find('.modal-body').html(html);
                modal.modal('show');
            });

            function nameToTitle(name) {
                return name.toLowerCase()
                    .split(' ')
                    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
                    .join(' ');
            }
        })(jQuery);
    </script>

    {{-- ───────────────────────────────────────────────────────────────────────
 | 2. Central filtering (tabs • presets • daterange • keywords)
 ─────────────────────────────────────────────────────────────────────── --}}
    <script src="//cdn.jsdelivr.net/npm/moment@2/moment.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        (function($) {
            'use strict';

            /* Debounce helper (100 ms) — use lodash/underscore if already loaded */
            const debounce = (fn, d = 100) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), d);
                };
            };

            /* Init daterangepicker -------------------------------------------*/
            $('.date-range-picker').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        format: 'YYYY-MM-DD',
                        cancelLabel: 'Clear'
                    }
                })
                .on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') +
                        ' - ' +
                        picker.endDate.format('YYYY-MM-DD'));
                    $('.quick-date-btn').removeClass('active'); // clear presets
                    fireFilter();
                })
                .on('cancel.daterangepicker', function() {
                    $(this).val('');
                    fireFilter();
                });

            /* Tabs ------------------------------------------------------------*/
            $('#orderTabs').on('click', 'a', function(e) {
                e.preventDefault();
                $('#orderTabs a').removeClass('active');
                $(this).addClass('active');
                fireFilter();
            });

            /* Quick-date presets ---------------------------------------------*/
            $('.quick-date-btn').on('click', function() {
                $('.quick-date-btn').removeClass('active');
                $(this).addClass('active');
                $('#orderDateRange').val(''); // clear manual range
                fireFilter();
            });

            /* Click on search icon next to daterange -------------------------*/
            $('#quickDateBar .input-group-text').on('click', fireFilter);

            /* Keyword boxes (keyup + Enter) ----------------------------------*/
            $('#filterOrderNo, #filterCustomer')
                .on('keyup', debounce(fireFilter, 300))
                .on('keypress', function(e) {
                    if (e.which === 13) fireFilter();
                });

            /* Manual range typed then blurred --------------------------------*/
            $('#orderDateRange').on('change', fireFilter);

            /* Central AJAX loader --------------------------------------------*/
            /* Central AJAX loader --------------------------------------------*/
            function fireFilter() {
                const status = $('#orderTabs a.active').data('status') || 'all';
                const dateKey = $('.quick-date-btn.active').data('range') || '';

                let start = '',
                    end = '';
                const rangeVal = $('#orderDateRange').val();
                if (rangeVal.includes(' - '))[start, end] = rangeVal.split(' - ');

                const orderNo = $('#filterOrderNo').val().trim();
                const user = $('#filterCustomer').val().trim();

                let url = "{{ route('admin.order.index') }}";
                const q = [];
                if (status !== 'all') q.push('status=' + status);
                if (start && end) q.push('start=' + start, 'end=' + end);
                else if (dateKey) q.push('date=' + dateKey);
                if (orderNo) q.push('order=' + encodeURIComponent(orderNo));
                if (user) q.push('user=' + encodeURIComponent(user));
                if (q.length) url += '?' + q.join('&');

                $('#orderTableContainer tbody')
                    .html('<tr><td colspan="8" class="text-center p-3">Loading…</td></tr>');

                $.get(url, function(resp) {

                    /* ── 1. swap the table rows ── */
                    $('#orderTableContainer tbody').replaceWith(resp);

                    /* ── 2. refresh tab badges ── */
                    $.get("{{ route('admin.order.status_counts') }}", function(counts) {
                        $('#orderTabs a').each(function() {
                            const type = $(this).data('status');
                            const count = counts[type] ?? 0;

                            $(this).find('span.badge').remove();
                            $(this).append(
                                `<span class="badge bg-primary ms-1">${count}</span>`
                            );
                        });
                    });
                });
            }

        })(jQuery);
    </script>
@endpush
