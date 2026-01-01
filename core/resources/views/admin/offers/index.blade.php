@extends('admin.layouts.app')
@php
    $admin = auth()->guard('admin')->user();
@endphp
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Discount Type')</th>
                                    <th>@lang('Total Products')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Expire Date')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offers as $offer)
                                    <tr>
                                        <td>{{ $offer->name }}</td>
                                        <td> @php echo $offer->discountTypeBadge() @endphp </td>
                                        <td>{{ $offer->total_products }}</td>
                                        <td>
                                            @if($admin->can('edit_offers'))
                                                <x-toggle-switch class="change_status" :checked="$offer->status" data-id="{{ $offer->id }}" />
                                            @else
                                                @if($offer->status)
                                                    <span class="badge bg-success">@lang('On')</span>
                                                @else
                                                    <span class="badge bg-danger">@lang('Off')</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td> {{ showDateTime($offer->ends_at, 'd M, Y') }} </td>
                                        @if($admin->can('edit_offers'))
                                        <td>
                                            <a href="{{ route('admin.offer.edit', $offer->id) }}" class="btn btn-outline--primary btn-sm">
                                                <i class="la la-pencil"></i>@lang('Edit')
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($offers->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($offers) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex gap-3">
        <x-search-form placeholder="Name" />
        @if($admin->can('add_offers'))
        <a href="{{ route('admin.offer.create') }}" class="btn btn-sm btn-outline--primary flex-shrink-0"> <i class="las la-plus"></i> @lang('Add New')</a>
        @endif
    </div>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {

            $('.change_status').on('change', function() {
                var id = $(this).data('id');

                var data = {
                    _token: `{{ csrf_token() }}`,
                    id: id
                };

                $.ajax({
                    url: "{{ route('admin.offer.status') }}",
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        notify(response.status, response.message);
                    }
                });
            });

        })(jQuery)
    </script>
@endpush
