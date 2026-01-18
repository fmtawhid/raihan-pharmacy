@extends('admin.layouts.app')

@section('panel')
<style>
    .pos-container {
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        min-height: 100vh;
        padding: 20px 0;
        border-radius: 0;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }
    
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .card-footer {
        border-radius: 0 0 12px 12px !important;
        padding: 16px 20px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
        font-size: 15px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .list-group-item {
        border: 1px solid #f0f0f0;
        padding: 12px 15px;
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9ff;
        border-color: #667eea;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4);
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        border: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(235, 51, 73, 0.4);
    }
    
    .header-section {
        color: white;
        margin-bottom: 25px;
        text-align: center;
    }
    
    .header-section h2 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .header-section p {
        font-size: 16px;
        opacity: 0.9;
    }
    
    .table-responsive {
        border-radius: 8px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-weight: 600;
        color: #333;
        border: none;
        padding: 12px;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9ff;
    }
    
    .selected-customer-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 16px;
        margin-top: 15px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .selected-customer-box strong {
        font-size: 16px;
        display: block;
        margin-bottom: 5px;
    }
    
    .selected-customer-box small {
        display: block;
        opacity: 0.9;
        font-size: 13px;
    }
    
    .cart-summary {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 15px;
        font-weight: 600;
        font-size: 18px;
        text-align: right;
    }
    
    .no-data-msg {
        text-align: center;
        padding: 30px;
        color: #999;
    }
    
    .search-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .badge-price {
        background: #667eea;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<div class="pos-container">
    <div class="header-section">
        <h2><i class="la la-shopping-bag"></i> Point of Sale System</h2>
        <p>Smart & Premium POS Management</p>
    </div>

    <div class="row g-4">

        {{-- LEFT SIDE --}}
        <div class="col-lg-6">

            {{-- Product Search --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <strong><i class="la la-search"></i> Search Products</strong>
                </div>
                <div class="card-body">
                    <input type="text"
                           id="product-search"
                           class="form-control form-control-lg"
                           placeholder="🔍 Enter product name or SKU...">

                    <ul id="search-results" class="list-group list-group-flush mt-3"></ul>
                </div>
            </div>

            {{-- Customer Section --}}
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <strong><i class="la la-user-circle"></i> Customer Management</strong>
                </div>
                <div class="card-body">
                    <input type="text"
                           id="customer-search"
                           class="form-control"
                           placeholder="🔍 Search by name, email, or mobile...">

                    <ul id="customer-results" class="list-group list-group-flush mt-2"></ul>

                    <div id="selected-customer" class="mt-3"></div>
                </div>
            </div>

        </div>

        {{-- RIGHT SIDE --}}
        <div class="col-lg-6">

            {{-- Shopping Cart --}}
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                    <strong><i class="la la-shopping-cart"></i> Shopping Cart</strong>
                    <button id="clear-cart" class="btn btn-sm btn-outline-light" style="border: 1px solid white;">
                        <i class="la la-trash"></i> Clear Cart
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="cart-table">
                            <thead class="table-light">
                                <tr>
                                    <th><strong>Product</strong></th>
                                    <th class="text-center"><strong>Qty</strong></th>
                                    <th class="text-end"><strong>Price</strong></th>
                                    <th class="text-end"><strong>Total</strong></th>
                                    <th class="text-center"><strong>Action</strong></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <div class="cart-summary" id="cart-total">
                        Total: $0.00
                    </div>
                    <button id="confirm-order"
                            class="btn btn-success btn-lg w-100 mt-3">
                        <i class="la la-check-circle"></i> Confirm & Complete Order
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection


@push('script')
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<!-- Include Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
// Configure Toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

$(document).ready(function(){

    // Load cart on page load
    function loadCart(){
        $.get('{{ route("admin.pos.getCart") }}', function(res){
            renderCart(res.cart);
        });
    }

    // Load selected customer on page load
    function loadSelectedCustomer(){
        $.get('{{ route("admin.pos.getCustomer") }}')
            .done(function(res){
                renderSelectedCustomer(res.customer);
            });
    }

    loadCart();
    loadSelectedCustomer();

    // Search products (debounced)
    let searchTimer;
    $('#product-search').on('input', function(){
        clearTimeout(searchTimer);
        let query = $(this).val().trim();

        // clear previous results while typing
        if(!query || query.length < 2){ $('#search-results').empty(); return; }

        searchTimer = setTimeout(function(){
            $.get('{{ route("admin.pos.searchProducts") }}', {query: query})
                .done(function(data){
                    let html = '';
                    if(!data || data.length === 0){
                        html = '<li class="list-group-item text-center text-muted"><i class="la la-search"></i> No products found</li>';
                    } else {
                        data.forEach(product=>{
                            let regularPrice = product.regular_price ? Number(product.regular_price).toFixed(2) : null;
                            let salePrice = product.sale_price ? Number(product.sale_price).toFixed(2) : null;
                            
                            let priceDisplay = '';
                            if (regularPrice && salePrice) {
                                // Show both prices
                                priceDisplay = `<span class="badge-price"><span style="text-decoration: line-through; opacity: 1; color: #000000;">$${regularPrice}</span> <strong>$${salePrice}</strong></span>`;
                            } else if (salePrice) {
                                // Only sale price
                                priceDisplay = `<span class="badge-price"><strong>$${salePrice}</strong></span>`;
                            } else if (regularPrice) {
                                // Only regular price
                                priceDisplay = `<span class="badge-price"><strong>$${regularPrice}</strong></span>`;
                            } else {
                                // No price
                                priceDisplay = `<span class="badge-price"><strong>N/A</strong></span>`;
                            }
                            
                            html += `<li class="search-result-item">
                                        <div>
                                            <strong>${product.name}</strong>
                                            <br>
                                            ${priceDisplay}
                                        </div>
                                        <button class="btn btn-sm btn-primary add-to-cart" data-id="${product.id}" data-regular-price="${regularPrice || 0}" data-sale-price="${salePrice || 0}">
                                            <i class="la la-plus"></i> Add to Cart
                                        </button>
                                    </li>`;
                        });
                    }
                    $('#search-results').html(html);
                })
                .fail(function(xhr){
                    toastr.error('Failed to search products');
                    $('#search-results').html('<li class="list-group-item text-center text-danger"><i class="la la-warning"></i> Search error</li>');
                });

        }, 300);
    });

    // Add to cart
    $(document).on('click', '.add-to-cart', function(){
        let $btn = $(this), id = $btn.data('id');
        let productName = $btn.closest('.search-result-item').find('strong').text();
        $btn.prop('disabled', true);
        
        $.post('{{ route("admin.pos.addToCart") }}', {_token:'{{ csrf_token() }}', product_id:id}, function(res){
            renderCart(res.cart);
            toastr.success(`<i class="la la-check-circle"></i> "${productName}" added to cart!`);
        }).fail(function(){
            toastr.error('Failed to add product to cart');
        }).always(()=>{ $btn.prop('disabled', false); });
    });

    // Remove from cart
    $(document).on('click', '.remove-from-cart', function(){
        let $btn = $(this), id = $btn.data('id');
        let productName = $btn.closest('tr').find('td:first').text();
        $btn.prop('disabled', true);
        
        $.post('{{ route("admin.pos.removeFromCart") }}', {_token:'{{ csrf_token() }}', product_id:id}, function(res){
            renderCart(res.cart);
            toastr.info(`<i class="la la-trash"></i> "${productName}" removed from cart`);
        }).fail(function(){
            toastr.error('Failed to remove product');
        }).always(()=>{ $btn.prop('disabled', false); });
    });

    // Clear cart
    $('#clear-cart').click(function(){
        Swal.fire({
            title: 'Clear Cart?',
            text: 'Are you sure you want to clear the entire shopping cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#11998e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="la la-trash"></i> Yes, Clear It!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                let $btn = $(this); $btn.prop('disabled', true);
                $.post('{{ route("admin.pos.clearCart") }}', {_token:'{{ csrf_token() }}'}, function(res){
                    renderCart(res.cart);
                    toastr.success('<i class="la la-trash"></i> Cart cleared successfully!');
                }).fail(function(){
                    toastr.error('Failed to clear cart');
                }).always(()=>{ $btn.prop('disabled', false); });
            }
        });
    });

    // Confirm order
    $('#confirm-order').click(function(){
        let cart = {};
        $('#cart-table tbody tr').each(function(){
            if($(this).find('td').length > 1) {
                cart[$(this).data('product-id')] = true;
            }
        });
        
        if(Object.keys(cart).length === 0) {
            toastr.warning('<i class="la la-warning-circle"></i> Please add products to cart first!');
            return;
        }

        Swal.fire({
            title: 'Confirm Order?',
            text: 'Please review your cart and customer details before confirming.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#38ef7d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="la la-check-circle"></i> Confirm Order',
            cancelButtonText: 'Cancel',
            html: '<div style="text-align: left; margin-top: 20px;"><p><strong>Cart Items:</strong> ' + Object.keys(cart).length + '</p><p><strong>Total:</strong> <span id="confirm-total"></span></p></div>',
            didOpen: () => {
                let total = 0;
                $('#cart-table tbody tr').each(function(){
                    let $tds = $(this).find('td');
                    if($tds.length > 3) {
                        let totalCell = $tds.eq(3).text().replace('$', '');
                        total += parseFloat(totalCell) || 0;
                    }
                });
                $('#confirm-total').text('$' + total.toFixed(2));
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let $btn = $('#confirm-order'); 
                $btn.prop('disabled', true);
                $btn.html('<i class="la la-spinner fa-spin"></i> Processing...');
                
                $.post('{{ route("admin.pos.confirmOrder") }}', {_token:'{{ csrf_token() }}'}, function(res){
                    if(res.status === 'success') {
                        Swal.fire({
                            title: '<i class="la la-check-circle" style="color: #38ef7d;"></i> Success!',
                            html: '<p style="font-size: 16px; margin: 15px 0;"><strong>' + res.message + '</strong></p><p style="color: #666; font-size: 14px;">Order Number: <strong>#' + res.order_number + '</strong></p><p style="color: #666; font-size: 14px;">Total Amount: <strong>$' + res.total_amount.toFixed(2) + '</strong></p>',
                            icon: 'success',
                            confirmButtonColor: '#38ef7d',
                            confirmButtonText: '<i class="la la-check"></i> Done'
                        }).then(() => {
                            renderCart({});
                            renderSelectedCustomer(null);
                            $('#product-search').val('');
                            $('#search-results').empty();
                            $('#customer-search').val('');
                            $('#customer-results').empty();
                            toastr.success('<i class="la la-check-circle"></i> Order completed successfully!');
                        });
                    }
                }).fail(function(xhr){
                    let errorMsg = 'Failed to process order';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#eb3349'
                    });
                    toastr.error(errorMsg);
                }).always(()=>{ 
                    $btn.prop('disabled', false);
                    $btn.html('<i class="la la-check-circle"></i> Confirm & Complete Order');
                });
            }
        });
    });

    // Render cart table
    function renderCart(cart){
        let html='', total=0, hasItems=false;
        for(let id in cart){
            let item = cart[id];
            let itemTotal = item.price * item.quantity;
            total += itemTotal; 
            hasItems=true;
            html += `<tr data-product-id="${id}">
                        <td><strong>${item.name}</strong></td>
                        <td class="text-center"><span class="badge bg-primary">${item.quantity}</span></td>
                        <td class="text-end">$${item.price.toFixed(2)}</td>
                        <td class="text-end"><strong>$${itemTotal.toFixed(2)}</strong></td>
                        <td class="text-center"><button class="btn btn-sm btn-danger remove-from-cart" data-id="${id}"><i class="la la-trash"></i></button></td>
                    </tr>`;
        }
        if(!hasItems){
            html = `<tr><td colspan="5" class="text-center text-muted py-4"><i class="la la-shopping-cart"></i> Your cart is empty</td></tr>`;
            $('#cart-total').html('Total: $0.00');
        } else {
            $('#cart-total').html('Total: <strong>$' + total.toFixed(2) + '</strong>');
        }
        $('#cart-table tbody').html(html);
    }

    // Render selected customer
    function renderSelectedCustomer(customer){
        if(!customer){
            $('#selected-customer').html('<div class="no-data-msg"><i class="la la-user"></i> No customer selected</div>');
            return;
        }
        let html = `
            <div class="selected-customer-box">
                <strong><i class="la la-user-check"></i> Selected Customer</strong>
                <strong style="font-size: 18px; margin-top: 10px; display: block;">${customer.name || 'N/A'}</strong>
                <small><i class="la la-envelope"></i> ${customer.email || 'No email'}</small>
                <small><i class="la la-phone"></i> ${customer.mobile ? customer.mobile : 'No mobile'}</small>
                <div style="margin-top: 12px;">
                    <button id="clear-customer" class="btn btn-sm btn-outline-light" style="border: 1px solid white;">
                        <i class="la la-times"></i> Change Customer
                    </button>
                </div>
            </div>`;
        $('#selected-customer').html(html);
    }

    // Customer search handlers
    let custTimer;
    $('#customer-search').on('input', function(){
        clearTimeout(custTimer);
        let query = $(this).val().trim();
        if(!query || query.length < 2){ $('#customer-results').empty(); return; }

        custTimer = setTimeout(function(){
            $.get('{{ route("admin.pos.searchCustomers") }}', {query: query})
                .done(function(data){
                    let html = '';
                    if(!data || data.length === 0){
                        html = `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="text-muted"><i class="la la-search"></i> No customers found</div>
                                    <button class="btn btn-sm btn-success create-customer"><i class="la la-plus"></i> Create New</button>
                                </li>`;
                    } else {
                        data.forEach(c => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div><strong>${c.name || 'N/A'}</strong><br><small><i class="la la-envelope"></i> ${c.email || 'No email'} ${c.mobile ? ' | <i class="la la-phone"></i> ' + c.mobile : ''}</small></div>
                                        <button class="btn btn-sm btn-primary select-customer" data-id="${c.id}" data-name="${c.name}"><i class="la la-check"></i> Select</button>
                                    </li>`;
                        });
                    }
                    $('#customer-results').html(html);
                })
                .fail(function(){
                    toastr.error('Failed to search customers');
                    $('#customer-results').html('<li class="list-group-item text-center text-danger"><i class="la la-warning"></i> Search error</li>');
                });
        }, 300);
    });

    // Create customer from search box
    $(document).on('click', '.create-customer', function(){
        let name = $('#customer-search').val().trim();
        if(!name) {
            toastr.warning('Please enter a customer name');
            return;
        }

        Swal.fire({
            title: 'Create New Customer',
            html: `
                <input type="text" id="cust_name" class="swal2-input" placeholder="Full Name" value="${name}">
                <input type="email" id="cust_email" class="swal2-input" placeholder="Email (optional)">
                <input type="tel" id="cust_mobile" class="swal2-input" placeholder="Mobile (optional)">
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#38ef7d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="la la-check-circle"></i> Create Customer',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const name = document.getElementById('cust_name').value;
                const email = document.getElementById('cust_email').value;
                const mobile = document.getElementById('cust_mobile').value;
                if (!name) {
                    Swal.showValidationMessage('Name is required');
                    return false;
                }
                return { name, email, mobile };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.pos.createCustomer") }}', {
                    _token:'{{ csrf_token() }}', 
                    name: result.value.name,
                    email: result.value.email || null,
                    mobile: result.value.mobile || null
                }, function(res){
                    renderSelectedCustomer(res.customer);
                    $('#customer-results').empty();
                    $('#customer-search').val('');
                    toastr.success(`<i class="la la-user-check"></i> Customer "${res.customer.name}" created and selected!`);
                }).fail(function(xhr){
                    let errorMsg = 'Failed to create customer';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                });
            }
        });
    });

    // Select customer
    $(document).on('click', '.select-customer', function(){
        let id = $(this).data('id');
        let name = $(this).data('name');
        
        $.post('{{ route("admin.pos.selectCustomer") }}', {_token:'{{ csrf_token() }}', user_id:id}, function(res){
            renderSelectedCustomer(res.customer);
            $('#customer-results').empty();
            $('#customer-search').val('');
            toastr.success(`<i class="la la-user-check"></i> Customer "${res.customer.name}" selected!`);
        }).fail(function(){
            toastr.error('Failed to select customer');
        });
    });

    // Clear selected customer
    $(document).on('click', '#clear-customer', function(){
        Swal.fire({
            title: 'Change Customer?',
            text: 'Select a different customer for this order.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="la la-check"></i> Yes, Change',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.pos.clearCustomer") }}', {_token:'{{ csrf_token() }}'}, function(res){
                    renderSelectedCustomer(null);
                    $('#customer-search').val('').focus();
                    $('#customer-results').empty();
                    toastr.info('<i class="la la-user-times"></i> Customer cleared');
                }).fail(function(){
                    toastr.error('Failed to clear customer');
                });
            }
        });
    });

});
</script>
@endpush
