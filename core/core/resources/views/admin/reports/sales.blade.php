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
                                            <td>
                                                @if($log->user)
                                                    <a href="{{ route('admin.users.detail', $log->user->id) }}">
                                                        {{ $log->user->username }}
                                                    </a>
                                                @else
                                                    {{  $log->guest_name }}
                                                @endif
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
            const printWindow = window.open('', '', 'height=600,width=900');

            // Format today's date
            const today = new Date();
            const generatedDate = today.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            const dateRange = `{{ request()->date ? e(request()->date) : 'All Time' }}`;

            printWindow.document.write('<html><head><title>Sales Report</title>');
            printWindow.document.write('<meta charset="UTF-8">');
            printWindow.document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
            printWindow.document.write('<style>');
            printWindow.document.write(`
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .print-header h2 { font-size: 24px; font-weight: bold; margin: 5px 0; }
                .print-header h4 { font-size: 18px; margin: 5px 0; color: #666; }
                .print-header p { font-size: 14px; color: #999; margin: 5px 0; }
                .print-summary { margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-radius: 4px; }
                .print-summary p { font-size: 13px; margin: 8px 0; display: flex; justify-content: space-between; }
                .print-summary strong { font-weight: 600; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th { background-color: #f0f0f0; padding: 10px; border: 1px solid #ddd; text-align: left; font-weight: 600; }
                td { padding: 8px 10px; border: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #fafafa; }
                .print-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 12px; text-align: right; color: #999; }
                @media print {
                    body { padding: 0; }
                    .print-header, .print-summary, table { page-break-inside: avoid; }
                }
            `);
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');

            // Header
            printWindow.document.write('<div class="print-header">');
            printWindow.document.write('<h2>Raihan Pharmacy</h2>');
            printWindow.document.write('<h4>Sales Report</h4>');
            printWindow.document.write(`<p>Date Range: <strong>${dateRange}</strong></p>`);
            printWindow.document.write('</div>');

            // Table content
            printWindow.document.write(content);

            // Footer
            printWindow.document.write('<div class="print-footer">');
            printWindow.document.write(`<p>Report Generated on: ${generatedDate}</p>`);
            printWindow.document.write('</div>');

            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            // Delay print to ensure content is rendered
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 250);
        }
    </script>
@endpush
