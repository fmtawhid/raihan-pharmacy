@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ $pageTitle }}</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="q" class="form-control" placeholder="Search batch number or product" value="{{ request('q') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="purchaser_id" class="form-control">
                                    <option value="">All Suppliers</option>
                                    @foreach($purchasers as $purchaser)
                                        <option value="{{ $purchaser->id }}" {{ request('purchaser_id') == $purchaser->id ? 'selected' : '' }}>
                                            {{ $purchaser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="la la-search"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('admin.products.purchases.list') }}" class="btn btn-secondary btn-block" title="Reset">
                                    <i class="la la-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive-md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Batch No')</th>
                                <th>@lang('Supplier')</th>
                                <th>@lang('Item')</th>
                                <th>@lang('Total Amount')</th>
                                <th>@lang('Purchase Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($purchases as $batch)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary" style="background-color: #007bff; color: white; padding: 8px 12px; font-size: 14px; font-weight: bold;">
                                            {{ $batch->batch_no }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($batch->purchaser)
                                            <a href="{{ route('admin.purchasers.edit', $batch->purchaser->id) }}" title="Edit Supplier">
                                                {{ $batch->purchaser->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light" style="background-color: #f0f0f0; color: #333; padding: 5px 10px; font-weight: bold;">
                                            {{ $batch->product_count }} @choice('item|items', $batch->product_count)
                                        </span>
                                        <br>
                                        <small class="text-muted">Total: <strong>{{ $batch->total_qty_received }}</strong></small>
                                    </td>
                                    <td>
                                        @if($batch->total_purchase_amount)
                                            {{ showAmount($batch->total_purchase_amount) }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ showDateTime($batch->purchased_at, 'd M, Y') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.products.batch.details', $batch->id) }}" class="btn btn-sm btn-info" title="View Batch Details">
                                            <i class="la la-eye"></i> @lang('View')
                                        </a>
                                        <a href="{{ route('admin.products.batch.edit', $batch->id) }}" class="btn btn-sm btn-warning" title="Edit Batch">
                                            <i class="la la-edit"></i> @lang('Edit')
                                        </a>
                                        <form action="{{ route('admin.products.batch.delete', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this batch?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Batch">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No purchase records found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($purchases->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($purchases) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.products.add.stock') }}" class="btn btn-sm btn-primary">
            <i class="la la-plus"></i> @lang('Add Stock')
        </a>
        <a href="{{ route('admin.products.stock.log.list') }}" class="btn btn-sm btn-info">
            <i class="la la-list"></i> @lang('Product Stock')
        </a>
    </div>
@endpush
