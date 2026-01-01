@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">

                {{-- Data Holder for edit mode --}}
                <div id="multiExpressData"
                    data-tiers='@json($deal->pricingTiers()->get()->toArray() ?? [])'
                    data-delivery='@json($deal->deliveryOptions()->get()->toArray() ?? [])'>
                </div>


                <form action="{{ isset($deal) ? route('admin.multi_express.deal.save',$deal->id) : route('admin.multi_express.deal.save') }}" method="POST" enctype="multipart/form-data" id="multiExpressDealForm">
                    @csrf

                    <!-- Basic Deal Info -->
                    <div class="form-group mt-2">
                        <label>@lang('Category')</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">@lang('Select Category')</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $deal->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-2">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $deal->title ?? '') }}" required>
                    </div>

                    <div class="form-group mt-2">
                        <label>@lang('Short Description')</label>
                        <textarea name="short_description" class="form-control">{{ old('short_description', $deal->short_description ?? '') }}</textarea>
                    </div>
<div class="form-group mt-2">
    <label for="productDescription">@lang('Description')</label>
    <textarea name="description" class="form-control description-field" rows="5" id="productDescription">
        {{ old('description', $deal->description ?? '') }}
    </textarea>
</div>

<div class="form-group mt-2">
    <label for="deliveryNote">@lang('Additional Information')</label>
    <textarea name="delivery_note" class="form-control description-field" rows="5" id="deliveryNote">
        {{ old('delivery_note', $deal->delivery_note ?? '') }}
    </textarea>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof nicEditor !== "undefined") {
        // দুইটা field এর জন্য editor instance
        const descriptionEditor = new nicEditor({fullPanel: true});
        descriptionEditor.panelInstance('productDescription');

        const deliveryNoteEditor = new nicEditor({fullPanel: true});
        deliveryNoteEditor.panelInstance('deliveryNote');
    }

    // Form submit হলে content save করা
    const form = document.querySelector('#productForm');
    if(form) {
        form.addEventListener('submit', function(e) {
            const descEditor = nicEditors.findEditor('productDescription');
            if(descEditor) descEditor.saveContent();

            const noteEditor = nicEditors.findEditor('deliveryNote');
            if(noteEditor) noteEditor.saveContent();
        });
    }
});
</script>



                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label>@lang('Deal Price')</label>
                            <input type="number" name="deal_price" class="form-control" value="{{ old('deal_price', $deal->deal_price ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Regular Price')</label>
                            <input type="number" name="regular_price" class="form-control" value="{{ old('regular_price', $deal->regular_price ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Discount Percent')</label>
                            <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', $deal->discount_percent ?? 0) }}">
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const dealPriceInput = document.querySelector('input[name="deal_price"]');
                        const regularPriceInput = document.querySelector('input[name="regular_price"]');
                        const discountPercentInput = document.querySelector('input[name="discount_percent"]');

                        function updateDiscount() {
                            const dealPrice = parseFloat(dealPriceInput.value) || 0;
                            const regularPrice = parseFloat(regularPriceInput.value) || 0;

                            if (regularPrice > 0 && dealPrice <= regularPrice) {
                                const discount = ((regularPrice - dealPrice) / regularPrice) * 100;
                                discountPercentInput.value = Math.round(discount);
                            } else {
                                discountPercentInput.value = 0;
                            }
                        }

                        // Event listeners
                        dealPriceInput.addEventListener('input', updateDiscount);
                        regularPriceInput.addEventListener('input', updateDiscount);

                        // Initial calculation
                        updateDiscount();
                    });
                    </script>


                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label>@lang('Min Required')</label>
                            <input type="number" name="min_required" class="form-control" value="{{ old('min_required', $deal->min_required ?? 1) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Max Capacity')</label>
                            <input type="number" name="max_capacity" class="form-control" value="{{ old('max_capacity', $deal->max_capacity ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Purchase Limit Per User')</label>
                            <input type="number" name="purchase_limit_per_user" class="form-control" value="{{ old('purchase_limit_per_user', $deal->purchase_limit_per_user ?? '') }}">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label>@lang('Stock Status')</label>
                            <select name="stock_status" class="form-control">
                                <option value="ready" {{ old('stock_status', $deal->stock_status ?? '') == 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="upcoming" {{ old('stock_status', $deal->stock_status ?? '') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="sold_out" {{ old('stock_status', $deal->stock_status ?? '') == 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Delivery Start Date')</label>
                            <input type="date" name="delivery_start_date" class="form-control" value="{{ old('delivery_start_date', $deal->delivery_start_date ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label>@lang('Delivery End Date')</label>
                            <input type="date" name="delivery_end_date" class="form-control" value="{{ old('delivery_end_date', $deal->delivery_end_date ?? '') }}">
                        </div>
                    </div>


                    

                    <!-- Feature Image -->
                    <div class="form-group mt-3">
                        <label>@lang('Feature Image')</label>
                        <input type="file" name="feature_image" class="form-control">
                        <div class="mt-2">
                            <img id="featurePreview"
                                 src="{{ isset($deal) && $deal->feature_image ? asset(''.$deal->feature_image) : '' }}"
                                 class="img-thumbnail {{ isset($deal) && $deal->feature_image ? '' : 'd-none' }}"
                                 width="150" alt="Feature Image">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group mt-3">
                        <label>@lang('Status')</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status', $deal->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="upcoming" {{ old('status', $deal->status ?? '') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="closed" {{ old('status', $deal->status ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label>@lang('Deal Start Time')</label>
                            <input type="datetime-local" name="deal_start_time" class="form-control"
                                value="{{ old('deal_start_time', isset($deal->deal_start_time) ? \Carbon\Carbon::parse($deal->deal_start_time)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label>@lang('Deal End Time')</label>
                            <input type="datetime-local" name="deal_end_time" class="form-control"
                                value="{{ old('deal_end_time', isset($deal->deal_end_time) ? \Carbon\Carbon::parse($deal->deal_end_time)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>


                    
                    <!-- Pricing Tiers Section -->
                    <h5 class="mt-4">Pricing Tiers</h5>
                    <div id="tiersWrapper"></div>
                    <button type="button" id="addTierBtn" class="btn btn-sm btn-secondary mt-2">Add Tier</button>

                    <!-- Delivery Options Section -->
                    <h5 class="mt-4">Delivery Options ( Dhaka, Outside Dhaka, Pickup )</h5>
                    <div id="deliveryWrapper"></div>
                    <button type="button" id="addDeliveryBtn" class="btn btn-sm btn-secondary mt-2">Add Delivery Option</button>

                    <!-- Submit -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-outline-primary w-100">@lang('Save Deal')</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-lib')
<script src="{{ asset('assets/vendor/sortable/Sortable.min.js') }}"></script>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataHolder = document.getElementById('multiExpressData');
    const dealTiers = JSON.parse(dataHolder.dataset.tiers || '[]');
    const dealDelivery = JSON.parse(dataHolder.dataset.delivery || '[]');

    const tiersWrapper = document.getElementById('tiersWrapper');
    const deliveryWrapper = document.getElementById('deliveryWrapper');

    let tierIndex = dealTiers.length || 0;
    let deliveryIndex = dealDelivery.length || 0;

    function el(tag, attrs = {}, html = '') {
        const e = document.createElement(tag);
        for (let k in attrs) {
            if (k === 'class') e.className = attrs[k];
            else if (k === 'dataset') {
                for (let d in attrs[k]) e.dataset[d] = attrs[k][d];
            } else e.setAttribute(k, attrs[k]);
        }
        if (html) e.innerHTML = html;
        return e;
    }


    function createTierRow(idx, data = {}) {
        const row = el('div', { class: 'row g-2 mb-2 tier-row', dataset: { index: idx } });
        const c1 = el('div', { class: 'col-md-4' }, `<input type="number" name="pricing_tiers[${idx}][min_quantity]" placeholder="Min Qty" class="form-control mb-1" value="${data.min_quantity || ''}">`);
        const c2 = el('div', { class: 'col-md-4' }, `<input type="number" name="pricing_tiers[${idx}][max_quantity]" placeholder="Max Qty" class="form-control mb-1" value="${data.max_quantity || ''}">`);
        const c3 = el('div', { class: 'col-md-4 d-flex' }, `<input type="number" name="pricing_tiers[${idx}][price_per_item]" placeholder="Price per Item" class="form-control" value="${data.price_per_item || ''}">`);
        const removeBtn = el('button', { type: 'button', class: 'btn btn-sm btn-danger remove-btn ms-2' }, 'X');
        c3.appendChild(removeBtn);

        row.appendChild(c1);
        row.appendChild(c2);
        row.appendChild(c3);
        return row;
    }

    function createDeliveryRow(idx, data = {}) {
        const row = el('div', { class: 'row g-2 mb-2 delivery-row', dataset: { index: idx } });
        const c1 = el('div', { class: 'col-md-4' }, `<input type="text" name="delivery_options[${idx}][type]" placeholder="Type" class="form-control mb-1" value="${data.type || ''}">`);
        const c2 = el('div', { class: 'col-md-4' }, `<input type="text" name="delivery_options[${idx}][label]" placeholder="Label" class="form-control mb-1" value="${data.label || ''}">`);
        const c3 = el('div', { class: 'col-md-4 d-flex' }, `<input type="number" name="delivery_options[${idx}][charge_per_item]" placeholder="Charge per item" class="form-control" value="${data.charge_per_item || ''}">`);
        const removeBtn = el('button', { type: 'button', class: 'btn btn-sm btn-danger remove-btn ms-2' }, 'X');
        c3.appendChild(removeBtn);

        row.appendChild(c1);
        row.appendChild(c2);
        row.appendChild(c3);
        return row;
    }

    // populate existing


    (dealTiers.length ? dealTiers : [{}]).forEach((t, i) => tiersWrapper.appendChild(createTierRow(i, t)));
    tierIndex = tiersWrapper.children.length;

    (dealDelivery.length ? dealDelivery : [{}]).forEach((d, i) => deliveryWrapper.appendChild(createDeliveryRow(i, d)));
    deliveryIndex = deliveryWrapper.children.length;

    document.getElementById('addTierBtn').addEventListener('click', () => tiersWrapper.appendChild(createTierRow(tierIndex++)));
    document.getElementById('addDeliveryBtn').addEventListener('click', () => deliveryWrapper.appendChild(createDeliveryRow(deliveryIndex++)));

    // remove buttons
    [tiersWrapper, deliveryWrapper].forEach(wrapper => {
        wrapper.addEventListener('click', function(e){
            const btn = e.target.closest('.remove-btn');
            if (!btn) return;
            const row = btn.closest('.feature-row, .tier-row, .delivery-row');
            if(row) row.remove();
        });
    });

    // Sortable
    if(typeof Sortable !== 'undefined'){
        new Sortable(tiersWrapper, { animation:150 });
        new Sortable(deliveryWrapper, { animation:150 });
    }

    // Feature image preview
    const inputFile = document.querySelector('input[name="feature_image"]');
    const preview = document.getElementById('featurePreview');
    if(inputFile && preview){
        inputFile.addEventListener('change', function(e){
            const file = e.target.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = function(ev){
                preview.src = ev.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush

@push('style')
<style>
    .remove-btn { margin-left: 5px; }
    .handle { cursor: move; }
</style>
@endpush
