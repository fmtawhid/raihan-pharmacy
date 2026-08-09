@extends('admin.layouts.app')

@section('panel')
<div class="container">
    <div class="card">
        <div class="card-header"><h5>@lang('Add Payment')</h5></div>
        <div class="card-body">
            @php
                $totalPrice = $order->total_price;
                $paidAmount = $order->totalPaid() ?? 0;
            @endphp

            @if($paidAmount >= $totalPrice)
                <!-- Already Fully Paid -->
                <div class="alert alert-success text-center">
                    <h5>@lang('Already Fully Paid')</h5><br>
                    <p>@lang('Total Amount'): {{ showAmount($totalPrice) }}</p><br>
                    <p>@lang('Paid Amount'): {{ showAmount($paidAmount) }}</p>
                </div>
                <div class="text-end">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">@lang('Back')</a>
                </div>
            @else
                <!-- Payment Form -->
                <form action="{{ route('admin.admin.multi_express.order.payment.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div class="mb-3">
                        <label>@lang('Total Amount')</label>
                        <input type="text" class="form-control" value="{{ showAmount($totalPrice) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label>@lang('Already Paid')</label>
                        <input type="text" class="form-control" value="{{ showAmount($paidAmount) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label>@lang('Payment Amount')</label>
                        <input type="number" step="0.01" max="{{ $totalPrice - $paidAmount }}" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>@lang('Payment Type')</label>
                        <select name="payment_type" class="form-select">
                            <option value="full">@lang('Full')</option>
                            <option value="partial">@lang('Partial')</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>@lang('Note (optional)')</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">@lang('Add Payment')</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">@lang('Cancel')</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
