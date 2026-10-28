@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="card border">
                <div class="card-body">
                    <h5 class="card-title">@lang('Return Summary')</h5>
                    <div class="row g-0">
                        <div class="col-xl-4 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Returns')</small>
                                <h6>{{ $summary->return_count }}</h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Quantity Returned')</small>
                                <h6>{{ $summary->total_quantity }}</h6>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6">
                            <div class="p-3 border h-100">
                                <small class="text-muted">@lang('Total Refund Amount')</small>
                                <h6>{{ showAmount($summary->total_refund) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="printArea" class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Return ID')</th>
                                    <th>@lang('Order No.')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quantity Returned')</th>
                                    <th>@lang('Refund Amount')</th>
                                    <th>@lang('Return Date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr>
                                        <td>
                                            @if($return->order)
                                                <a href="{{ route('admin.order.details', $return->order->id) }}" class="text--primary">#{{ $return->id }}</a>
                                            @else
                                                #{{ $return->id }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($return->order)
                                                <a href="{{ route('admin.order.details', $return->order->id) }}" class="text--primary">
                                                    {{ $return->order->order_number }}
                                                </a>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ optional($return->product)->name ?? '--' }}</td>
                                        <td>{{ $return->quantity_returned }}</td>
                                        <td>{{ showAmount($return->refund_amount) }}</td>
                                        <td>{{ showDateTime($return->created_at, 'd M, Y') }}</td>
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
            </div>
        </div>

        @if ($returns->hasPages())
            <div class="col-lg-12">
                <div class="card-footer py-4">
                    {{ paginateLinks($returns) }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
        <x-search-form placeholder="Search order or product" dateSearch="yes" class="mb-0" />
        <button class="btn btn-sm btn-outline--primary" onclick="printDiv('printArea')">
            <i class="las la-print"></i> @lang('Print')
        </button>
    </div>
@endpush

@push('script')
    <script>
        function printDiv(divId) {
            const content = document.getElementById(divId).innerHTML;
            const printWindow = window.open('', '', 'height=600,width=900');

            if (!printWindow) {
                window.alert('Please allow pop-ups to print this report.');
                return;
            }

            const generatedDate = new Date().toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            const dateRange = @json(e(request()->date ?: 'All Time'));

            printWindow.document.write('<html><head><title>Return Report</title><meta charset="UTF-8">');
            printWindow.document.write('<style>body{font-family:Arial,sans-serif;padding:20px}.print-header{text-align:center;margin-bottom:20px;border-bottom:2px solid #333;padding-bottom:10px}.print-header h2{margin:5px}.print-summary{margin:20px 0;padding:15px;background:#f5f5f5}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:12px}th,td{padding:8px 10px;border:1px solid #ddd;text-align:left}th{background:#f0f0f0}.print-footer{margin-top:20px;padding-top:10px;border-top:1px solid #ddd;font-size:12px;text-align:right}@media print{body{padding:0}}</style>');
            printWindow.document.write('</head><body><div class="print-header"><h2>Raihan Pharmacy</h2><h3>Return Report</h3><p>Date Range: ' + dateRange + '</p></div>');
            printWindow.document.write(content);
            printWindow.document.write('<div class="print-footer">Report Generated on: ' + generatedDate + '</div></body></html>');
            printWindow.document.close();
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 250);
        }
    </script>
@endpush
