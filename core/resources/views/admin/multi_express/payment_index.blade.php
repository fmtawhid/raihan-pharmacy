@extends('admin.layouts.app')

@section('panel')
<div class="card b-radius--10">
    <div class="card-body">
        <h5 class="mb-3">{{ $pageTitle }}</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Payment Type</th>
                        <th>Status</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($payment->order)
                                    <a href="#">
                                        #{{ $payment->order_id }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $payment->user->name ?? $payment->user_id }}</td>
                            <td>{{ showAmount($payment->amount) }}</td>
                            <td>{{ ucfirst($payment->payment_type) }}</td>
                            <td>
                                <span class="badge {{ $payment->status=='paid' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->note ?? '-' }}</td>
                            <td>{{ showDateTime($payment->created_at, 'F d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
