@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">@lang('Send SMS')</h5>
                        <a href="{{ route('admin.sms.panel.history') }}" class="btn btn-sm btn-outline--primary">
                            <i class="las la-history"></i> Previous SMS
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Send using the configured masking sender ID. Every recipient and delivery result is recorded in SMS history.</p>
                    <form id="sms-send-form" action="{{ route('admin.sms.panel.send') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Recipients</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipient-all" value="all" @checked(old('recipient_type') === 'all')>
                                    <label class="form-check-label" for="recipient-all">All customers</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipient-selected" value="selected" @checked(old('recipient_type', 'selected') === 'selected')>
                                    <label class="form-check-label" for="recipient-selected">Choose customers</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipient-manual" value="manual" @checked(old('recipient_type') === 'manual')>
                                    <label class="form-check-label" for="recipient-manual">Phone numbers</label>
                                </div>
                            </div>
                        </div>

                        <div id="customer-recipient-panel" class="border rounded p-3 mb-3">
                            <label for="customer-search" class="form-label">Search customers</label>
                            <input type="search" id="customer-search" class="form-control" autocomplete="off" placeholder="Type a name or phone number">
                            <div id="customer-search-results" class="list-group mt-2"></div>
                            <div id="selected-customers" class="d-flex flex-wrap gap-2 mt-2"></div>
                            <small class="text-muted">Search and select one or more customers. Customers without a phone number are skipped.</small>
                        </div>

                        <div id="manual-recipient-panel" class="form-group mb-3" style="display:none">
                            <label for="sms-mobile" class="form-label">Mobile number(s)</label>
                            <textarea id="sms-mobile" name="mobile" class="form-control" rows="3" placeholder="88017XXXXXXXX, 016XXXXXXXX">{{ old('mobile') }}</textarea>
                            <small class="text-muted">Separate numbers with commas, semicolons or new lines.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="sms-message" class="form-label">@lang('SMS Message')</label>
                            <textarea id="sms-message" name="message" class="form-control" rows="5" maxlength="5000" placeholder="@lang('Write your SMS message')" required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            <i class="las la-paper-plane"></i> @lang('Send SMS')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(function () {
        const selectedCustomers = new Map();
        const availableCustomers = new Map();
        let searchTimer;
        let requestId = 0;

        function updateRecipientPanels() {
            const type = $('input[name="recipient_type"]:checked').val();
            $('#customer-recipient-panel').toggle(type === 'selected');
            $('#manual-recipient-panel').toggle(type === 'manual');
        }

        function renderSelectedCustomers() {
            let html = '';
            selectedCustomers.forEach(function (customer, id) {
                html += `<span class="badge bg-primary d-inline-flex align-items-center gap-2 p-2">
                    ${$('<span>').text(customer.name + ' · ' + customer.mobile).html()}
                    <button type="button" class="btn-close btn-close-white remove-sms-customer" data-id="${id}" aria-label="Remove"></button>
                    <input type="hidden" name="customer_ids[]" value="${id}">
                </span>`;
            });
            $('#selected-customers').html(html);
        }

        $('input[name="recipient_type"]').on('change', updateRecipientPanels);
        updateRecipientPanels();

        $('#sms-send-form').on('submit', function (event) {
            if ($('input[name="recipient_type"]:checked').val() === 'all'
                && !window.confirm('Send this SMS to every customer with a phone number?')) {
                event.preventDefault();
            }
        });

        $('#customer-search').on('input', function () {
            clearTimeout(searchTimer);
            const query = $(this).val().trim();
            const currentRequestId = ++requestId;
            if (query.length < 2) {
                $('#customer-search-results').empty();
                return;
            }

            searchTimer = setTimeout(function () {
                $.get('{{ route("admin.sms.panel.customers") }}', { query: query })
                    .done(function (customers) {
                        if (currentRequestId !== requestId) return;
                        let html = '';
                        availableCustomers.clear();
                        customers.forEach(function (customer) {
                            if (selectedCustomers.has(String(customer.id))) return;
                            availableCustomers.set(String(customer.id), customer);
                            const safeName = $('<span>').text(customer.name).html();
                            const safeMobile = $('<span>').text(customer.mobile).html();
                            html += `<button type="button" class="list-group-item list-group-item-action add-sms-customer"
                                data-id="${customer.id}">
                                <strong>${safeName}</strong><span class="text-muted ms-2">${safeMobile}</span>
                            </button>`;
                        });
                        $('#customer-search-results').html(html || '<div class="list-group-item text-muted">No customers found.</div>');
                    })
                    .fail(function () {
                        if (currentRequestId === requestId) {
                            $('#customer-search-results').html('<div class="list-group-item text-danger">Customer search failed.</div>');
                        }
                    });
            }, 250);
        });

        $(document).on('click', '.add-sms-customer', function () {
            const id = String($(this).data('id'));
            const customer = availableCustomers.get(id);
            if (!customer) return;
            selectedCustomers.set(id, customer);
            renderSelectedCustomers();
            $(this).remove();
        });

        $(document).on('click', '.remove-sms-customer', function () {
            selectedCustomers.delete(String($(this).data('id')));
            renderSelectedCustomers();
        });
    });
</script>
@endpush