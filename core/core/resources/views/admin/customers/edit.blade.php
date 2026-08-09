@extends('admin.layouts.app')

@section('panel')
    @php
        $bd = getBangladeshLocationData();
        $postcodes = json_decode(file_get_contents(resource_path('data/bd-postcodes.json')), true)['postcodes'];
    @endphp

    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="page-title">@lang('Edit Customer')</h6>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn--sm btn--primary">
                        <i class="la la-arrow-left"></i> @lang('Back')
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">@lang('Customer Name')</label>
                                <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">@lang('Company')</label>
                                <input type="text" name="company" value="{{ old('company', $customer->company) }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">@lang('Contact Number')</label>
                                <input type="text" name="contact_number"
                                    value="{{ old('contact_number', $customer->contact_number) }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">@lang('Email')</label>
                                <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                                    class="form-control">
                            </div>

                            {{-- Location --}}
                            <div class="col-md-3">
                                <label class="form-label">@lang('Division')</label>
                                <select id="divisionSelect" name="division_id" class="form-select" required></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">@lang('District')</label>
                                <select id="districtSelect" name="district_id" class="form-select" required></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">@lang('Area')</label>
                                <select id="thanaSelect" name="area_name" class="form-select" required></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">@lang('Postcode')</label>
                                <input type="text" id="postcode" name="postcode"
                                    value="{{ old('postcode', $customer->postcode) }}" class="form-control" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">@lang('Remarks')</label>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $customer->remarks) }}</textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn--primary w-100">@lang('Update Customer')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BD_LOC = @json($bd);
        const POSTCODES = @json($postcodes);
        const selectedDivision = "{{ $customer->division_id }}";
        const selectedDistrict = "{{ $customer->district_id }}";
        const selectedArea = "{{ $customer->area_name }}";
    </script>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            const $div = $('#divisionSelect');
            const $dist = $('#districtSelect');
            const $thana = $('#thanaSelect');
            const $postcode = $('#postcode');

            const option = (v, t) => `<option value="${v}">${t}</option>`;
            const empty = '<option value="">—</option>';

            $.each(BD_LOC.divisions, (_, d) => $div.append(option(d.id, d.name)));

            $div.on('change', function() {
                const id = +this.value || 0;
                $dist.html(empty);
                $thana.html(empty);
                if (!id) return;

                BD_LOC.districts.filter(d => +d.division_id === id)
                    .forEach(d => $dist.append(option(d.id, d.name)));
            });

            $dist.on('change', function() {
                const id = +this.value || 0;
                $thana.html(empty);
                if (!id) return;

                const areas = id === 1 ?
                    BD_LOC.dhaka.map(a => ({
                        value: a.name,
                        name: a.name
                    })) :
                    BD_LOC.upazilas.filter(u => +u.district_id === id).map(u => ({
                        value: u.name,
                        name: u.name
                    }));

                areas.sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(a => $thana.append(option(a.value, a.name)));
            });

            $thana.on('change', function() {
                const area = $(this).val();
                const did = $dist.val();

                const hits = POSTCODES.filter(p =>
                    +p.district_id === +did &&
                    (p.upazila.toLowerCase() === area.toLowerCase() || p.postOffice.toLowerCase() === area
                        .toLowerCase())
                );

                if (hits.length === 1) {
                    $postcode.replaceWith(
                        `<input type="text" id="postcode" name="postcode" class="form-control" value="${hits[0].postCode}" readonly>`
                        );
                } else if (hits.length > 1) {
                    const $sel = $('<select id="postcode" name="postcode" class="form-select" required>');
                    $sel.append('<option value="">—</option>');
                    hits.forEach(p => $sel.append(
                        `<option value="${p.postCode}">${p.postOffice} (${p.postCode})</option>`));
                    $postcode.replaceWith($sel);
                } else {
                    $postcode.replaceWith(
                        `<input type="text" id="postcode" name="postcode" class="form-control" required>`);
                }
            });

            // Pre-select
            $div.val(selectedDivision).trigger('change');
            setTimeout(() => {
                $dist.val(selectedDistrict).trigger('change');
                setTimeout(() => $thana.val(selectedArea).trigger('change'), 50);
            }, 50);

        })(jQuery);
    </script>
@endpush
