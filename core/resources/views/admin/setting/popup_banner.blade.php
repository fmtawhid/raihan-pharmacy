@extends('admin.layouts.app')
@section('panel')
<div class="row mb-none-30">
    <div class="col-lg-12">
        <div class="card">
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Status')</label>
                                <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success"
                                    data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Enable')"
                                    data-off="@lang('Disabled')"
                                    @if(@$popup->data_values->status) checked @endif name="status">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" class="form-control" name="title"
                                    value="{{ @$popup->data_values->title }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Banner Image') <small class="text-muted">(@lang('Optional') - 600x400)</small></label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                @if(@$popup->data_values->image)
                                    <div class="mt-2">
                                        <img src="{{ getImage(getFilePath('popupBanner') . '/' . @$popup->data_values->image, getFileSize('popupBanner')) }}"
                                            alt="Popup Banner" class="img-thumbnail" style="max-height:150px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('Description')</label>
                        <textarea class="form-control" rows="4" name="description">{{ @$popup->data_values->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Button Text') <small class="text-muted">(@lang('Optional'))</small></label>
                                <input type="text" class="form-control" name="btn_text"
                                    value="{{ @$popup->data_values->btn_text }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Button URL') <small class="text-muted">(@lang('Optional'))</small></label>
                                <input type="text" class="form-control" name="btn_url"
                                    value="{{ @$popup->data_values->btn_url }}"
                                    placeholder="https://example.com/page">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
