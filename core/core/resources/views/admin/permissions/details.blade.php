@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5>{{ $page_title }}</h5>
                <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-primary float-end">Edit Admin</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td>{{ $admin->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $admin->name }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $admin->username }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $admin->email }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $admin->updated_at->format('d M, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
