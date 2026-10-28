@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title mb-0">{{ $pageTitle }}</h5>
                    <span class="text-muted">Expired stock and batches expiring through {{ showDateTime($cutoff, 'd M, Y') }}</span>
                </div>
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <th>@lang('SKU / Variant')</th>
                                <th>@lang('Batch No')</th>
                                <th>@lang('Expiry Date')</th>
                                <th>@lang('Remaining Stock')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                @php
                                    $daysLeft = today()->diffInDays($batch->expiry_date, false);
                                    $remaining = max(0, $batch->qty_received - $batch->qty_sold);
                                @endphp
                                <tr>
                                    <td>{{ $batch->product?->name ?? '--' }}</td>
                                    <td>
                                        <code>{{ $batch->product?->sku ?? '--' }}</code>
                                        @if($batch->variant)
                                            <small class="d-block text-muted">{{ $batch->variant->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $batch->batch_no }}</td>
                                    <td>{{ $batch->expiry_date->format('d M, Y') }}</td>
                                    <td>{{ $remaining }}</td>
                                    <td>
                                        @if($daysLeft < 0)
                                            <span class="badge badge--danger">Expired {{ abs($daysLeft) }} @choice('day|days', abs($daysLeft)) ago</span>
                                        @elseif($daysLeft === 0)
                                            <span class="badge badge--warning">Expires today</span>
                                        @else
                                            <span class="badge badge--warning">{{ $daysLeft }} @choice('day|days', $daysLeft) left</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.products.batch.edit', $batch->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-edit"></i> @lang('Edit Batch')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">@lang('No expired or soon-to-expire stock found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($batches->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($batches) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.products.add.stock') }}" class="btn btn-sm btn-primary">
        <i class="las la-plus"></i> @lang('Add Stock')
    </a>
@endpush