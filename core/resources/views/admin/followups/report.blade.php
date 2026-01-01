@extends('admin.layouts.app')

@section('panel')
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ $pageTitle }}</h5>
        <a href="{{ route('admin.followups.report', ['download' => 1]) }}"
           class="btn btn-sm btn-success">
            Export Excel
        </a>
    </div>
    <div class="card-body">
        <p>Total contacted: <strong>{{ $stats->contacted }}</strong></p>
        <p>Total potential: <strong>{{ $stats->potential }}</strong></p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h6 class="mb-0">Per-Employee Breakdown</h6></div>
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Contacted</th>
                    <th>Potential</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summaries as $row)
                    <tr>
                        <td>{{ $row->admin->name }}</td>
                        <td>{{ $row->contacted }}</td>
                        <td>{{ $row->potential }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
