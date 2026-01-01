@extends('admin.layouts.app')
@php
    $admin = auth()->guard('admin')->user();
@endphp
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">{{ $page_title }}</h5>
                @if($admin->can('add_roles'))
                <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="la la-plus me-1"></i> Create Role
                </a>
                @endif
            </div>

            <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th style="width: 200px;">Name</th>
                            <th>Permissions</th>
                            <th class="text-end" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($role->permissions as $permission)
                                        <span class="badge bg-info text-dark">{{ $permission->name }}</span>
                                    @empty
                                        <span class="text-muted">No permissions</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-end">
                                @if ($admin->can('view_roles'))
                                    <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-sm btn-info me-1">
                                        View
                                    </a>
                                @endif
                                @if ($admin->can('edit_roles'))
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-primary me-1">
                                        Edit
                                    </a>
                                @endif
                                @if ($admin->can('delete_roles'))
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No roles found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
