@extends('admin.layouts.app')

@section('panel')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Add Daily Follow-Up</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.followups.store') }}">
            @csrf

            <div class="row g-3">
                {{-- Date (defaults to today) --}}
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date"
                           name="contact_date"
                           value="{{ old('contact_date', now()->toDateString()) }}"
                           class="form-control @error('contact_date') is-invalid @enderror">
                    @error('contact_date') <small class="invalid-feedback">{{ $message }}</small> @enderror
                </div>

                {{-- Customers contacted --}}
                <div class="col-md-4">
                    <label class="form-label">Customers Contacted</label>
                    <input type="number"
                           name="customers_contacted"
                           value="{{ old('customers_contacted') }}"
                           min="0"
                           class="form-control @error('customers_contacted') is-invalid @enderror">
                    @error('customers_contacted') <small class="invalid-feedback">{{ $message }}</small> @enderror
                </div>

                {{-- Potential customers --}}
                <div class="col-md-4">
                    <label class="form-label">Potential Customers</label>
                    <input type="number"
                           name="potential_customers"
                           value="{{ old('potential_customers') }}"
                           min="0"
                           class="form-control @error('potential_customers') is-invalid @enderror">
                    @error('potential_customers') <small class="invalid-feedback">{{ $message }}</small> @enderror
                </div>

                {{-- Notes --}}
                <div class="col-12">
                    <label class="form-label">Notes / Daily Activities</label>
                    <textarea name="notes" rows="3"
                        class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <small class="invalid-feedback">{{ $message }}</small> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save Log</button>
        </form>
    </div>
</div>
@endsection
