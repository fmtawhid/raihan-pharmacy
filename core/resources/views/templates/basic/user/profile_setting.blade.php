@extends('Template::layouts.user')

@section('panel')
    <div class="row gy-4">
        {{-- Profile Card --}}
        <div class="col-12">
            <div class="card custom--card">
                <div class="card-body">
                    <div class="user-profile">
                        <div class="thumb">
                            <img id="imagePreview" src="{{ getImage(null) }}"
                                 data-src="{{ getAvatar(getFilePath('userProfile') . '/' . $user->image) }}"
                                 class="lazyload" alt="@lang('user')">
                            <label for="file-input" class="file-input-btn">
                                <i class="la la-edit"></i>
                            </label>
                        </div>
                        <div class="user-profile-content">
                            <h6 class="title">{{ $user->fullname }}</h6>
                            <p class="d-flex align-items-center gap-2 mb-0">
                                <span><i class="las la-user-alt"></i></span>
                                <span>{{ $user->username }}</span>
                            </p>
                            <ul class="user-profile__info">
                                @if ($user->email)
                                    <li><span class="icon"><i class="las la-envelope"></i></span><span class="text">{{ $user->email }}</span></li>
                                @endif
                                @if ($user->mobileNumber)
                                    <li><span class="icon"><i class="las la-phone"></i></span><span class="text">{{ $user->mobileNumber }}</span></li>
                                @endif
                                @if (@$user->country_name)
                                    <li><span class="icon"><i class="las la-globe"></i></span><span class="text">{{ $user->country_name }}</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Form --}}
        <div class="col-12">
            <div class="card custom--card">
                <div class="card-body">
                    <h5 class="title mb-3">@lang('Update Your Profile')</h5>
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="user-profile-form row">
                        @csrf
                        <input type="file" class="d-none" name="image" id="file-input" accept=".png, .jpg, .jpeg" />

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('First Name')</label>
                                <input class="form--control" type="text" name="firstname" value="{{ $user->firstname }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Last Name')</label>
                                <input class="form--control" type="text" name="lastname" value="{{ $user->lastname }}" required>
                            </div>
                        </div>

                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('State')</label>
                                <input class="form--control" type="text" name="state" value="{{ $user->state }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('City')</label>
                                <input class="form--control" type="text" name="city" value="{{ $user->city }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Zip')</label>
                                <input class="form--control" type="text" name="zip" value="{{ $user->zip }}">
                            </div>
                        </div> --}}

                        {{-- Division/District/Area/Postcode --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Division')</label>
                                <select name="division_id" id="division" class="form--control" required></select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('District')</label>
                                <select name="district_id" id="district" class="form--control" required></select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Area')</label>
                                <select name="area_name" id="area" class="form--control" required></select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Postcode')</label>
                                <input type="text" name="postcode" id="postcode" class="form--control" value="{{ $user->postcode }}" readonly required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Address')</label>
                                <textarea class="form--control" name="address" rows="3">{{ $user->address }}</textarea>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <button type="submit" class="btn btn--base h-45 w-100">@lang('Update Profile')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    'use strict';
    (function ($) {
        // Image preview
        $("#file-input").on('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#imagePreview').attr('src', e.target.result).hide().fadeIn(650);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // BD Location Logic
        const divisions = @json(json_decode(file_get_contents(resource_path('data/bd-divisions.json')), true)['divisions']);
        const districts = @json(json_decode(file_get_contents(resource_path('data/bd-districts.json')), true)['districts']);
        const upazilas = @json(json_decode(file_get_contents(resource_path('data/bd-upazilas.json')), true)['upazilas']);
        const dhakaCity = @json(json_decode(file_get_contents(resource_path('data/dhaka-city.json')), true)['dhaka']);
        const postcodes = @json(json_decode(file_get_contents(resource_path('data/bd-postcodes.json')), true)['postcodes']);

        const currentDivision = "{{ $user->division_id }}";
        const currentDistrict = "{{ $user->district_id }}";
        const currentArea = "{{ $user->area_name }}";

        function populateDropdown($select, items, labelKey = 'name', valueKey = 'id', selected = '') {
            $select.empty().append(`<option value="">@lang('Select')</option>`);
            items.forEach(item => {
                const value = item[valueKey];
                const label = item[labelKey];
                const isSelected = value == selected ? 'selected' : '';
                $select.append(`<option value="${value}" ${isSelected}>${label}</option>`);
            });
        }

        $('#division').on('change', function () {
            const divisionId = $(this).val();
            const filtered = districts.filter(d => d.division_id === divisionId);
            populateDropdown($('#district'), filtered, 'name', 'id');
            $('#area').empty(); $('#postcode').val('');
        });

        $('#district').on('change', function () {
            const districtId = $(this).val();
            let areas;
            if (districtId === '1') {
                areas = dhakaCity.map(a => ({ name: a.name }));
            } else {
                areas = upazilas.filter(u => u.district_id === districtId);
            }
            areas.sort((a, b) => a.name.localeCompare(b.name));
            populateDropdown($('#area'), areas, 'name', 'name');
            $('#postcode').val('');
        });

        $('#area').on('change', function () {
            const area = $(this).val();
            const districtId = $('#district').val();
            const matches = postcodes.filter(p =>
                p.district_id === districtId &&
                (p.upazila.toLowerCase() === area.toLowerCase() || p.postOffice.toLowerCase() === area.toLowerCase())
            );

            if (matches.length === 1) {
                $('#postcode').val(matches[0].postCode).prop('readonly', true);
            } else if (matches.length > 1) {
                const $select = $('<select name="postcode" id="postcode" class="form--control" required></select>');
                $select.append('<option value="">@lang("Select")</option>');
                matches.forEach(m =>
                    $select.append(`<option value="${m.postCode}">${m.postOffice} (${m.postCode})</option>`)
                );
                $('#postcode').replaceWith($select);
            } else {
                $('#postcode').replaceWith('<input type="text" name="postcode" id="postcode" class="form--control" required>').val('').prop('readonly', false);
            }
        });

        // Init on page load
        populateDropdown($('#division'), divisions, 'name', 'id', currentDivision);
        if (currentDivision) {
            const filteredDistricts = districts.filter(d => d.division_id === currentDivision);
            populateDropdown($('#district'), filteredDistricts, 'name', 'id', currentDistrict);
        }
        if (currentDistrict) {
            let areas;
            if (currentDistrict === '1') {
                areas = dhakaCity.map(a => ({ name: a.name }));
            } else {
                areas = upazilas.filter(u => u.district_id === currentDistrict);
            }
            areas.sort((a, b) => a.name.localeCompare(b.name));
            populateDropdown($('#area'), areas, 'name', 'name', currentArea);
        }
    })(jQuery);
</script>
@endpush
