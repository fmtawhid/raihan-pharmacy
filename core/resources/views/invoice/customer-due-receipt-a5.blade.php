<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $user->fullname ?: $user->username }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A5 portrait; margin: 8mm; }
        body { margin: 0; background: #edf1f3; color: #1d292d; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.5; }
        .toolbar { width: 148mm; margin: 14px auto; text-align: right; }
        .toolbar button { border: 0; border-radius: 4px; padding: 9px 14px; background: #146c5b; color: #fff; font-size: 12px; cursor: pointer; }
        .sheet { position: relative; width: 148mm; min-height: 210mm; margin: 0 auto 20px; padding: 8mm; background: #fff; box-shadow: 0 3px 18px rgba(20, 43, 47, .12); overflow: hidden; }
        .header { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 13px; border-bottom: 2px solid #146c5b; }
        .store { margin: 0; color: #146c5b; font-size: 20px; line-height: 1.2; }
        .eyebrow { margin: 4px 0 0; color: #607176; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .meta { text-align: right; }
        .meta h2 { margin: 0 0 4px; font-size: 18px; }
        .muted { color: #607176; }
        .customer { padding: 15px 0; }
        .customer strong { display: block; font-size: 14px; }
        .paid-stamp { position: absolute; top: 67mm; right: 13mm; width: 34mm; height: 34mm; display: grid; place-items: center; border: 3px double #14744f; border-radius: 50%; color: #14744f; font-size: 18px; font-weight: 800; letter-spacing: 1px; transform: rotate(-14deg); opacity: .82; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { padding: 7px 5px; background: #edf4f2; color: #28433e; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 8px 5px; border-bottom: 1px solid #e5ebea; vertical-align: top; overflow-wrap: anywhere; }
        th:first-child { width: 40%; }
        th:nth-child(2) { width: 34%; }
        th:last-child { width: 26%; }
        .number { text-align: right; white-space: nowrap; }
        .total { display: flex; justify-content: space-between; margin-top: 16px; padding: 11px 0; border-top: 1px solid #9aadaa; font-size: 15px; font-weight: 700; }
        .balance { display: flex; justify-content: space-between; gap: 12px; padding: 11px; background: #f1f6f3; border: 1px solid #dce6e3; }
        .balance strong { display: block; margin-top: 3px; font-size: 14px; }
        .footer { margin-top: 32px; padding-top: 10px; border-top: 1px solid #dce6e3; color: #607176; text-align: center; font-size: 9px; }
        @media screen and (max-width: 600px) { .toolbar, .sheet { width: 100%; } .sheet { min-height: 0; padding: 18px; } }
        @media print { body { background: #fff; font-size: 10px; } .toolbar { display: none; } .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print A5 Receipt</button></div>
    <main class="sheet">
        <header class="header">
            <div>
                <h1 class="store">{{ gs('site_name') ?? 'Store' }}</h1>
                <p class="eyebrow">POS payment receipt</p>
            </div>
            <div class="meta">
                <h2>RECEIPT</h2>
                <div>{{ showDateTime(now(), 'd M Y, h:i A') }}</div>
            </div>
        </header>
        <div class="paid-stamp">PAID</div>
        <section class="customer">
            <strong>{{ $user->fullname ?: $user->username }}</strong>
            <div>{{ $user->mobileNumber ?: '—' }}</div>
        </section>
        <table>
            <thead><tr><th>POS order</th><th>Transaction</th><th class="number">Paid</th></tr></thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->order->order_number ?? 'POS order' }}</td>
                        <td>{{ $payment->trx }}</td>
                        <td class="number">{{ showAmount($payment->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total"><span>Amount received</span><span>{{ showAmount($paidAmount) }}</span></div>
        <div class="balance">
            <div><span class="muted">Remaining customer due</span><strong>{{ showAmount($dueAmount) }}</strong></div>
            <div><span class="muted">Payment status</span><strong>{{ $dueAmount > 0 ? 'Partially paid' : 'Fully paid' }}</strong></div>
        </div>
        <footer class="footer">Payment received. Thank you.</footer>
    </main>
</body>
</html>