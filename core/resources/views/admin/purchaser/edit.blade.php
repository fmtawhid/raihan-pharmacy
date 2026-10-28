@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="page-title">@lang('Edit Supplier')</h6>
                    <a href="{{ route('admin.purchasers.index') }}" class="btn btn--sm btn--primary">
                        <i class="la la-arrow-left"></i> @lang('Back')
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.purchasers.update', $purchaser->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                {{-- Supplier Name --}}
                                <div class="mb-3">
                                    <label class="form-label required">@lang('Supplier Name')</label>
                                    <input type="text" name="name" value="{{ old('name', $purchaser->name) }}"
                                        class="form-control @error('name') is-invalid @enderror" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="mb-3">
                                    <label class="form-label">@lang('Email')</label>
                                    <input type="email" name="email" value="{{ old('email', $purchaser->email) }}"
                                        class="form-control @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div class="mb-3">
                                    <label class="form-label">@lang('Phone')</label>
                                    <input type="text" name="phone" value="{{ old('phone', $purchaser->phone) }}"
                                        class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Meta Info --}}
                                <div class="mb-3">
                                    <small class="text-muted d-block">
                                        <i class="la la-clock"></i> 
                                        @lang('Created on'): {{ $purchaser->created_at->format('d M Y H:i') }}
                                    </small>
                                    @if($purchaser->updated_at != $purchaser->created_at)
                                        <small class="text-muted d-block">
                                            <i class="la la-edit"></i> 
                                            @lang('Last updated'): {{ $purchaser->updated_at->format('d M Y H:i') }}
                                        </small>
                                    @endif
                                </div>

                                {{-- Buttons --}}
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn--primary">
                                        <i class="la la-save"></i> @lang('Update Supplier')
                                    </button>
                                    <a href="{{ route('admin.purchasers.index') }}" class="btn btn--outline-secondary">
                                        <i class="la la-times"></i> @lang('Cancel')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <script>
            (function() {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    setTimeout(() => alert.remove(), 3000);
                }
            })();
        </script>
    @endif
@endsection
