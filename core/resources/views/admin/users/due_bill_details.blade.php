@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Due Bill Details</h4>
            <span class="text-muted">{{ $user->fullname ?: $user->username }} · {{ $user->mobileNumber ?: 'No phone number' }}</span>
        </div>
        <div class="d-flex gap-2">
            @if($paidPaymentIds)
                <a href="{{ route('admin.users.due.bills.receipt', ['id' => $user->id, 'payments' => implode(',', $paidPaymentIds)]) }}" target="_blank" rel="noopener" class="btn btn--success">
                    <i class="las la-print"></i> Print Paid Receipt
                </a>
            @endif
            @if($dueAmount > 0)
                <a href="{{ route('admin.users.due.bills.print', $user->id) }}" target="_blank" rel="noopener" class="btn btn-outline--dark">
                    <i class="las la-file-invoice"></i> Print Due Bill
                </a>
            @endif
            <a href="{{ route('admin.users.due.bills') }}" class="btn btn-outline--dark">Back</a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Total outstanding</small>
                <h4 class="mt-2 mb-0">{{ showAmount($dueAmount) }}</h4>
            </div></div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted">Unpaid POS orders</small>
                <h4 class="mt-2 mb-0">{{ number_format($dueOrders->count()) }}</h4>
            </div></div>
        </div>
        @if($dueAmount > 0)
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card h-100"><div class="card-body">
                    <form method="POST" action="{{ route('admin.users.due.bills.pay', $user->id) }}">
                        @csrf
                        <label for="amount" class="small text-muted">Pay due amount</label>
                        <div class="input-group mt-2">
                            <input id="amount" type="number" name="amount" class="form-control" min="0.01" max="{{ number_format($dueAmount, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" required>
                            <button type="submit" class="btn btn--success">Pay Due</button>
                        </div>
                        <small class="text-muted">Maximum {{ showAmount($dueAmount) }}</small>
                        @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </form>
                </div></div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Outstanding POS Orders</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table--light style--two mb-0">
                    <thead><tr><th>Order</th><th>Date</th><th>Items</th><th class="text-end">Order total</th><th class="text-end">Due</th></tr></thead>
                    <tbody>
                        @forelse($dueOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ showDateTime($order->created_at, 'd M Y, H:i') }}</td>
                                <td>
                                    @foreach($order->orderDetail as $item)
                                        <div>{{ $item->product->name ?? 'Product removed' }} × {{ $item->quantity }}</div>
                                    @endforeach
                                </td>
                                <td class="text-end">{{ showAmount($order->total_amount) }}</td>
                                <td class="text-end fw-bold">{{ showAmount($order->due_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No outstanding POS bills found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection