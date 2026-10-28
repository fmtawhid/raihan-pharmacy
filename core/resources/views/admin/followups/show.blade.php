@extends('admin.layouts.app')

@section('panel')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ $pageTitle }}</h5>
        <a href="{{ route('admin.followups.index') }}" class="btn btn-sm btn-outline-primary">
            ← Back
        </a>
    </div>

    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Date</dt>
            <dd class="col-sm-9">{{ $log->contact_date->format('d-M-Y') }}</dd>

            <dt class="col-sm-3">Customers Contacted</dt>
            <dd class="col-sm-9">{{ $log->customers_contacted }}</dd>

            <dt class="col-sm-3">Potential Customers</dt>
            <dd class="col-sm-9">{{ $log->potential_customers }}</dd>

            <dt class="col-sm-3">Notes</dt>
            <dd class="col-sm-9">{{ $log->notes ?: '—' }}</dd>

            <dt class="col-sm-3">Entered By</dt>
            <dd class="col-sm-9">{{ $log->admin->name }} (ID #{{ $log->admin_id }})</dd>
        </dl>
    </div>
</div>
@endsection
