@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Send SMS')</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">@lang('Messages are sent using the configured masking sender ID.')</p>
                    <form action="{{ route('admin.sms.panel.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>@lang('Mobile Number(s)')</label>
                            <textarea name="mobile" class="form-control" rows="3" placeholder="88017XXXXXXXX" required>{{ old('mobile') }}</textarea>
                            <small class="text-muted">@lang('Separate multiple numbers with commas or new lines')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('SMS Message')</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="@lang('Write your SMS message')" required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            <i class="las la-paper-plane"></i> @lang('Send SMS')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection