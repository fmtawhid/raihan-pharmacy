@extends('admin.layouts.app')
@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($feature) ? route('admin.multi_express.feature.save',[$deal->id,$feature->id]) : route('admin.multi_express.feature.save',$deal->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" value="{{ $feature->title ?? old('title') }}" required>
                    </div>
                    <div class="form-group mt-2">
                        <label>@lang('Icon')</label>
                        <input type="text" name="icon" class="form-control" value="{{ $feature->icon ?? old('icon') }}">
                        <small>@lang('Optional, e.g., la la-star')</small>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-outline-primary w-100">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
