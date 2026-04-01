@extends('Template::layouts.user')
@section('panel')

<div class="row justify-content-center">
    <div class="col-12">
        {{-- Upload Form --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="las la-file-medical me-2"></i>@lang('Upload Prescription')</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('user.prescription.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">@lang('Prescription File') <span class="text-danger">*</span></label>
                        <input type="file" name="prescription" class="form-control @error('prescription') is-invalid @enderror"
                            accept=".pdf,.jpeg,.jpg,.png,.webp">
                        <div class="form-text text-muted">@lang('Allowed formats: PDF, JPEG, JPG, PNG, WEBP — Max 5 MB')</div>
                        @error('prescription')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">@lang('Note') <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                            placeholder="@lang('Add any note about your prescription...')">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn--base">
                        <i class="las la-upload me-1"></i>@lang('Upload')
                    </button>
                </form>
            </div>
        </div>

        {{-- Uploaded Prescriptions List --}}
        @if ($prescriptions->count())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">@lang('My Prescriptions')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('File Name')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Note')</th>
                                <th>@lang('Uploaded At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prescriptions as $index => $rx)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $rx->original_name }}</td>
                                <td><span class="badge bg--info">{{ strtoupper($rx->file_extension) }}</span></td>
                                <td>{{ $rx->note ?? '—' }}</td>
                                <td>{{ $rx->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $rx->file_path) }}" target="_blank"
                                        class="btn btn-sm btn--base me-1" title="@lang('View')">
                                        <i class="las la-eye"></i>
                                    </a>
                                    <form action="{{ route('user.prescription.destroy', $rx->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('@lang('Are you sure you want to delete this prescription?')')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn--danger" title="@lang('Delete')">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="las la-file-medical-alt fs-1 text-muted"></i>
                <p class="mt-2 text-muted">@lang('No prescriptions uploaded yet.')</p>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
