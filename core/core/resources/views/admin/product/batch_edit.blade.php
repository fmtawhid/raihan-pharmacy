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

                <form method="POST" action="{{ route('admin.products.batch.update', $batch->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="mb-3">@lang('Batch Information')</h6>
                                <div class="form-group">
                                    <label>@lang('Batch Number')</label>
                                    <input type="text" class="form-control" value="{{ $batch->batch_no }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Purchase Date')</label>
                                    <input type="text" class="form-control" value="{{ showDateTime($batch->purchased_at, 'd M, Y') }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="mb-3">@lang('Supplier Details')</h6>
                                <div class="form-group">
                                    <label for="purchaser_id">@lang('Supplier') <span class="text-danger">*</span></label>
                                    <select name="purchaser_id" id="purchaser_id" class="form-control @error('purchaser_id') is-invalid @enderror">
                                        <option value="">@lang('Select Supplier')</option>
                                        @foreach($purchasers as $purchaser)
                                            <option value="{{ $purchaser->id }}" {{ $batch->purchaser_id == $purchaser->id ? 'selected' : '' }}>
                                                {{ $purchaser->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('purchaser_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="purchase_price">@lang('Purchase Price (Per Item)') <span class="text-danger">*</span></label>
                                    <input type="number" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" 
                                           value="{{ $batch->purchase_price }}" min="0" step="0.01" required>
                                    @error('purchase_price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
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

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="la la-save"></i> @lang('Update Batch')
                        </button>
                        <a href="{{ route('admin.products.purchases.list') }}" class="btn btn-secondary">
                            <i class="la la-times"></i> @lang('Cancel')
                        </a>
                    </div>
                </form>
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
