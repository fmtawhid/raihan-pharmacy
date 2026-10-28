@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header">
        <h5>{{ $page_title }}</h5>
    </div>
    <div class="card-body">
        <p><strong>ID:</strong> {{ $role->id }}</p>
        <p><strong>Name:</strong> {{ $role->name }}</p>
        <p><strong>Guard:</strong> {{ $role->guard_name }}</p>
    </div>
</div>
@endsection
