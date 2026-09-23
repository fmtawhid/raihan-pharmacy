<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $order->order_number }}</title>

    <style>
        /*
         * ============================================================
         * 80MM THERMAL RECEIPT - PRINT SAFE VERSION
         * ============================================================
         *
         * Physical paper : 80mm
         * Safe content   : 72mm
         * Left/right     : 4mm each
         *
         * IMPORTANT:
         * - No fixed receipt height
         * - No max-height
         * - No overflow:hidden
         * - No absolute/fixed positioning
         * - All sections stay in normal document flow
         * - Tables use explicit widths so the right edge cannot overflow
         */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: 80mm auto;
            margin: 0;
            padding: 0;
        }

        html {
            width: 80mm;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            width: 80mm;
            min-width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: "Courier New", Courier, monospace;
            font-size: 9px;
            line-height: 1.25;
            overflow: visible;
            overflow-x: hidden;
        }

        /*
         * Do NOT make this 80mm.
         * 78mm leaves minimal safety area on 80mm thermal roll.
         */
        .receipt-container {
            width: 78mm;
            max-width: 78mm;
            margin: 0 auto;
            padding: 2mm 0 2mm;
            background: #fff;
            overflow: visible;
        }

        /* ------------------------------------------------------------
           COMMON
           ------------------------------------------------------------ */

        .divider {
            width: 78mm;
            max-width: 78mm;
            height: 0;
            border-top: 1px dashed #000;
            margin: 1.5mm auto;
        }

        .solid-divider {
            width: 78mm;
            max-width: 78mm;
            height: 0;
            border-top: 1px solid #000;
            margin: 1.5mm auto;
        }

        /* ------------------------------------------------------------
           STORE HEADER
           ------------------------------------------------------------ */

        .store-header {
            width: 78mm;
            max-width: 78mm;
            margin: 0 auto 0.8mm;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .store-name {
            width: 100%;
            font-size: 14.5px;
            line-height: 1.1;
            font-weight: 900;
            text-transform: uppercase;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .receipt-title {
            margin-top: 0.8mm;
            font-size: 10.5px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: 0.4px;
        }

        /* ------------------------------------------------------------
           INVOICE INFO
           ------------------------------------------------------------ */

        .invoice-info {
            width: 78mm;
            max-width: 78mm;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            page-break-inside: avoid;
            break-inside: avoid;
            margin: 0 auto;
        }

        .invoice-info td {
            padding: 0.5mm 0;
            vertical-align: top;
            font-size: 9.2px;
            line-height: 1.2;
            font-weight: 700;
        }

        .invoice-info .label {
            width: 22.5mm;
            white-space: nowrap;
        }

        .invoice-info .value {
            width: 55.5mm;
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* ------------------------------------------------------------
           ITEMS TABLE
           ------------------------------------------------------------ */

        .items-table {
            width: 78mm;
            max-width: 78mm;
            margin: 0.8mm 0 0;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        /*
         * Total = 78mm exactly:
         * SL     5mm
         * ITEM  35mm
         * QTY   11mm
         * MRP   12.5mm
         * TOTAL 14.5mm
         */
        .items-table col.sl {
            width: 5mm;
        }

        .items-table col.item {
            width: 35mm;
        }

        .items-table col.qty {
            width: 11mm;
        }

        .items-table col.mrp {
            width: 12.5mm;
        }

        .items-table col.total {
            width: 14.5mm;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table thead tr {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .items-table th {
            padding: 0.9mm 0.15mm;
            font-size: 8px;
            line-height: 1.02;
            font-weight: 900;
            vertical-align: middle;
            white-space: nowrap;
        }

        .items-table tbody {
            display: table-row-group;
        }

        .items-table tbody tr {
            border-bottom: 1px dotted #777;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .items-table tbody tr:last-child {
            border-bottom: 1px solid #000;
        }

        .items-table td {
            padding: 0.9mm 0.15mm;
            font-size: 8.3px;
            line-height: 1.08;
            font-weight: 700;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .items-table th:nth-child(1),
        .items-table td:nth-child(1) {
            text-align: center;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: left;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            text-align: center;
            white-space: nowrap;
        }

        .items-table th:nth-child(4),
        .items-table td:nth-child(4),
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) {
            text-align: right;
            white-space: nowrap;
            font-size: 7.7px;
        }

        /* ------------------------------------------------------------
           SUMMARY
           ------------------------------------------------------------ */

        .summary-section {
            width: 78mm;
            max-width: 78mm;
            margin-top: 1.2mm;
            page-break-inside: auto;
            break-inside: auto;
            margin-left: auto;
            margin-right: auto;
        }

        .summary-table {
            width: 78mm;
            max-width: 78mm;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .summary-table td {
            padding: 0.5mm 0;
            font-size: 9.2px;
            line-height: 1.18;
            font-weight: 700;
            vertical-align: top;
        }

        .summary-table .summary-label {
            width: 50mm;
            text-align: left;
            padding-right: 1.5mm;
        }

        .summary-table .summary-value {
            width: 28mm;
            text-align: right;
            white-space: nowrap;
        }

        .summary-total-row td {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding-top: 1mm;
            padding-bottom: 1mm;
            font-size: 9.8px;
            font-weight: 900;
        }

        /* ------------------------------------------------------------
           FOOTER
           ------------------------------------------------------------ */

        .footer-section {
            width: 78mm;
            max-width: 78mm;
            text-align: center;
            font-size: 7.6px;
            font-weight: 700;
            line-height: 1.25;
            margin-top: 1.2mm;
            margin-left: auto;
            margin-right: auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .warranty-note,
        .sold-by {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .thanks {
            margin: 1.2mm 0;
            font-size: 9px;
            font-weight: 900;
        }

        /* ------------------------------------------------------------
           PRINT
           ------------------------------------------------------------ */

        @media print {
            html,
            body {
                width: 80mm !important;
                min-width: 80mm !important;
                max-width: 80mm !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
            }

            .receipt-container {
                width: 78mm !important;
                max-width: 78mm !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 auto !important;
                padding: 2mm 0 2mm !important;
                overflow: visible !important;
            }

            .store-header,
            .invoice-info,
            .footer-section {
                width: 78mm !important;
                max-width: 78mm !important;
            }

            .items-table,
            .summary-table {
                width: 78mm !important;
                max-width: 78mm !important;
            }

            .items-table {
                page-break-before: auto !important;
                page-break-after: auto !important;
            }

            .items-table thead {
                display: table-header-group !important;
            }

            .items-table tbody {
                display: table-row-group !important;
            }

            .items-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .store-header,
            .invoice-info,
            .footer-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Screen preview only */
        @media screen {
            body {
                margin: 10px auto;
            }

            .receipt-container {
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.12);
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">

        <!-- STORE HEADER -->
        <div class="store-header">
            <div class="store-name">
                {{ gs('site_name') ?? 'Store' }}
            </div>

            <div class="receipt-title">
                SALES INVOICE
            </div>
        </div>

        <div class="divider"></div>

        <!-- INVOICE INFORMATION -->
        <table class="invoice-info">
            <tbody>
                <tr>
                    <td class="label">Invoice No:</td>
                    <td class="value">{{ $order->order_number }}</td>
                </tr>

                <tr>
                    <td class="label">Date:</td>
                    <td class="value">
                        {{ showDateTime($order->created_at, 'd-m-Y H:i:s') }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Customer:</td>
                    <td class="value">
                        {{ $order->user->fullname ?? 'Walk-in Customer' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- ITEMS -->
        <table class="items-table">
            <colgroup>
                <col class="sl">
                <col class="item">
                <col class="qty">
                <col class="mrp">
                <col class="total">
            </colgroup>

            <thead>
                <tr>
                    <th>S.L</th>
                    <th>ITEM</th>
                    <th>QTY</th>
                    <th>MRP</th>
                    <th>TOTAL</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $subtotal = 0;
                    $totalQty = 0;
                    $totalDiscount = 0;
                @endphp

                @foreach ($order->orderDetail as $index => $data)
                    @php
                        $itemTotal = $data->price * $data->quantity;
                        $subtotal += $itemTotal;
                        $totalQty += $data->quantity;
                        $totalDiscount += $data->discount;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>{{ $data->product->name }}</td>

                        <td>{{ $data->quantity }}</td>

                        <td>{{ showAmount($data->price) }}</td>

                        <td>{{ showAmount($itemTotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- SUMMARY -->
        <div class="summary-section">
            <table class="summary-table">
                <tbody>
                    <tr>
                        <td class="summary-label">Subtotal:</td>
                        <td class="summary-value">{{ showAmount($subtotal) }}</td>
                    </tr>

                    @if ($totalDiscount > 0)
                        <tr>
                            <td class="summary-label">Discount:</td>
                            <td class="summary-value">-{{ showAmount($totalDiscount) }}</td>
                        </tr>
                    @endif

                    <tr class="summary-total-row">
                        <td class="summary-label">NET TOTAL:</td>
                        <td class="summary-value">
                            {{ showAmount($subtotal - $totalDiscount) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="summary-label">Total Item:</td>
                        <td class="summary-value">
                            {{ count($order->orderDetail) }} Item
                        </td>
                    </tr>

                    <tr>
                        <td class="summary-label">Total Qty:</td>
                        <td class="summary-value">{{ $totalQty }}</td>
                    </tr>

                    <tr>
                        <td class="summary-label">Paid Amount:</td>
                        <td class="summary-value">
                            {{ showAmount($order->total_amount) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="summary-label">Payment:</td>
                        <td class="summary-value">
                            @if (isset($order->deposit) && $order->deposit->method_code == 0)
                                Cash
                            @else
                                {{ $order->deposit->gateway->name ?? 'Cash' }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="divider"></div>

        <!-- FOOTER -->
        <div class="footer-section">
            <p class="warranty-note">
                Note: Physical damage, burn case, sticker<br>
                remove are not valid for warranty
            </p>

            <p class="thanks">
                *** THANKS FOR SHOPPING WITH US ***
            </p>

            <p class="sold-by">
                Sold by:
                {{ auth()->user()->username ?? 'Super Admin' }}
            </p>

            <p class="sold-by">
                {{ showDateTime($order->created_at, 'd-m-Y H:i:s') }}
            </p>
        </div>

    </div>

    <script>
        /*
         * Print only after the complete page has loaded and the browser
         * has had time to calculate the full receipt height.
         */
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 700);
        });

        window.addEventListener('afterprint', function () {
            window.close();
        });
    </script>
</body>
</html>
