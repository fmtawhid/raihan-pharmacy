@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body table-responsive">
                <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Title')</th>
                            <th>@lang('Icon')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($features as $feature)
                        <tr>
                            <td>{{ $feature->title }}</td>
                            <td>{{ $feature->icon }}</td>
                            <td>
                                <form action="{{ route('admin.multi_express.feature.delete',[$deal->id,$feature->id]) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">@lang('Delete')</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">@lang('No features added')</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <a href="{{ route('admin.multi_express.feature.create',$deal->id) }}" class="btn btn-outline-primary mt-3">@lang('Add New Feature')</a>
    </div>
</div>
@endsection
