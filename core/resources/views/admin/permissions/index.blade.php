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
                @if($admin->can('add_permissions'))
                <form method="POST" action="{{ route('admin.permissions.store') }}" class="d-flex">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Permission name" required>
                    <button type="submit" class="btn btn-sm btn-outline--primary ms-2">Add</button>
                </form>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Permission</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->id }}</td>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    @if($admin->can('edit_permissions'))
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-outline--primary"><i class="la la-pencil"></i> Edit</a>
                                    @endif
                                    @if($admin->can('delete_permissions'))
                                        <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this permission?')">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">{{ $emptyMessage }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
