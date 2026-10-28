@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ $pageTitle }}</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.branch-salesmen.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" class="form-control" value="{{ old('username') }}" required maxlength="100" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn--primary"><i class="las la-user-plus"></i> Create Salesman</button>
            <a href="{{ route('admin.branch-salesmen.index') }}" class="btn btn--dark">Cancel</a>
        </form>
    </div>
</div>
@endsection