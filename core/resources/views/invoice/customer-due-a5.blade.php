<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Bill - {{ $user->fullname ?: $user->username }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A5 portrait; margin: 8mm; }
        body { margin: 0; background: #edf1f3; color: #1d292d; font-family: Arial, Helvetica, sans-serif; font-size: 10px; line-height: 1.45; }
        .toolbar { width: 148mm; margin: 14px auto; text-align: right; }
        .toolbar button { border: 0; border-radius: 4px; padding: 9px 14px; background: #146c5b; color: #fff; font-size: 12px; cursor: pointer; }
        .sheet { width: 148mm; min-height: 210mm; margin: 0 auto 20px; padding: 8mm; background: #fff; box-shadow: 0 3px 18px rgba(20, 43, 47, .12); }
        .header { display: flex; justify-content: space-between; gap: 10px; padding-bottom: 12px; border-bottom: 2px solid #146c5b; }
        .store { margin: 0; color: #146c5b; font-size: 19px; line-height: 1.2; }
        .label { margin: 4px 0 0; color: #607176; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .meta { text-align: right; }
        .meta h2 { margin: 0 0 4px; font-size: 17px; }
        .muted { color: #607176; }
        .customer { padding: 13px 0; }
        .customer strong { display: block; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { padding: 6px 4px; background: #edf4f2; color: #28433e; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 6px 4px; border-bottom: 1px solid #e5ebea; vertical-align: top; overflow-wrap: anywhere; }
        th:nth-child(1) { width: 23%; }
        th:nth-child(2) { width: 18%; }
        th:nth-child(3) { width: 30%; }
        th:nth-child(4), th:nth-child(5) { width: 14.5%; }
        .number { text-align: right; white-space: nowrap; }
        .items { color: #46585b; }
        .total { display: flex; justify-content: space-between; gap: 10px; margin-top: 14px; padding: 10px 0; border-top: 1px solid #9aadaa; font-size: 14px; font-weight: 700; }
        .footer { margin-top: 28px; padding-top: 9px; border-top: 1px solid #dce6e3; color: #607176; text-align: center; font-size: 9px; }
        @media screen and (max-width: 600px) { .toolbar, .sheet { width: 100%; } .sheet { min-height: 0; padding: 18px; overflow-x: auto; } table { min-width: 540px; } }
        @media print { body { background: #fff; font-size: 9px; } .toolbar { display: none; } .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; } tr { break-inside: avoid; } thead { display: table-header-group; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print A5 Due Bill</button></div>
    <main class="sheet">
        <header class="header">
            <div>
                <h1 class="store">{{ gs('site_name') ?? 'Store' }}</h1>
                <p class="label">Customer due statement</p>
            </div>
            <div class="meta">
                <h2>DUE BILL</h2>
                <div class="muted">{{ showDateTime(now(), 'd M Y, h:i A') }}</div>
            </div>
        </header>
        <section class="customer">
            <strong>{{ $user->fullname ?: $user->username }}</strong>
            <div>{{ $user->mobileNumber ?: '—' }}</div>
            @if($user->address)<div>{{ $user->address }}</div>@endif
        </section>
        <table>
            <thead><tr><th>POS order</th><th>Date</th><th>Items</th><th class="number">Total</th><th class="number">Due</th></tr></thead>
            <tbody>
                @foreach($dueOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ showDateTime($order->created_at, 'd M Y') }}</td>
                        <td class="items">
                            @foreach($order->orderDetail as $item)
                                <div>{{ $item->product->name ?? 'Product removed' }} × {{ $item->quantity }}</div>
                            @endforeach
                        </td>
                        <td class="number">{{ showAmount($order->total_amount) }}</td>
                        <td class="number">{{ showAmount($order->due_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total"><span>Total Due</span><span>{{ showAmount($dueAmount) }}</span></div>
        <footer class="footer">Please contact the store to settle the outstanding balance.</footer>
    </main>
</body>
</html>