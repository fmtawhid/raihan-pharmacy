@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $pageTitle }}</h5>
        <a href="{{ route('admin.branch-salesmen.create') }}" class="btn btn-sm btn-outline--primary">
            <i class="las la-user-plus"></i> Add Salesman
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table--light style--two mb-0">
                <thead>
                    <tr><th>Name</th><th>Username</th><th>Email</th><th>Joined</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($salesmen as $salesman)
                        <tr>
                            <td>{{ $salesman->name }}</td>
                            <td>{{ $salesman->username }}</td>
                            <td>{{ $salesman->email }}</td>
                            <td>{{ showDateTime($salesman->created_at, 'd M Y') }}</td>
                            <td>
                                <form action="{{ route('admin.branch-salesmen.destroy', $salesman) }}" method="POST" onsubmit="return confirm('Remove this salesman from your branch?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline--danger" title="Remove salesman" aria-label="Remove salesman">
                                        <i class="las la-user-minus"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No salesmen assigned to this branch.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $salesmen->links() }}</div>
</div>
@endsection