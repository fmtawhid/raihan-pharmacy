{{-- resources/views/admin/stock/receive_modal.blade.php --}}
<div class="modal fade" id="receiveStock" tabindex="-1" aria-labelledby="receiveStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="receiveStockLabel">@lang('Purchaser Info')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="receiveForm" method="post">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
                <input type="hidden" name="variant_id" value="">

                <div class="modal-body">
                    <div class="row gy-3">

                        <div class="col-md-6">
                            <label class="form-label">@lang('Batch No')</label>
                            <input class="form-control" name="batch_no" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">@lang('Purchased From')</label>

                            <select name="purchaser_id" id="purchaserSelect" class="form-control">
                                @foreach ($purchasers as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                                <option value="new">@lang('Other (type below)')</option>
                            </select>

                            <input name="new_purchaser" id="newPurchaserInput" class="form-control mt-2 d-none"
                                placeholder="@lang('New purchaser name')">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">@lang('Unit Cost')</label>
                            <input name="purchase_price" type="number" step="any" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">@lang('Qty Received')</label>
                            <input name="quantity" type="number" min="1" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">@lang('Purchase Date')</label>
                            <input name="purchased_at" type="date" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Close')</button>
                    <button class="btn btn--primary">@lang('Save')</button>
                </div>
            </form>

        </div>
    </div>
</div>


@push('script')
<script>
(function ($) {
    'use strict';

    $('#receiveForm').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        let   data  = $form.serializeArray();

        const purchaserVal = $form.find('[name=purchaser_id]').val();

        /* ── if user picked “Other (new)” ────────────────────────── */
        if (purchaserVal === 'new') {
            const newName = $.trim($form.find('[name=new_purchaser]').val());
            if (!newName.length) {
                notify('error', 'Type the new purchaser name');
                return;
            }

            /* create purchaser → then repost stock-receive */
            $.post('{{ route('admin.purchasers.store') }}',
                   {name: newName, _token: '{{ csrf_token() }}'},
                   res => {
                       data = data.filter(f => f.name !== 'purchaser_id');
                       data.push({name: 'purchaser_id', value: res.id});

                       $.post('{{ route('admin.stock.receive') }}',
                              $.param(data),
                              () => location.reload());
                   });
        } else {
            /* normal path */
            $.post('{{ route('admin.stock.receive') }}', data,
                   () => location.reload());
        }
    });

    /* tiny helper: toggle “new purchaser” input */
    $('#purchaserSelect').on('change', function () {
        $('#newPurchaserInput').toggleClass('d-none', $(this).val() !== 'new');
    });

})(jQuery);
</script>
@endpush