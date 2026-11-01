@extends('admin.layouts.app')

@section('panel')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">@lang('Previous SMS')</h5>
        <a href="{{ route('admin.sms.panel.index') }}" class="btn btn-sm btn--primary">
            <i class="las la-paper-plane"></i> Send SMS
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.sms.panel.history') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="sms-search" class="form-label mb-1">Search number or message</label>
                    <input id="sms-search" type="search" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Phone number or message">
                </div>
                <div class="col-md-3">
                    <label for="sms-status" class="form-label mb-1">Status</label>
                    <select id="sms-status" name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sms-source" class="form-label mb-1">Source</label>
                    <select id="sms-source" name="source" class="form-select">
                        <option value="">All SMS</option>
                        <option value="sms_panel" @selected(request('source') === 'sms_panel')>SMS Panel</option>
                        <option value="due_bill" @selected(request('source') === 'due_bill')>Due Bills</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn--primary" aria-label="Filter SMS history">
                        <i class="las la-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table--light style--two mb-0">
                <thead>
                    <tr>
                        <th>Sent at</th>
                        <th>Phone number</th>
                        <th>Message</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ showDateTime($log->created_at, 'd M, Y H:i') }}</td>
                            <td class="text-nowrap">{{ $log->recipient_number }}</td>
                            <td style="min-width:260px;max-width:480px;white-space:normal">{{ $log->message }}</td>
                            <td>{{ $log->source === 'due_bill' ? 'Due Bills' : 'SMS Panel' }}</td>
                            <td>
                                <span class="badge {{ $log->status === 'sent' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td style="min-width:180px;white-space:normal">{{ $log->response_message ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No SMS history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer py-3">
                {{ paginateLinks($logs) }}
            </div>
        @endif
    </div>
@endsection
