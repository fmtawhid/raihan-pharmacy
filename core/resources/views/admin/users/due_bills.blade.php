@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
        <h6 class="page-title">@lang('Due Bills')</h6>
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                placeholder="@lang('Search name or phone')" aria-label="@lang('Search name or phone')">
            <button type="submit" class="btn btn--primary">
                <i class="las la-search"></i> @lang('Search')
            </button>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive--md table-responsive">
            <table class="table table--light style--two mb-0">
                <thead>
                    <tr>
                        <th>@lang('Customer')</th>
                        <th>@lang('Phone')</th>
                        <th>@lang('Due Amount')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $user->fullname ?: $user->username }}</span>
                                <br><small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>{{ $user->mobileNumber ?: '—' }}</td>
                            <td class="fw-bold">{{ showAmount($user->due_amount) }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('admin.users.due.bills.details', $user->id) }}" class="btn btn-sm btn-outline--dark">
                                        <i class="las la-eye"></i> @lang('Details')
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.due.bills.sms', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-sms"></i> @lang('Send SMS')
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.pos.payment.all', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline--success">
                                            <i class="las la-money-bill-wave"></i> @lang('Pay Full Due')
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">@lang('No due bills found.')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer py-3">
                {{ paginateLinks($users) }}
            </div>
        @endif
    </div>
@endsection

@if (session('payment_success'))
    @php
        $paymentSuccess = session('payment_success');
        $receiptUrl = route('admin.users.due.bills.receipt', [
            'id' => $paymentSuccess['user_id'],
            'payments' => $paymentSuccess['payment_ids'],
        ]);
        $receivedAmount = showAmount($paymentSuccess['amount']);
    @endphp
    @push('script')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const receiptUrl = @json($receiptUrl);
                const receivedAmount = @json($receivedAmount);

                Swal.fire({
                    title: 'Payment successful',
                    text: 'Received ' + receivedAmount,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="las la-print"></i> Print',
                    cancelButtonText: 'Done',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = receiptUrl;
                    }
                });
            });
        </script>
    @endpush
@endif