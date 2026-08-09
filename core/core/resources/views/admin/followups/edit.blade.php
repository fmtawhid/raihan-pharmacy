@extends('admin.layouts.app')

@section('panel')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ $pageTitle }}</h5>
        <a href="{{ route('admin.followups.index') }}" class="btn btn-sm btn-light">
            ← Back
        </a>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.followups.update', $log) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Contact Date</label>
                <input type="date" name="contact_date" value="{{ old('contact_date', $log->contact_date->format('Y-m-d')) }}"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Customers Contacted</label>
                <input type="number" name="customers_contacted" value="{{ old('customers_contacted', $log->customers_contacted) }}"
                       class="form-control" required min="0">
            </div>

            <div class="mb-3">
                <label class="form-label">Potential Customers</label>
                <input type="number" name="potential_customers" value="{{ old('potential_customers', $log->potential_customers) }}"
                       class="form-control" required min="0">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control">{{ old('notes', $log->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
@endsection
