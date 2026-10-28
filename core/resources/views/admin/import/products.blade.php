@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $pageTitle }}</h4>
                </div>

                <div class="card-body">
                    <!-- Info Box -->
                    <div class="alert alert-info" role="alert">
                        <h5>📄 Instructions for Importing Products</h5>
                        <ul class="mb-0 mt-3">
                            <li><strong>Download the template:</strong> Click the button below to download the sample Excel file</li>
                            <li><strong>Fill in your data:</strong> Use the columns: Sl, Product, Category, Brand, SKU, Purchase, Sell, Wholesale, Total Qty, Sold Qty, Remaining</li>
                            <li><strong>Format prices as:</strong> 9BDT, 12BDT, 0.72BDT (currency will be extracted)</li>
                            <li><strong>Format quantities as:</strong> 232 Pcs, 100 Pcs (numbers will be extracted)</li>
                            <li><strong>Upload the file:</strong> Select the completed Excel file and click Import</li>
                        </ul>
                    </div>

                    <!-- Download Template Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.import.template') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-download"></i> Download Excel Template
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <div class="card card-border-primary">
                        <div class="card-header">
                            <h5 class="mb-0">Upload Products Excel File</h5>
                        </div>
                        <div class="card-body">
                            <form id="importForm" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="file" class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                    <input type="file" 
                                           id="file" 
                                           name="file" 
                                           class="form-control" 
                                           accept=".xlsx,.xls,.csv" 
                                           required>
                                    <small class="form-text text-muted">
                                        Supported formats: xlsx, xls, csv (Max size: 5MB)
                                    </small>
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-upload"></i> Import Products
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Progress Area (Hidden by default) -->
                    <div id="progressArea" class="mt-4" style="display: none;">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="progressBar" 
                                 role="progressbar" 
                                 style="width: 0%">
                                0%
                            </div>
                        </div>
                        <p class="text-muted mt-2" id="progressText">Processing your file...</p>
                    </div>

                    <!-- Result Area (Hidden by default) -->
                    <div id="resultArea" class="mt-4" style="display: none;">
                        <div id="resultContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-border-primary {
        border-left: 4px solid #007bff;
    }
    
    .alert-info {
        border-radius: 0.25rem;
    }
</style>

<script>
$(document).ready(function() {
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        const fileInput = $('#file')[0];
        const file = fileInput.files[0];

        if (!file) {
            alert('Please select a file');
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Show progress
        $('#progressArea').show();
        $('#resultArea').hide();
        $('#submitBtn').prop('disabled', true);

        // Simulate progress
        let progress = 10;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 30;
                $('#progressBar').css('width', progress + '%').text(Math.round(progress) + '%');
            }
        }, 300);

        // Send request
        $.ajax({
            url: '{{ route("admin.import.products.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                clearInterval(progressInterval);
                $('#progressBar').css('width', '100%').text('100%');

                const resultHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5><i class="fas fa-check-circle"></i> ${response.message}</h5>
                        <hr>
                        <p><strong>Successfully Imported:</strong> ${response.success_count} products</p>
                        <p><strong>Rows Skipped:</strong> ${response.skip_count} rows</p>
                        ${response.errors.length > 0 ? `
                            <p><strong>Errors (${response.errors.length}):</strong></p>
                            <ul class="mb-0">
                                ${response.errors.map(err => `<li>${err}</li>`).join('')}
                            </ul>
                        ` : ''}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;

                $('#resultContent').html(resultHtml);
                $('#resultArea').show();
                $('#submitBtn').prop('disabled', false);
                $('#file').val(''); // Clear file input
            },
            error: function(xhr) {
                clearInterval(progressInterval);
                $('#progressBar').css('width', '100%').text('Error');

                let errorMessage = 'An error occurred during import';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                const resultHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="fas fa-times-circle"></i> Import Failed</h5>
                        <p>${errorMessage}</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;

                $('#resultContent').html(resultHtml);
                $('#resultArea').show();
                $('#submitBtn').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
