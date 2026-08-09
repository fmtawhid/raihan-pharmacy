@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ $pageTitle }}</h5>
                    <a href="{{ route('admin.products.purchases.list') }}" class="btn btn-sm btn-secondary">
                        <i class="la la-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">@lang('Batch Information')</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">@lang('Batch Number'):</th>
                                    <td><strong class="text-primary">{{ $batch->batch_no }}</strong></td>
                                </tr>
                                <tr>
                                    <th>@lang('Purchase Date'):</th>
                                    <td>{{ showDateTime($batch->purchased_at, 'd M, Y') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="mb-3">@lang('Supplier Details')</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">@lang('Supplier'):</th>
                                    <td>
                                        @if($batch->purchaser)
                                            <a href="{{ route('admin.purchasers.edit', $batch->purchaser->id) }}">
                                                {{ $batch->purchaser->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">@lang('Products in This Batch')</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table--light">
                            <thead>
                                <tr>
                                    <th>@lang('Product Name')</th>
                                    <th>@lang('SKU')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Unit Price')</th>
                                    <th>@lang('Total Cost')</th>
                                    <th>@lang('Sold')</th>
                                    <th>@lang('Remaining')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batchItems as $item)
                                    <tr>
                                        <td>
                                            @if($item->product)
                                                <a href="{{ route('admin.products.edit', $item->product->id) }}">
                                                    {{ $item->product->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->product)
                                                <code>{{ $item->product->sku }}</code>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ $item->qty_received }}</strong>
                                        </td>
                                        <td>
                                            @if($item->purchase_price)
                                                {{ showAmount($item->purchase_price) }}
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->purchase_price)
                                                <strong>{{ showAmount($item->purchase_price * $item->qty_received) }}</strong>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-danger">{{ $item->qty_sold }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-info">{{ $item->qty_received - $item->qty_sold }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center text-muted">@lang('No products found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.products.add.stock') }}" class="btn btn-sm btn-primary">
            <i class="la la-plus"></i> @lang('Add Stock')
        </a>
        <a href="{{ route('admin.products.purchases.list') }}" class="btn btn-sm btn-info">
            <i class="la la-list"></i> @lang('All Batches')
        </a>
    </div>
@endpush
