<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 14mm; }
        body {
            margin: 0;
            background: #edf1f3;
            color: #1d292d;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .toolbar { width: 210mm; margin: 16px auto; text-align: right; }
        .toolbar button {
            border: 0;
            border-radius: 4px;
            padding: 10px 16px;
            background: #146c5b;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
        }
        .invoice {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            padding: 14mm;
            background: #fff;
            box-shadow: 0 3px 18px rgba(20, 43, 47, .12);
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 18px;
            border-bottom: 2px solid #146c5b;
        }
        .store-name { margin: 0; color: #146c5b; font-size: 25px; line-height: 1.2; }
        .invoice-label { margin: 5px 0 0; color: #607176; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { margin: 0 0 7px; font-size: 22px; }
        .muted { color: #607176; }
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding: 20px 0; }
        .section-label { margin: 0 0 7px; color: #607176; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .party-name { margin: 0 0 4px; font-size: 15px; font-weight: 700; }
        .items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .items th { padding: 9px 8px; background: #edf4f2; color: #28433e; text-align: left; font-size: 10px; text-transform: uppercase; }
        .items td { padding: 10px 8px; border-bottom: 1px solid #e5ebea; vertical-align: top; }
        .items .number { text-align: right; white-space: nowrap; }
        .summary-area { display: flex; justify-content: flex-end; margin: 18px 0; }
        .summary { width: 270px; border-collapse: collapse; }
        .summary td { padding: 5px 0; }
        .summary td:last-child { text-align: right; white-space: nowrap; }
        .summary .grand-total td { padding-top: 9px; border-top: 1px solid #9aadaa; font-size: 15px; font-weight: 700; }
        .balance { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 18px 0 24px; }
        .balance > div { padding: 12px; border: 1px solid #dce6e3; border-radius: 4px; }
        .balance span { display: block; color: #607176; font-size: 10px; text-transform: uppercase; }
        .balance strong { display: block; margin-top: 3px; font-size: 16px; }
        .history { margin-top: 18px; }
        .history h3 { margin: 0 0 8px; font-size: 14px; }
        .history table { width: 100%; border-collapse: collapse; }
        .history th, .history td { padding: 7px 8px; border-bottom: 1px solid #e5ebea; text-align: left; }
        .history th { color: #607176; font-size: 10px; text-transform: uppercase; }
        .history td:last-child, .history th:last-child { text-align: right; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #dce6e3; color: #607176; text-align: center; }
        @media screen and (max-width: 760px) {
            .toolbar, .invoice { width: 100%; }
            .invoice { min-height: 0; padding: 20px; }
            .header { gap: 12px; }
        }
        @media print {
            body { background: #fff; font-size: 11px; }
            .toolbar { display: none; }
            .invoice { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            .items tr, .balance, .history tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print A4 Invoice</button></div>
    <main class="invoice">
        @php
            $subtotal = 0;
            $totalDiscount = 0;
            $totalQty = 0;
            foreach ($order->orderDetail as $line) {
                $subtotal += (float) $line->price * (int) $line->quantity;
                $totalDiscount += (float) $line->discount;
                $totalQty += (int) $line->quantity;
            }
        @endphp
        <header class="header">
            <div>
                <h1 class="store-name">{{ gs('site_name') ?? 'Store' }}</h1>
                <p class="invoice-label">Sales invoice</p>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <div><strong>{{ $order->order_number }}</strong></div>
                <div class="muted">{{ showDateTime($order->created_at, 'd M Y, h:i A') }}</div>
            </div>
        </header>

        <section class="parties">
            <div>
                <p class="section-label">Billed to</p>
                <p class="party-name">{{ $order->user->fullname ?? 'Walk-in Customer' }}</p>
                @if ($order->user)
                    <div>{{ $order->user->email }}</div>
                    <div>{{ $order->user->mobileNumber }}</div>
                    <div>{{ $order->user->address }}</div>
                @endif
            </div>
            <div>
                <p class="section-label">Payment</p>
                <div>{{ $order->deposit && $order->deposit->method_code == 0 ? 'Cash' : ($order->deposit->gateway->name ?? 'Cash') }}</div>
                <div class="muted">Status: {{ $dueAmount > 0 ? 'Partially paid' : 'Paid' }}</div>
            </div>
        </section>

        <table class="items">
            <thead>
                <tr><th>#</th><th>Product</th><th class="number">Unit price</th><th class="number">Qty</th><th class="number">Discount</th><th class="number">Line total</th></tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetail as $index => $line)
                    @php $lineTotal = ((float) $line->price * (int) $line->quantity) - (float) $line->discount; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $line->product->name ?? 'Product removed' }}</td>
                        <td class="number">{{ showAmount($line->price) }}</td>
                        <td class="number">{{ $line->quantity }}</td>
                        <td class="number">{{ showAmount($line->discount) }}</td>
                        <td class="number">{{ showAmount($lineTotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-area">
            <table class="summary">
                <tr><td>Subtotal</td><td>{{ showAmount($subtotal) }}</td></tr>
                @if ($totalDiscount > 0)
                    <tr><td>Discount</td><td>-{{ showAmount($totalDiscount) }}</td></tr>
                @endif
                <tr class="grand-total"><td>Total</td><td>{{ showAmount($order->total_amount) }}</td></tr>
                <tr><td>Items / quantity</td><td>{{ $order->orderDetail->count() }} / {{ $totalQty }}</td></tr>
            </table>
        </div>

        <section class="balance">
            <div><span>Total</span><strong>{{ showAmount($order->total_amount) }}</strong></div>
            <div><span>Paid</span><strong>{{ showAmount($paidAmount) }}</strong></div>
            <div><span>Due</span><strong>{{ showAmount($dueAmount) }}</strong></div>
        </section>

        @if ($order->deposits->isNotEmpty())
            <section class="history">
                <h3>Payment history</h3>
                <table>
                    <thead><tr><th>Date</th><th>Method</th><th>Status</th><th>Transaction</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($order->deposits->sortBy('created_at') as $payment)
                            <tr>
                                <td>{{ showDateTime($payment->updated_at, 'd M Y, h:i A') }}</td>
                                <td>{{ $payment->method_code == 0 ? 'Cash' : ($payment->gateway->name ?? 'Online') }}</td>
                                <td>{{ $payment->status == \App\Constants\Status::PAYMENT_SUCCESS ? 'Paid' : ($payment->status == \App\Constants\Status::PAYMENT_PENDING ? 'Due' : 'Other') }}</td>
                                <td>{{ $payment->trx }}</td>
                                <td>{{ showAmount($payment->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <footer class="footer">Thank you for your purchase.</footer>
    </main>
</body>
</html>
