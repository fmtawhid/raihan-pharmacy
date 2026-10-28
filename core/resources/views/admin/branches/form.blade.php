@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header"><h5>{{ $pageTitle }}</h5></div>
    <div class="card-body">
        <form action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}" method="POST">
            @csrf
            @if($branch->exists) @method('PUT') @endif
            <div class="form-group">
                <label for="name">Branch name</label>
                <input id="name" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="code">Branch code</label>
                <input id="code" name="code" class="form-control" value="{{ old('code', $branch->code) }}" required maxlength="32">
            </div>
            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" @selected((string) old('is_active', (int) $branch->is_active) === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', (int) $branch->is_active) === '0')>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="allow_backorder_sales">POS stock control</label>
                <select id="allow_backorder_sales" name="allow_backorder_sales" class="form-control">
                    <option value="0" @selected((string) old('allow_backorder_sales', (int) $branch->allow_backorder_sales) === '0')>Require available branch stock</option>
                    <option value="1" @selected((string) old('allow_backorder_sales', (int) $branch->allow_backorder_sales) === '1')>Allow POS sales without available stock</option>
                </select>
                <small class="text-muted">This setting applies only to POS sales in this branch.</small>
            </div>
            <button type="submit" class="btn btn--primary">Save</button>
            <a href="{{ route('admin.branches.index') }}" class="btn btn--dark">Cancel</a>
        </form>
    </div>
</div>
@endsection