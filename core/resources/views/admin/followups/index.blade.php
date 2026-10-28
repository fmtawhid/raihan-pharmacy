@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daily Follow-Up Logs</h5>
        <a href="{{ route('admin.followups.create') }}" class="btn btn-sm btn-primary">
            + Add New
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contacted</th>
                        <th>Potential</th>
                        <th>Notes</th>
                        <th>Entered By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->contact_date->format('d-M-Y') }}</td>
                            <td>{{ $log->customers_contacted }}</td>
                            <td>{{ $log->potential_customers }}</td>
                            <td class="text-truncate" style="max-width:260px;" title="{{ $log->notes }}">
                                {{ Str::limit($log->notes, 50) }}
                            </td>
                            <td>{{ $log->admin->name }}</td>
                            {{-- VIEW button --}}
                            <td class="text-end">
                                <a href="{{ route('admin.followups.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                                <a href="{{ route('admin.followups.edit', $log) }}" class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.followups.destroy', $log) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this follow-up log?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
