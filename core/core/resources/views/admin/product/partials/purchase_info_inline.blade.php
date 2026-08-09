{{-- resources/admin/product/partials/purchase_info_inline.blade.php --}}
<div class="card radius--10px mt-4" id="purchaseInfoCard">
    <div class="card-header">
        <h6 class="card-title mb-0">@lang('Purchase / Batch Info')</h6>
    </div>

    <div class="card-body">
        <div class="row gy-3">

            {{-- ── Batch No ─────────────────────────────────────────────── --}}
            <div class="col-md-6">
                <label class="form-label">@lang('Batch No')</label>
                <input name="batch_no" class="form-control" value="{{ old('batch_no', @$product->batch_no) }}">
            </div>

            {{-- ── Purchased From (Supplier) ──────────────────────────── --}}
            <div class="col-md-6">
                <label class="form-label">@lang('Purchased From')</label>

                @php
                    // Show only first 2 suppliers, excluding the "self" one (ID 1)
                    $visiblePurchasers = $purchasers->where('id', '!=', 1)->take(2);
                    $oldSupplier = old('purchaser_id', @$product->latestPurchase?->purchaser_id);
                @endphp

                <select name="purchaser_id" id="purchaserSelect" class="form-select select2" data-old="{{ old('purchaser_id', @$product->latestPurchase?->purchaser_id) }}">
                    <option value="">@lang('Select')</option>

                    {{-- ① Self / Manufacturer (submitted as "self") --}}
                    <option value="self" @selected($oldSupplier === 'self' || $oldSupplier == null)>
                        @lang('Self / Manufacturer')
                    </option>

                    {{-- ② First 2 real suppliers --}}
                    @foreach ($visiblePurchasers as $p)
                        <option value="{{ $p->id }}" @selected($oldSupplier == $p->id)>
                            {{ __($p->name) }}
                        </option>
                    @endforeach

                    {{-- ③ "Other" manual entry --}}
                    <option value="new" @selected($oldSupplier === 'new')>
                        @lang('Other (type below)')
                    </option>
                </select>
            </div>

            {{-- manual supplier input – shown only when “Other” is chosen --}}
            <div class="col-md-6 d-none" id="newPurchaserWrapper">
                <label class="form-label">@lang('New Purchaser Name')</label>
                <input name="new_purchaser" class="form-control" value="{{ old('new_purchaser') }}">
            </div>

            {{-- ── Purchase Price ─────────────────────────────────────── --}}
            <div class="col-md-6">
                <label class="form-label">@lang('Purchase Price (unit)')</label>
                <div class="input-group">
                    <span class="input-group-text">{{ gs('cur_sym') }}</span>
                    <input name="purchase_price" type="number" step="any" class="form-control"
                        value="{{ old('purchase_price', @$product->latestPurchase?->purchase_price) }}">
                </div>
            </div>

            {{-- ── Qty Received ───────────────────────────────────────── --}}
            <div class="col-md-6">
                <label class="form-label">@lang('Qty Received')</label>
                <input name="quantity" type="number" class="form-control"
                    value="{{ old('quantity', @$product->latestPurchase?->quantity) }}">
            </div>

            {{-- ── Purchase Date ───────────────────────────────────────── --}}
            <div class="col-md-6">
                <label class="form-label">@lang('Purchase Date')</label>
                <input name="purchased_at" type="date" class="form-control"
                    value="{{ old('purchased_at', now()->toDateString()) }}">
            </div>
        </div>
    </div>
</div>
@push('script')
<script>
$(document).ready(function () {
    const $select = $('#purchaserSelect');

    const fixedOptions = [
        { id: 'self', text: 'Self / Manufacturer' },
        { id: 'new', text: 'Other (type below)' }
    ];

    const oldValue = $select.data('old');

    $select.select2({
        placeholder: 'Select or search purchaser',
        ajax: {
            url: '{{ route("admin.purchasers.search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: fixedOptions.concat(data.results)
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true
    });

    // Inject selected option if needed (for old value)
    if (oldValue === 'self' || oldValue === 'new') {
        const selected = fixedOptions.find(o => o.id === oldValue);
        if (selected) {
            const option = new Option(selected.text, selected.id, true, true);
            $select.append(option).trigger('change');
        }
    }

    $select.on('change', function () {
        $('#newPurchaserWrapper').toggleClass('d-none', $(this).val() !== 'new');
    });
});
</script>
@endpush
