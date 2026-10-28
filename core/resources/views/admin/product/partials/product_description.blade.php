<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">@lang('Product Description')</h5>
    </div>
    <div class="card-body">
        <!-- Description Field -->
        <div class="form-group row mb-3">
            <div class="col-md-3">
                <label for="productDescription" class="form-label">@lang('Description')</label>
            </div>
            <div class="col-md-9">
                <textarea rows="5" class="form-control description-field" name="description" id="productDescription">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
        </div>

        <!-- Summary Field -->
        <!-- <div class="form-group row">
            <div class="col-md-3">
                <label for="productSummary" class="form-label">@lang('Summary')</label>
            </div>
            <div class="col-md-9">
                <textarea rows="5" class="form-control" name="summary" >{{ old('summary', $product->summary ?? '') }}</textarea>
            </div>
        </div> -->
        <div class="form-group row">
            <div class="col-md-3">
                <label for="productSummary" class="form-label">@lang('Summary')</label>
            </div>
            <div class="col-md-9">
                <textarea rows="5" class="form-control" name="summary" id="summaryField">{{ old('summary', $product->summary ?? '') }}</textarea>
            </div>
        </div>
        @push('script')
            <script src="https://unpkg.com/tinymce@5.3.0/tinymce.min.js"></script>
            <script>
                tinymce.init({
                    selector: '#summaryField', // TinyMCE will only apply to this
                    plugins: 'fontawesomepicker advlist autolink lists link image charmap print preview hr anchor pagebreak autoresize code codesample directionality fullscreen emoticons help quickbars table',
                    toolbar: 'fontawesomepicker forecolor backcolor fontsizeselect formatselect | bullist numlist | bold italic underline | link image | code',
                    fontsize_formats: '12px 14px 16px 18px 24px 36px',
                    external_plugins: {
                        fontawesomepicker: 'https://www.unpkg.com/tinymce-fontawesomepicker/fontawesomepicker/plugin.min.js'
                    },
                    fontawesomeUrl: 'https://www.unpkg.com/@fortawesome/fontawesome-free@5.14.0/css/all.min.css',
                    height: 300,
                    menubar: false
                });
            </script>
        @endpush


    </div>
</div>
