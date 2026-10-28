<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Customer Invoice - {{ $user->username }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A5 portrait; margin: 8mm; }
        body {
            margin: 0;
            background: #edf1f3;
            color: #1d292d;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.5;
        }
        .toolbar { width: 148mm; margin: 14px auto; text-align: right; }
        .toolbar button {
            border: 0;
            border-radius: 4px;
            padding: 9px 14px;
            background: #146c5b;
            color: #fff;
            font-size: 12px;
            cursor: pointer;
        }
        .invoice {
            position: relative;
            width: 148mm;
            min-height: 210mm;
            margin: 0 auto 20px;
            padding: 8mm;
            background: #fff;
            box-shadow: 0 3px 18px rgba(20, 43, 47, .12);
        }
        .invoice.is-paid::before { content: 'PAID'; position: fixed; z-index: 0; top: 40%; left: 16%; width: 68%; padding: 8px 0; border: 5px double rgba(20, 108, 91, .35); color: rgba(20, 108, 91, .24); text-align: center; font-size: 58px; font-weight: 800; letter-spacing: 3px; transform: rotate(-24deg); pointer-events: none; }
        .invoice > * { position: relative; z-index: 1; }
        .header { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 2px solid #146c5b; }
        .store-name { margin: 0; color: #146c5b; font-size: 20px; line-height: 1.2; }
        .eyebrow { margin: 4px 0 0; color: #607176; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { margin: 0 0 5px; font-size: 17px; }
        .muted { color: #607176; }
        .customer { padding: 13px 0; }
        .section-label { margin: 0 0 5px; color: #607176; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .customer-name { margin: 0 0 3px; font-size: 13px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        .items { table-layout: fixed; }
        .items th { padding: 6px 4px; background: #edf4f2; color: #28433e; text-align: left; font-size: 8px; text-transform: uppercase; }
        .items td { padding: 6px 4px; border-bottom: 1px solid #e5ebea; vertical-align: top; overflow-wrap: anywhere; }
        .items .numeric { text-align: right; white-space: nowrap; }
        .items .ref { width: 22mm; }
        .items .date { width: 17mm; }
        .items .product { width: auto; }
        .items .qty { width: 10mm; }
        .items .line-total { width: 24mm; }
        .totals { width: 200px; margin: 12px 0 12px auto; }
        .totals td { padding: 3px 0; }
        .totals td:last-child { text-align: right; white-space: nowrap; }
        .totals .grand-total td { padding-top: 6px; border-top: 1px solid #9aadaa; font-size: 12px; font-weight: 700; }
        .balance { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 12px 0 16px; }
        .balance > div { padding: 7px; border: 1px solid #dce6e3; border-radius: 4px; }
        .balance span { display: block; color: #607176; font-size: 8px; text-transform: uppercase; }
        .balance strong { display: block; margin-top: 2px; font-size: 12px; }
        .history { margin-top: 14px; }
        .history h3 { margin: 0 0 6px; font-size: 12px; }
        .history th, .history td { padding: 5px 4px; border-bottom: 1px solid #e5ebea; text-align: left; overflow-wrap: anywhere; }
        .history th { color: #607176; font-size: 8px; text-transform: uppercase; }
        .history .numeric { text-align: right; white-space: nowrap; }
        .footer { margin-top: 22px; padding-top: 9px; border-top: 1px solid #dce6e3; color: #607176; text-align: center; font-size: 8px; }
        @media screen and (max-width: 600px) {
            .toolbar, .invoice { width: 100%; }
            .invoice { min-height: 0; padding: 18px; overflow-x: auto; }
            .items, .history table { min-width: 520px; }
        }
        @media print {
            body { background: #fff; font-size: 8px; }
            .toolbar { display: none; }
            .invoice { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            tr { break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print A5 Invoice</button></div>
    <main class="invoice {{ $posDueAmount <= 0 ? 'is-paid' : '' }}">
        @php
            $subtotal = 0;
            $totalDiscount = 0;
            $totalQty = 0;
            $paymentHistory = collect();
            foreach ($posOrders as $posOrder) {
                foreach ($posOrder->orderDetail as $item) {
                    $subtotal += (float) $item->price * (int) $item->quantity;
                    $totalDiscount += (float) $item->discount;
                    $totalQty += (int) $item->quantity;
                }
                foreach ($posOrder->deposits as $payment) {
                    $paymentHistory->push($payment->setAttribute('pos_order_number', $posOrder->order_number));
                }
            }
        @endphp
        <header class="header">
            <div>
                <h1 class="store-name">{{ gs('site_name') ?? 'Store' }}</h1>
                <p class="eyebrow">{{ $isFilteredInvoice ? 'Selected POS customer invoice' : 'Consolidated POS customer invoice' }}</p>
            </div>
            <div class="invoice-meta">
                <h2>INVOICE</h2>
                <div><strong>POS-CUST-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
                <div class="muted">{{ showDateTime(now(), 'd M Y, h:i A') }}</div>
                <div class="muted">{{ $posOrders->count() }} {{ $isFilteredInvoice ? 'selected ' : '' }}POS transactions</div>
            </div>
        </header>

        <section class="customer">
            <p class="section-label">Customer</p>
            <p class="customer-name">{{ $user->fullname }}</p>
            <div>{{ $user->username }} · {{ $user->email }}</div>
            <div>{{ $user->mobileNumber }}</div>
            @if ($user->address)<div>{{ $user->address }}</div>@endif
        </section>

        <table class="items">
            <thead>
                <tr>
                    <th class="ref">POS reference</th>
                    <th class="date">Date</th>
                    <th class="product">Product</th>
                    <th class="qty numeric">Qty</th>
                    <th class="line-total numeric">Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posOrders as $posOrder)
                    @foreach ($posOrder->orderDetail as $item)
                        @php $lineTotal = ((float) $item->price * (int) $item->quantity) - (float) $item->discount; @endphp
                        <tr>
                            <td>{{ $posOrder->order_number }}</td>
                            <td>{{ showDateTime($posOrder->created_at, 'd M Y') }}</td>
                            <td>
                                <div>{{ $item->product->name ?? 'Product removed' }}</div>
                                <small class="muted">Unit {{ showAmount($item->price) }} · Discount {{ showAmount($item->discount) }}</small>
                            </td>
                            <td class="numeric">{{ $item->quantity }}</td>
                            <td class="numeric">{{ showAmount($lineTotal) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td>{{ showAmount($subtotal) }}</td></tr>
            @if ($totalDiscount > 0)
                <tr><td>Discount</td><td>-{{ showAmount($totalDiscount) }}</td></tr>
            @endif
            <tr class="grand-total"><td>Grand total</td><td>{{ showAmount($posTotalAmount) }}</td></tr>
            <tr><td>Total items / quantity</td><td>{{ $posOrders->sum(fn ($order) => $order->orderDetail->count()) }} / {{ $totalQty }}</td></tr>
        </table>

        <section class="balance">
            <div><span>Total</span><strong>{{ showAmount($posTotalAmount) }}</strong></div>
            <div><span>Paid</span><strong>{{ showAmount($posPaidAmount) }}</strong></div>
            <div><span>Due</span><strong>{{ showAmount($posDueAmount) }}</strong></div>
        </section>



        <footer class="footer">
            This invoice consolidates {{ $isFilteredInvoice ? 'the selected' : 'all' }} POS transactions for this customer.
        </footer>
    </main>
</body>
</html>
