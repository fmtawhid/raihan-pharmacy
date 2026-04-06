@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ot-border--10 box--shadow1">
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Order # or Product..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" placeholder="Start Date">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" placeholder="End Date">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-outline--primary w-100">
                                <i class="la la-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                @if(request('search') || request('start_date') || request('end_date'))
                <div class="mb-3">
                    <a href="{{ route('admin.order.returns.list') }}" class="btn btn-sm btn-outline--secondary">
                        <i class="la la-times"></i> Clear Filters
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="card b-radius--10 ot-border--10 box--shadow1">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light">
                        <thead>
                            <tr>
                                <th>@lang('Return ID')</th>
                                <th>@lang('Order Number')</th>
                                <th>@lang('Product Name')</th>
                                <th>@lang('Quantity Returned')</th>
                                <th>@lang('Refund Amount')</th>
                                <th>@lang('Return Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                            <tr>
                                <td><strong>#{{ $return->id }}</strong></td>
                                <td>
                                    <a href="{{ route('admin.order.details', $return->order->id) }}" class="text--primary">
                                        {{ optional($return->order)->order_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>{{ optional($return->product)->name ?? 'N/A' }}</td>
                                <td><span class="badge badge--primary">{{ $return->quantity_returned }}</span></td>
                                <td>
                                    <strong class="text--success">
                                        ৳{{ showAmount($return->refund_amount) }}
                                    </strong>
                                </td>
                                <td>{{ $return->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="button--group">
                                        <form method="POST" action="{{ route('admin.order.return.delete', $return->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline--danger" onclick="return confirm('Are you sure?')">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <strong>@lang('No returns found')</strong>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table><!-- table end -->
                </div>
            </div>
            @if($returns->hasPages())
                <div class="card-footer py-4">
                    {{ $returns->links($paginate_default ?? 'pagination::bootstrap-5') }}
                </div>
            @endif
        </div><!-- card end -->
    </div>
</div>
@endsection
