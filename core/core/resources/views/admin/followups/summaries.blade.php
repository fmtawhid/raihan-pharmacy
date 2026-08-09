@extends('admin.layouts.app')

@push('style')
    <style>
        /* light green/red rows */
        tr.trend-up    { background:#e9f7ef; }
        tr.trend-down  { background:#fdecea; }
    </style>
@endpush

@section('panel')
<div class="card shadow-sm">
    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ $pageTitle }}</h5>

        <div class="d-flex flex-wrap gap-2">
            {{-- FILTERS --}}
            <form method="GET" class="d-flex gap-2">
                <input type="month" name="month" value="{{ request('month') }}" class="form-control form-control-sm w-auto">
                <select name="admin_id" class="form-select form-select-sm w-auto">
                    <option value="">All Admins</option>
                    @foreach ($admins as $admin)
                        <option value="{{ $admin->id }}" @selected(request('admin_id') == $admin->id)>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Filter</button>
            </form>

            {{-- EXPORT --}}
            <a href="{{ route('admin.followups.report',['download'=>1]) }}"
               class="btn btn-sm btn-outline-success">
               <i class="las la-file-excel"></i> Excel
            </a>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:110px;">Month</th>
                    <th>Employee</th>
                    <th class="text-end">Contacted</th>
                    <th class="text-end">Potential</th>
                    <th style="width:90px;">Δ</th>
                    <th class="text-center" style="width:70px;">Note</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($months as $row)
                @php
                    $prev = $prevMonths[$row->admin_id][$row->month] ?? null;   // passed from controller
                    $trendClass = '';
                    $delta = 0;
                    if ($prev){
                        $delta = $row->contacted_total - $prev;
                        $trendClass = $delta > 0 ? 'trend-up' : ($delta < 0 ? 'trend-down':'');
                    }
                @endphp

                <tr class="{{ $trendClass }}">
                    <td>{{ $row->month }}</td>
                    <td>{{ $row->admin->name }}</td>
                    <td class="text-end fw-semibold">{{ $row->contacted_total }}</td>
                    <td class="text-end">{{ $row->potential_total }}</td>
                    <td>
                        @if ($delta !== 0)
                            <span class="badge {{ $delta>0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $delta>0 ? '▲ '.$delta : '▼ '.abs($delta) }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#noteModal{{ $row->id }}">
                            <i class="las la-pen"></i>
                        </button>

                        {{-- MODAL --}}
                        <div class="modal fade" id="noteModal{{ $row->id }}" tabindex="-1">
                          <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content"
                                  action="{{ route('admin.followups.summaries.note.update', $row) }}"
                                  method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Note – {{ $row->admin->name }} ({{ $row->month }})
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <textarea name="summary_note" rows="5"
                                              class="form-control">{{ old('summary_note', $row->summary_note) }}</textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Save
                                    </button>
                                </div>
                            </form>
                          </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No snapshots yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="card-footer">{{ $months->withQueryString()->links() }}</div>
</div>
@endsection
