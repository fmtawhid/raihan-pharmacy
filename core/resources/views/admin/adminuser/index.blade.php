@extends('admin.layouts.app')
@php
    $admin = auth()->guard('admin')->user();
@endphp
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $page_title }}</h5>
                @if($admin->can('add_admin'))
                <a href="{{ route('admin.admins.create') }}" class="btn btn-sm btn-outline--primary">Create Admin</a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                                <tr>
                                    <td>{{ $admin->id }}</td>
                                    <td>{{ $admin->name }}</td>
                                    <td>{{ $admin->username }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>
                                        @if ($admin->can('view_admin'))
                                            <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn btn-sm btn-info">Details</a>
                                        
                                        @endif
                                        @if ($admin->id != 1 && $admin->can('edit_admin'))
                                            <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        @endif

                                        @if ($admin->id != 1 && $admin->can('delete_admin'))
                                            <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?')">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ $emptyMessage }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $admins->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
