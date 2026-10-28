@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $pageTitle }}</h5>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-sm btn-outline--primary">Create Branch</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table--light style--two mb-0">
                <thead><tr><th>Name</th><th>Code</th><th>Managers</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->code }}</td>
                            <td>{{ $branch->admins_count }}</td>
                            <td>{{ $branch->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('admin.branches.show', $branch) }}" class="btn btn-sm btn-outline--primary">Details</a>
                                <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-primary">Edit</a>
                                @if($branch->code !== 'MAIN')
                                    <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this branch?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No branches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $branches->links() }}</div>
</div>
@endsection