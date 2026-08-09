@extends($activeTemplate . 'layouts.master')

@section('content')
    @php
        $content = getContent('profile_complete_page.content', true);
    @endphp


    <div class="py-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <div class="card custom--card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">{{ __(@$content->data_values->title) }}</h5>
                            <p class="bg-light p-3 rounded">{{ __(@$content->data_values->description) }}</p>
                            <form method="POST" action="{{ route('user.data.submit') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Username')</label>
                                            <input type="text" class="form-control form--control checkUser"
                                                name="username" value="{{ old('username') }}">
                                            <small class="text-danger usernameExist"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="display: none">
                                        <div class="form-group ">
                                            <label class="form-label">@lang('Country')</label>
                                            <select name="country" class="form-control form--control select2">
                                                @foreach ($countries as $key => $country)
                                                    <option data-mobile_code="{{ $country->dial_code }}"
                                                        value="{{ $country->country }}" data-code="{{ $key }}">
                                                        {{ __($country->country) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Mobile')</label>
                                            <div class="input-group">
                                                <span class="input-group-text mobile-code"></span>
                                                <input type="hidden" name="mobile_code">
                                                <input type="hidden" name="country_code">
                                                <input type="number" name="mobile" value="{{ old('mobile') }}"
                                                    class="form-control form--control checkUser ps-0" required>
                                            </div>
                                            <small class="text-danger mobileExist"></small>
                                        </div>
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label class="form-label">@lang('Division')</label>
                                        <select name="division_id" id="division" class="form-control form--control"
                                            required></select>
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label class="form-label">@lang('District')</label>
                                        <select name="district_id" id="district" class="form-control form--control"
                                            required></select>
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label class="form-label">@lang('Area')</label>
                                        <select name="area_name" id="area" class="form-control form--control"
                                            required></select>
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label class="form-label">@lang('Postcode')</label>
                                        <input type="text" name="postcode" id="postcode"
                                            class="form-control form--control" required readonly>
                                    </div <div class="form-group col-sm-6 d-none">
                                    <label class="form-label">@lang('State')</label>
                                    <input type="text" class="form-control form--control" name="state"
                                        value="{{ old('state') }}">
                                </div>
                                <div class="form-group col-sm-6 d-none">
                                    <label class="form-label">@lang('City')</label>
                                    <input type="text" class="form-control form--control" name="city"
                                        value="{{ old('city') }}">
                                </div>
                                <div class="form-group col-sm-6 d-none">
                                    <label class="form-label">@lang('Zip Code')</label>
                                    <input type="text" class="form-control form--control" name="zip"
                                        value="{{ old('zip') }}">
                                </div>

                                <div class="form-group col-sm-6 d-none">
                                    <label class="form-label">@lang('Address')</label>
                                    <input type="text" class="form-control form--control" name="address"
                                        value="{{ old('address') }}">
                                </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn--base h-45 w-100">
                                @lang('Submit')
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush


@push('script')
    <script>
        "use strict";
        (function($) {
            $('.select2-selection__rendered').removeAttr("data-bs-original-title");

            $.each($('.select2'), function() {
                $(this)
                    .wrap(`<div class="position-relative"></div>`)
                    .select2({
                        dropdownParent: $(this).parent(),
                    });
            });

            @if ($mobileCode)
                $('select[name=country]').val($(`option[data-code={{ $mobileCode }}]`).val()).select2({
                    dropdownParent: $('select[name=country]').parent()
                });
            @endif

            $('select[name=country]').on('change', function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
                var value = $('[name=mobile]').val();
                var name = 'mobile';
                checkUser(value, name);
            });

            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));


            $('.checkUser').on('focusout', function(e) {
                var value = $(this).val();
                var name = $(this).attr('name')
                checkUser(value, name);
            });

            function checkUser(value, name) {
                var url = '{{ route('user.checkUser') }}';
                var token = '{{ csrf_token() }}';

                if (name == 'mobile') {
                    var mobile = `${value}`;
                    var data = {
                        mobile: mobile,
                        mobile_code: $('.mobile-code').text().substr(1),
                        _token: token
                    }
                }
                if (name == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.field} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            }
        })(jQuery);
    </script>

    <script>
        (function($) {
            const divisions = @json(json_decode(file_get_contents(resource_path('data/bd-divisions.json')), true)['divisions']);
            const districts = @json(json_decode(file_get_contents(resource_path('data/bd-districts.json')), true)['districts']);
            const upazilas = @json(json_decode(file_get_contents(resource_path('data/bd-upazilas.json')), true)['upazilas']);
            const dhakaCity = @json(json_decode(file_get_contents(resource_path('data/dhaka-city.json')), true)['dhaka']);
            const postcodes = @json(json_decode(file_get_contents(resource_path('data/bd-postcodes.json')), true)['postcodes']);

            const selectedDivision = '{{ auth()->user()->division_id }}';
            const selectedDistrict = '{{ auth()->user()->district_id }}';
            const selectedArea = '{{ auth()->user()->area_name }}';
            const selectedPostcode = '{{ auth()->user()->postcode }}';

            function populateDropdown($select, items, labelKey = 'name', valueKey = 'id', selected = null) {
                $select.empty();
                $select.append(`<option value="">@lang('Select')</option>`);
                items.forEach(item => {
                    const isSelected = item[valueKey] == selected ? 'selected' : '';
                    $select.append(
                    `<option value="${item[valueKey]}" ${isSelected}>${item[labelKey]}</option>`);
                });
            }

            $('#division').on('change', function() {
                const divisionId = $(this).val();
                const filteredDistricts = districts.filter(d => d.division_id === divisionId);
                populateDropdown($('#district'), filteredDistricts, 'name', 'id');
                $('#area').empty();
                $('#postcode').val('');
            });

            $('#district').on('change', function() {
                const districtId = $(this).val();
                let areas;

                if (districtId === '1') {
                    areas = dhakaCity.map(a => ({
                        name: a.name
                    }));
                    areas.sort((a, b) => a.name.localeCompare(b.name));
                } else {
                    areas = upazilas.filter(u => u.district_id === districtId);
                    areas.sort((a, b) => a.name.localeCompare(b.name));
                }

                populateDropdown($('#area'), areas, 'name', 'name');
                $('#postcode').val('');
            });

            $('#area').on('change', function() {
                const area = $(this).val();
                const districtId = $('#district').val();

                const matches = postcodes.filter(p =>
                    p.district_id === districtId &&
                    (
                        p.upazila.toLowerCase() === area.toLowerCase() ||
                        p.postOffice.toLowerCase() === area.toLowerCase()
                    )
                );

                if (matches.length === 1) {
                    $('#postcode').val(matches[0].postCode).prop('readonly', true);
                } else if (matches.length > 1) {
                    let $select = $(
                        '<select name="postcode" id="postcode" class="form-control form--control" required></select>'
                        );
                    $select.append('<option value="">@lang('Select')</option>');
                    matches.forEach(m => $select.append(
                        `<option value="${m.postCode}">${m.postOffice} (${m.postCode})</option>`));
                    $('#postcode').replaceWith($select);
                } else {
                    $('#postcode')
                        .replaceWith(
                            '<input type="text" name="postcode" id="postcode" class="form-control form--control" required>'
                            )
                        .val('')
                        .prop('readonly', false);
                }
            });

            // Initial render
            populateDropdown($('#division'), divisions, 'name', 'id', selectedDivision);

            if (selectedDivision) {
                const filteredDistricts = districts.filter(d => d.division_id === selectedDivision);
                populateDropdown($('#district'), filteredDistricts, 'name', 'id', selectedDistrict);
            }

            if (selectedDistrict) {
                let areas;

                if (selectedDistrict === '1') {
                    areas = dhakaCity.map(a => ({
                        name: a.name
                    }));
                } else {
                    areas = upazilas.filter(u => u.district_id === selectedDistrict);
                }

                areas.sort((a, b) => a.name.localeCompare(b.name));
                populateDropdown($('#area'), areas, 'name', 'name', selectedArea);
            }

            if (selectedPostcode) {
                $('#postcode').val(selectedPostcode);
            }

        })(jQuery);
    </script>
@endpush
