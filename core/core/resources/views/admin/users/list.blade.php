@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Customer')</th>
                                    <th>@lang('Email-Mobile')</th>
                                    <th>@lang('Country')</th>
                                    <th>@lang('Orders')</th>
                                    <th>@lang('Total Shopping')</th>
                                    <th>@lang('Joined At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>

                                        <td>
                                            <span class="fw-bold">{{ $user->fullname }}</span>
                                            <br>
                                            <a class="text--small"
                                                href="{{ route('admin.users.detail', $user->id) }}">{{ $user->username }}</a>
                                        </td>

                                        <td>
                                            {{ $user->email }}<br>{{ $user->mobileNumber }}
                                        </td>

                                        <td>
                                            <span class="fw-bold"
                                                title="{{ @$user->country_name }}">{{ $user->country_code }}</span>
                                        </td>

                                        <td>{{ $user->orders_count }}</td>

                                        <td>{{ showAmount($user->orders_sum_subtotal) }}</td>

                                        <td>
                                            {{ showDateTime($user->created_at) }} <br>
                                            {{ diffForHumans($user->created_at) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.detail', $user->id) }}"
                                                class="btn btn-sm btn-outline--primary">
                                                <i class="las la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($users->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($users) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <form method="GET" id="filterForm" class="row gy-2 gx-3 align-items-end justify-content-end">

        {{-- Search --}}
        <div class="col-md-auto text-nowrap">
            <label for="search" class="form-label"><small>Username/Email</small></label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Username / Email">
        </div>

        {{-- Division --}}
        <div class="col-md-auto text-nowrap">
            <label for="division" class="form-label"><small>Divisions</small></label>
            <select name="division_id" id="division" class="form-select">
                <option value="">All Divisions</option>
            </select>
        </div>

        {{-- District --}}
        <div class="col-md-auto text-nowrap">
            <label for="district" class="form-label"><small>All Districts</small></label>
            <select name="district_id" id="district" class="form-select">
                <option value="">All Districts</option>
            </select>
        </div>

        {{-- Area --}}
        <div class="col-md-auto text-nowrap">
            <label for="area" class="form-label"><small>All Areas</small></label>
            <select name="area_name" id="area" class="form-select">
                <option value="">All Areas</option>
            </select>
        </div>

        {{-- Filter Button --}}
        <div class="col-md-auto">
            <button type="submit" class="btn btn--primary btn-sm w-100">
                <i class="la la-filter"></i> Filter
            </button>
        </div>

    </form>
@endpush


@push('style')
<style>
    select.form-select:hover {
        background-color: #f3f4f6 !important;
        border-color: #3b82f6 !important;
        cursor: pointer;
    }
</style>
@endpush
@push('script')
    <script>
        (function() {
            "use strict";

            // ---- raw JSON injected from controller ----
            const divisions = @json($bdData['divisions']);
            const districts = @json($bdData['districts']);
            const upazilas = @json($bdData['upazilas']);
            const dhakaCity = @json($bdData['dhaka']);

            // ---- cached dom refs ----
            const $division = $('#division');
            const $district = $('#district');
            const $area = $('#area');

            function populate($sel, items, label = 'name', value = 'id', selected = null) {
                $sel.empty().append('<option value="">All</option>');
                items.forEach(it => {
                    const val = it[value],
                        text = it[label];
                    const s = (selected && val == selected) ? 'selected' : '';
                    $sel.append(`<option value="${val}" ${s}>${text}</option>`);
                });
            }

            // --- division change ---
            $division.on('change', () => {
                const divId = $division.val();
                const filteredDistricts = districts.filter(d => d.division_id === divId);
                populate($district, filteredDistricts);
                $area.empty().append('<option value="">All Areas</option>');
            });

            // --- district change ---
            $district.on('change', () => {
                const disId = $district.val();
                let areas = [];

                if (disId === '1') { // Dhaka special-case
                    areas = dhakaCity.map(d => ({
                        name: d.name,
                        id: d.name
                    }));
                } else {
                    areas = upazilas
                        .filter(u => u.district_id === disId)
                        .map(u => ({
                            name: u.name,
                            id: u.name
                        }));
                }
                areas.sort((a, b) => a.name.localeCompare(b.name));
                populate($area, areas, 'name', 'id');
            });

            // --- initial render with request() values preserved ---
            populate($division, divisions, 'name', 'id', '{{ request('division_id') }}');

            if ('{{ request('division_id') }}') {
                $division.trigger('change');
                // after division filled, set district
                setTimeout(() => {
                    $district.val('{{ request('district_id') }}').trigger('change');
                    // after district filled, set area
                    setTimeout(() => {
                        $area.val('{{ request('area_name') }}');
                    }, 0);
                }, 0);
            }
        })();
    </script>
@endpush
