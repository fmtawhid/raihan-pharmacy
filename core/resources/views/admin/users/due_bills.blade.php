@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
        <h6 class="page-title">@lang('Due Bills')</h6>
        <div class="d-flex flex-wrap gap-2">
            
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                    placeholder="@lang('Search name or phone')" aria-label="@lang('Search name or phone')">
                <button type="submit" class="btn btn--primary">
                    <i class="las la-search"></i> @lang('Search')
                </button>
            </form>
        </div>
    </div>

    @if($canAddDue)
        <div id="add-customer-due" class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Add Customer Due</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.due.bills.add') }}">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label for="due-user" class="form-label">Search customer</label>
                            <select id="due-user" name="user_id" class="form-control" required>
                                @if(old('user_id'))
                                    <option value="{{ old('user_id') }}" selected>{{ old('user_label', 'Selected customer') }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="user_label" id="due-user-label" value="{{ old('user_label') }}">
                            @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="due-amount" class="form-label">Amount</label>
                            <input id="due-amount" type="number" name="amount" class="form-control"
                                min="0.01" step="0.01" value="{{ old('amount') }}" required>
                            @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-3 col-md-8 mb-3">
                            <label for="due-description" class="form-label">Short description</label>
                            <input id="due-description" type="text" name="description" class="form-control"
                                maxlength="255" value="{{ old('description') }}" required>
                            @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-2 col-md-4 mb-3">
                            <button type="submit" class="btn btn--success w-100">
                                <i class="las la-plus"></i> Add Due
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
                                    <form method="POST" action="{{ route('admin.users.due.bills.pay', $user->id) }}">
                                        @csrf
                                        <input type="hidden" name="amount" value="{{ number_format($user->due_amount, 2, '.', '') }}">
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

        @if($canAddDue)
            @push('script')
                <script>
                    $(function () {
                        $('#due-user').select2({
                            width: '100%',
                            placeholder: 'Type customer name or phone',
                            minimumInputLength: 2,
                            ajax: {
                                url: @json(route('admin.users.due.bills.customers.search')),
                                dataType: 'json',
                                delay: 300,
                                data: function (params) {
                                    return { search: params.term, page: params.page || 1 };
                                },
                                processResults: function (response) {
                                    return response;
                                },
                                cache: true
                            }
                        });
                        $('#due-user').on('select2:select', function (event) {
                            $('#due-user-label').val(event.params.data.text);
                        });
                    });
                </script>
            @endpush
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