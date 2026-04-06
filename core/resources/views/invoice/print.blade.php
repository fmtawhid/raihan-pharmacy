<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $order->order_number }}</title>
</head>

<body onload="window.print()">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            width: 80mm;
            margin: 0;
            padding: 0;
        }
        body {
            padding: 6px 8px;
        }
        .receipt-container {
            max-width: 80mm;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .invoice-header {
            font-size: 11px;
            margin-bottom: 8px;
            line-height: 1.5;
            font-weight: 600;
        }
        .invoice-header p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        table thead th {
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            padding: 3px 2px;
            border-bottom: 1px solid #000;
        }
        table tbody td {
            font-size: 10px;
            padding: 2px 2px;
            border-bottom: 1px dotted #ccc;
            font-weight: 600;
        }
        table tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }
        .summary-section {
            font-size: 11px;
            margin: 8px 0;
            line-height: 1.6;
            font-weight: 600;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
        }
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            margin: 4px 0;
        }
        .footer-section {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
            line-height: 1.5;
            font-weight: 600;
        }
        .thanks {
            font-weight: bold;
            font-size: 10px;
            margin: 6px 0;
        }
    </style>

    <div class="receipt-container">
        <!-- Store Header -->
        <div class="text-center">
            <div class="store-name">{{ gs('site_name') ?? 'Store' }}</div>
        </div>

        <div class="divider"></div>

        <!-- Invoice Details -->
        <div class="invoice-header">
            <p><strong>Invoice No:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ showDateTime($order->created_at, 'd-m-Y H:i:s') }}</p>
            <p><strong>Customer:</strong> {{ $order->user->fullname ?? 'Walk-in Customer' }}</p>
        </div>

        <div class="divider"></div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">S.L</th>
                    <th style="width: 45%;">Item</th>
                    <th style="width: 12%; text-align: center;">Qty</th>
                    <th style="width: 18%; text-align: right;">MRP</th>
                    <th style="width: 17%; text-align: right;">Total</th>
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
                        <td style="text-align: center;">{{ $data->quantity }}</td>
                        <td style="text-align: right;">{{ showAmount($data->price) }}</td>
                        <td style="text-align: right;">{{ showAmount($itemTotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>{{ showAmount($subtotal) }}</span>
            </div>

            @if($totalDiscount > 0)
            <div class="summary-row">
                <span>Discount:</span>
                <span>-{{ showAmount($totalDiscount) }}</span>
            </div>
            @endif

            <div class="summary-total">
                <div class="summary-row">
                    <span>Net Total:</span>
                    <span>{{ showAmount($subtotal - $totalDiscount) }}</span>
                </div>
            </div>

            <div class="summary-row">
                <span>Total Item:</span>
                <span>{{ count($order->orderDetail) }} Item</span>
            </div>

            <div class="summary-row">
                <span>Paid Amount:</span>
                <span>{{ showAmount($order->total_amount) }}</span>
            </div>


            <div class="summary-row">
                <span>Mode of Payment:</span>
                <span>
                    @if (isset($order->deposit) && $order->deposit->method_code == 0)
                        cash_payment
                    @else
                        {{ $order->deposit->gateway->name ?? 'cash_payment' }}
                    @endif
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer-section">
            <p style="font-size: 8px;">Note: Physical damage, burn case, sticker<br>remove are not valid for warranty</p>
            <p class="thanks">*** Thanks For Shopping With Us ***</p>
            <p style="font-size: 9px;">Sold by: {{ auth()->user()->username ?? 'Super Admin' }} {{ showDateTime($order->created_at, 'd-m-Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
