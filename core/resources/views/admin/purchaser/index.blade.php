@extends('admin.layouts.app')

@section('panel')
    {{-- ───── Page header / breadcrumb ───── --}}
    <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
        <h6 class="page-title">{{ __('Suppliers / Purchasers') }}</h6>
        <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
            @stack('breadcrumb-plugins')
        </div>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="margin-bottom: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);">
            <i class="la la-check-circle" style="font-size: 1.5rem; margin-right: 1rem; color: #28a745;"></i>
            <div style="flex: 1;">
                <strong style="color: #155724; font-size: 1.05rem;">{{ __('Success!') }}</strong>
                <p style="margin: 0.25rem 0 0 0; color: #1e7e34; font-size: 0.95rem;">{{ session('success') }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ───── Filter row ───── --}}
    <form method="get" class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                placeholder="@lang('Search by name, email, or phone…')">
        </div>

        <div class="col-auto">
            <button class="btn btn--sm btn--primary">@lang('Filter')</button>
        </div>

        <div class="col-auto ms-auto">
            <button type="button" class="btn btn--sm btn--primary ms-2" data-bs-toggle="modal"
                data-bs-target="#purchaser-modal">
                <i class="la la-plus"></i> @lang('Add Supplier')
            </button>
        </div>
    </form>

    {{-- ───── Table ───── --}}
    <div class="table-responsive--md table-responsive table-sm">
        <table class="table table--light style--two">
            <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Phone')</th>
                    <th>@lang('Joined')</th>
                    <th>@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchasers as $purchaser)
                    <tr>
                        <td>#{{ $purchaser->id }}</td>
                        <td>{{ $purchaser->name }}</td>
                        <td>
                            @if($purchaser->email)
                                <a href="mailto:{{ $purchaser->email }}">{{ $purchaser->email }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($purchaser->phone)
                                <a href="tel:{{ $purchaser->phone }}">{{ $purchaser->phone }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $purchaser->created_at->format('d M Y') }}</small>
                        </td>
                        <td>
                            <div class="btn-group gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.purchasers.edit', $purchaser->id) }}" 
                                    class="btn btn--xs btn--info" 
                                    title="Edit">
                                    <i class="la la-edit"></i>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.purchasers.destroy', $purchaser->id) }}" 
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--xs btn--danger" 
                                        onclick="return confirm('@lang('Are you sure?')')"
                                        title="Delete">
                                        <i class="la la-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3">@lang('No suppliers found.')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- pagination --}}
    @if ($purchasers->hasPages())
        <div class="card-footer py-3">
            {{ paginateLinks($purchasers) }}
        </div>
    @endif

    {{-- ───── Modal (Bootstrap + Alpine) ───── --}}
    <div id="purchaser-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">@lang('Add Supplier')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="purchaser-form" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">

                    <div class="modal-body">
                        {{-- Supplier Name --}}
                        <div class="mb-3">
                            <label class="form-label required">@lang('Supplier Name')</label>
                            <input type="text" class="form-control" id="form-name" name="name" required>
                            <span class="invalid-feedback" id="error-name"></span>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">@lang('Email')</label>
                            <input type="email" class="form-control" id="form-email" name="email">
                            <span class="invalid-feedback" id="error-email"></span>
                        </div>

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label class="form-label">@lang('Phone')</label>
                            <input type="text" class="form-control" id="form-phone" name="phone">
                            <span class="invalid-feedback" id="error-phone"></span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn--secondary"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--primary">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function($) {
            'use strict';

            const modal = new bootstrap.Modal(document.querySelector('#purchaser-modal'));
            const modalEl = document.querySelector('#purchaser-modal');
            const form = document.querySelector('#purchaser-form');

            // Add Supplier button
            $(document).on('click', '[data-bs-target="#purchaser-modal"]', function(e) {
                e.preventDefault();
                $('#modal-title').text('{{ __('Add Supplier') }}');
                $('#form-method').val('POST');
                form.action = '{{ route('admin.purchasers.store') }}';
                $('#purchaser-form')[0].reset();
                $('#purchaser-form').removeClass('was-validated');
                $('#purchaser-form .form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                modal.show();
            });

            // Form submission
            $(form).on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const url = form.action;
                const method = $('#form-method').val();

                $.ajax({
                    url: url,
                    method: method === 'PUT' ? 'POST' : 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': method
                    },
                    success: function(response) {
                        modal.hide();
                        location.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            
                            $('#purchaser-form .form-control').removeClass('is-invalid');
                            $('.invalid-feedback').text('');

                            $.each(errors, function(field, messages) {
                                const input = $('#form-' + field);
                                input.addClass('is-invalid');
                                $('#error-' + field).text(messages[0]);
                            });
                        } else {
                            alert('{{ __('An error occurred. Please try again.') }}');
                        }
                    }
                });
            });

            // Clear form on modal closed
            $(modalEl).on('hidden.bs.modal', function() {
                $('#purchaser-form')[0].reset();
                $('#purchaser-form').removeClass('was-validated');
                $('#purchaser-form .form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            });

            // Auto-hide success alerts with animation
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.classList.add('fade-out');
                    setTimeout(function() {
                        successAlert.remove();
                    }, 300);
                }, 4000);
            }
        })(jQuery);
    </script>
@endsection

@push('style')
    <style>
        .table th,
        .table td {
            font-size: 0.8rem;
            padding: 0.5rem;
        }

        .btn.btn--xs {
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
        }

        /* ────────── Success Alert Professional Styling ────────── */
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #b1dfbb !important;
            border-left: 4px solid #28a745 !important;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            animation: slideInDown 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(10px);
        }

        .alert-success:hover {
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            transform: translateY(-1px);
            transition: all 0.3s ease;
        }

        .alert-success .la-check-circle {
            animation: checkBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .alert-success strong {
            color: #155724;
            font-weight: 700;
            display: block;
        }

        .alert-success p {
            color: #1e7e34;
            margin: 0.3rem 0 0 0 !important;
            font-weight: 500;
        }

        .alert-success .btn-close {
            opacity: 0.4;
            color: #155724 !important;
            transition: opacity 0.2s ease;
        }

        .alert-success .btn-close:hover {
            opacity: 0.7;
        }

        .alert-success .btn-close:focus {
            opacity: 1;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }

        /* ────────── Animations ────────── */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes checkBounce {
            0% {
                transform: scale(0) rotateZ(-45deg);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1) rotateZ(0deg);
            }
        }

        @keyframes slideOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .alert-success.fade-out {
            animation: slideOutUp 0.4s cubic-bezier(0.6, -0.28, 0.735, 0.045) forwards;
        }
    </style>
@endpush

@push('breadcrumb-plugins')
    <x-search-form />
@endpush
