@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
        <h6 class="page-title">@lang('Customer Detail')</h6>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn--sm btn--primary">
                <i class="la la-arrow-left"></i> @lang('Back to List')
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">{{ $customer->name }} 
                <small class="text-muted">({{ $customer->company }})</small>
            </h5>

            <div class="row gy-3">
                <div class="col-md-6">
                    <strong>@lang('Contact Number'):</strong> {{ $customer->contact_number }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('Email'):</strong> {{ $customer->email ?? '—' }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('Division'):</strong> 
                    {{ $divName[$customer->division_id] ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('District'):</strong> 
                    {{ $disName[$customer->district_id] ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('Area'):</strong> 
                    {{ is_numeric($customer->area_name) ? $upaName[$customer->area_name] ?? '-' : $customer->area_name }}
                </div>
                <div class="col-md-6">
                    <strong>@lang('Postcode'):</strong> {{ $customer->postcode ?? '-' }}
                </div>
                <div class="col-md-12">
                    <strong>@lang('Remarks'):</strong><br>
                    <p>{{ $customer->remarks ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
