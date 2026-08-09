@extends('admin.layouts.app')

@section('panel')
<div class="row g-4 mb-4">
    <!-- Dashboard Cards -->
    <div class="col-md-3">
        <div class="card b-radius--10 text-center">
            <div class="card-body">
                <h6>Total Deals</h6>
                <p class="fs-4 fw-bold">{{ $totalDeals }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card b-radius--10 text-center">
            <div class="card-body">
                <h6>Total Orders</h6>
                <p class="fs-4 fw-bold">{{ $totalOrders }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card b-radius--10 text-center">
            <div class="card-body">
                <h6>Product Bookings</h6>
                <p class="fs-4 fw-bold">{{ $totalProductBookings }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card b-radius--10 text-center">
            <div class="card-body">
                <h6>Total Payments</h6>
                <p class="fs-4 fw-bold">{{ showAmount($totalPayments) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card b-radius--10 text-center">
            <div class="card-body">
                <h6>Due Payments</h6>
                <p class="fs-4 fw-bold">{{ showAmount($remainingPayments) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Existing Deals Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body table-responsive">
                <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Title')</th>
                            <th>@lang('Category')</th>
                            <th>@lang('Deal Price')</th>
                            <th>@lang('Deal Status')</th>
                            <th>@lang('Stock Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deals as $deal)
                        <tr>
                            <td>{{ $deal->title }}</td>
                            <td>{{ $deal->category->name ?? '-' }}</td>
                            <td>{{ showAmount($deal->deal_price) }} ৳</td>
                            <td>
                                @if($deal->status=='active')
                                    <span class="badge bg-success">@lang('Active')</span>
                                @elseif($deal->status=='upcoming')
                                    <span class="badge bg-warning">@lang('Upcoming')</span>
                                @else
                                    <span class="badge bg-danger">@lang('Closed')</span>
                                @endif
                            </td>

                            <td>
                                @if($deal->stock_status=='ready')
                                    <span class="badge bg-success">Ready</span>
                                @elseif($deal->stock_status=='upcoming')
                                    <span class="badge bg-warning">Upcoming</span>
                                @else
                                    <span class="badge bg-danger">Sold Out</span>
                                @endif
                            </td>

                            <td class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.multi_express.deal.edit',$deal->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="las la-edit"></i> @lang('Edit')
                                </a>

                                <form action="{{ route('admin.multi_express.deal.delete',$deal->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="las la-trash"></i> @lang('Delete')
                                    </button>
                                </form>

                     
                                <a href="{{ route('admin.multi_express.deal.show',$deal->id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="las la-eye"></i> @lang('View Details')
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">@lang('No deals found')</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deals->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($deals) }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.multi_express.deal.create') }}" class="btn btn-sm btn-outline-primary">
    <i class="las la-plus"></i> @lang('Add New')
</a>
@endpush
