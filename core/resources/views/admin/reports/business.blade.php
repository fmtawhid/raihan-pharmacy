@extends('admin.layouts.app')
@php
    $admin = auth()->guard('admin')->user();
@endphp
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-header">
                    <h5 class="card-title mb-0">Business Report</h5>
                    <span class="text-muted">Day: {{ now()->format('d M Y') }}</span>
                    <div class="d-flex justify-content-end gap-2 px-4 mb-3">
                        @if($admin->can('print_business_reports'))
                            <button onclick="printDiv('printArea')" class="btn btn-sm btn-outline--primary">
                                <i class="las la-print"></i> Print
                            </button>
                        @endif
                        @if($admin->can('download_csv_sales_reports'))
                            <a href="{{ route('admin.reports.business.csv', request()->all()) }}"
                               class="btn btn-sm btn-outline--dark">
                                <i class="las la-file-csv"></i> Download CSV
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="print-summary print-hidden mb-4">
                        <h6 class="fw-bold mb-3">Summary (Filtered)</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="p-3 border rounded">
                                    <small class="text-muted">Total Unique Products</small>
                                    <h6>{{ $totalProducts }}</h6>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded">
                                    <small class="text-muted">Total Quantity</small>
                                    <h6>{{ $totalQuantity }}</h6>
                                </div>
                            </div>
                            @foreach ($statusCounts as $status => $count)
                                <div class="col-md-2">
                                    <div class="p-3 border rounded">
                                        <small class="text-muted">{{ $status }}</small>
                                        <h6>{{ $count }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <form method="GET" action="" class="row gy-2 gx-3 align-items-center">
                        <div class="col-md-2">
                            <input type="text" name="order_number" class="form-control" placeholder="Order Number"
                                value="{{ request('order_number') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="sku" class="form-control" placeholder="SKU"
                                value="{{ request('sku') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="product" class="form-control" placeholder="Product"
                                value="{{ request('product') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="date" class="form-control date-range-picker"
                                placeholder="Date Range" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach ([
            0 => 'Pending',
            1 => 'Processing',
            2 => 'Dispatched',
            3 => 'Delivered',
            4 => 'Cancelled',
            9 => 'Returned',
        ] as $key => $label)
                                    <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn--primary w-100" type="submit"><i class="la la-search"></i></button>
                        </div>
                    </form>
                </div>


                <div class="card-body p-0">

                    <div id="printArea">




                        <div class="table-responsive--sm table-responsive">
                            <table class="table table--light style--two">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>Order Number</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalQuantity = 0; @endphp
                                    @forelse($orders as $order)
                                        @foreach ($order->orderDetail as $detail)
                                            @php
                                                $product = $detail->product;
                                                $variant = $detail->productVariant;
                                                $sku = $variant->sku ?? ($product->sku ?? 'N/A');
                                                $image = $variant?->mainImage(false) ?? $product?->mainImage(false);
                                                $name = $product?->name ?? 'N/A';
                                                $quantity = $detail->quantity;
                                                $totalQuantity += $quantity;
                                            @endphp
                                            <tr>
                                                <td>{{ $sku }}

                                                </td>
                                                <td>
                                                    <img src="{{ $image }}" width="40" alt="Product Image">
                                                </td>
                                                <td>
                                                    @if ($variant && $variant->name)
                                                        {{ $product?->name ?? 'N/A' }}<br>
                                                        <small class="text-muted">{{ $variant->name }}</small>
                                                    @else
                                                        {{ $product?->name ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td>{{ $order->order_number }}</td>
                                                <td>{{ $quantity }}</td>
                                                <td>{!! $order->statusBadge() !!}</td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No data available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total</td>
                                        <td class="fw-bold">{{ $totalQuantity }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <br>

                            {{-- PRINT VERSION ONLY --}}
                            <div class="d-none d-print-block mt-4" style="font-size: 12px;">
                                <strong>Summary (Filtered):</strong>
                                Total Unique Products: {{ $totalProducts }},
                                Total Quantity: {{ $totalQuantity }}
                                @foreach ($statusCounts as $status => $count)
                                    , {{ $status }}: {{ $count }}
                                @endforeach
                            </div>



                        </div>
                    </div>
                </div>

                @if ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer py-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    {{-- <style>
        @media print {

            form,
            .card-header,
            .card-footer,
            .btn,
            .pagination {
                display: none !important;
            }

            table {
                font-size: 12px;
            }

            body {
                background: white !important;
            }
        }
    </style> --}}
@endpush

@push('script')
    <script src="//cdn.jsdelivr.net/npm/moment@2/moment.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            $('.date-range-picker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            $('.date-range-picker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            $('.date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>

    <script>
        function printDiv(divId) {
            const content = document.getElementById(divId).innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');

            const today = new Date();
            const generatedDate = today.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            const dateRange = `{{ request()->date ? e(request()->date) : 'All Time' }}`;

            printWindow.document.write('<html><head><title>Business Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body{font-family:Arial, sans-serif; padding:20px;}');
            printWindow.document.write('table{width:100%;border-collapse:collapse;margin-top:20px;}');
            printWindow.document.write('th,td{padding:8px;border:1px solid #ddd;text-align:left;font-size:12px;}');
            printWindow.document.write('h2,h4,p{text-align:center;margin:0;}');
            printWindow.document.write('hr{margin:10px 0;}');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<h2>MultiTech BD</h2>');
            printWindow.document.write('<h4>Business Report</h4>');
            printWindow.document.write(`<p>Date Range: ${dateRange}</p>`);
            printWindow.document.write('<hr>');

            printWindow.document.write(content);

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
