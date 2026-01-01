@extends('admin.layouts.app')
@php
    $admin = auth()->guard('admin')->user();
@endphp
@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="card border">
                <div class="card-body">
                    <h5 class="card-title">@lang('Sales Summary')</h5>
                    <div class="row g-0">
                        <div class="col-xl-3 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Sales Product')</small>
                                <h6>{{ $totalSalesProduct }}</h6>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Shipping Charge')</small>
                                <h6>{{ showAmount($totalShippingCharge) }}</h6>
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Sales Amount')</small>
                                <h6>{{ showAmount($totalSalesAmount) }}</h6>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Amount')</small>
                                <h6>{{ showAmount($totalAmount) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="printArea">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive--sm table-responsive">
                            <table class="table table--light style--two">
                                <thead>
                                    <tr>
                                        <th>@lang('Order No.')</th>
                                        <th>@lang('Customer')</th>
                                        <th>@lang('Date')</th>
                                        <th>@lang('Total Product')</th>
                                        <th>@lang('Shipping Charge')</th>
                                        <th>@lang('Subtotal')</th>
                                        <th>@lang('Total')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.order.details', $log->id) }}">{{ $log->order_number }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('admin.users.detail', @$log->user->id) }}">{{ @$log->user->username }}</a>
                                            </td>
                                            <td>{{ showDateTime($log->created_at, 'd M, Y') }}</td>
                                            <td>{{ $log->total_product }}</td>
                                            <td>{{ showAmount($log->shipping_charge) }}</td>
                                            <td>{{ showAmount($log->subtotal) }}</td>
                                            <td>{{ showAmount($log->total_amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($logs->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($logs) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- @push('style')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Optional: Hide page footer or pagination */
            .card-footer {
                display: none !important;
            }
        }
    </style>
@endpush --}}


@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">

        <x-search-form dateSearch="yes" class="mb-0" />
        @if($admin->can('print_sales_reports'))
        <button class="btn btn-sm btn-outline--primary" onclick="printDiv('printArea')">
            <i class="las la-print"></i> @lang('Print')
        </button>
        @endif

        {{-- <a href="{{ route('admin.orders.download.excel', request()->all()) }}" class="btn btn-sm btn-outline--dark">
            <i class="las la-file-excel"></i> @lang('Download Excel')
        </a> --}}
      @if($admin->can('download_csv_sales_reports'))
      <a href="{{ route('admin.reports.sales.csv', request()->all()) }}" class="btn btn-sm btn-outline--dark">
            <i class="las la-file-csv"></i> @lang('Download CSV')
        </a>
        @endif
    </div>
@endpush

@push('script')
    <script>
        function printDiv(divId) {
            const content = document.getElementById(divId).innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');

            // Format today's date
            const today = new Date();
            const generatedDate = today.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            const dateRange = `{{ request()->date ? e(request()->date) : 'All Time' }}`;

            printWindow.document.write('<html><head><title>Sales Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body{font-family:Arial, sans-serif; padding:20px;}');
            printWindow.document.write('table{width:100%;border-collapse:collapse;margin-top:20px;}');
            printWindow.document.write('th,td{padding:8px;border:1px solid #ddd;text-align:left;font-size:12px;}');
            printWindow.document.write('h2,h4,p{text-align:center;margin:0;}');
            printWindow.document.write('hr{margin:10px 0;}');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');

            // Header
            printWindow.document.write('<h2>MultiTech BD</h2>');
            printWindow.document.write('<h4>Sales Report</h4>');
            printWindow.document.write(`<p>Date Range: ${dateRange}</p>`);
            printWindow.document.write('<hr>');

            // Table content
            printWindow.document.write(content);

            // Footer
            printWindow.document.write('<hr>');
            printWindow.document.write(
                `<p style="text-align:right;font-size:12px;">Print Generated on: ${generatedDate}</p>`);

            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
@endpush
