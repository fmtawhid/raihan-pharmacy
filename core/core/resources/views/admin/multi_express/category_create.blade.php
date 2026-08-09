@extends('admin.layouts.app')
@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($category) ? route('admin.multi_express.category.save',$category->id) : route('admin.multi_express.category.save') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>@lang('Name')</label>
                        <input type="text" name="name" class="form-control" value="{{ $category->name ?? old('name') }}" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>@lang('Status')</label>
                        <select name="status" class="form-control">
                            <option value="active" @if(isset($category) && $category->status=='active') selected @endif>@lang('Active')</option>
                            <option value="inactive" @if(isset($category) && $category->status=='inactive') selected @endif>@lang('Inactive')</option>
                        </select>
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
