@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">

    <!-- Product Search & Customer -->
    <div class="col-md-6">
        <h5>Search Products</h5>
        <input type="text" id="product-search" class="form-control" placeholder="Type product name...">
        <ul id="search-results" class="list-group mt-2"></ul>

        <hr>
        <h5 class="mt-3">Customer</h5>
        <input type="text" id="customer-search" class="form-control" placeholder="Type customer name, email or mobile...">
        <ul id="customer-results" class="list-group mt-2"></ul>
        <div id="selected-customer" class="mt-2"></div>
    </div>

    <!-- Cart -->
    <div class="col-md-6">
        <h5>Cart</h5>
        <table class="table table-bordered" id="cart-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button id="clear-cart" class="btn btn-danger">Clear Cart</button>
        <button id="confirm-order" class="btn btn-success float-end">Confirm Order</button>
    </div>

</div>
@endsection

@push('script')
<script>
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
            console.log('Searching for:', query, '->', '{{ route("admin.pos.searchProducts") }}');

            $.get('{{ route("admin.pos.searchProducts") }}', {query: query})
                .done(function(data){
                    console.log('search response:', data);
                    let html = '';
                    if(!data || data.length === 0){
                        html = '<li class="list-group-item text-center text-muted">No products found</li>';
                    } else {
                        data.forEach(product=>{
                            let price = Number(product.price || 0).toFixed(2);
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>${product.name} - $${price}</div>
                                        <button class="btn btn-sm btn-primary add-to-cart" data-id="${product.id}">Add</button>
                                    </li>`;
                        });
                    }
                    $('#search-results').html(html);
                })
                .fail(function(xhr){
                    console.error('Search failed', xhr.status, xhr.responseText);
                    $('#search-results').html('<li class="list-group-item text-center text-danger">Search error</li>');
                });

        }, 300);
    });

    // Add to cart
    $(document).on('click', '.add-to-cart', function(){
        let $btn = $(this), id = $btn.data('id');
        $btn.prop('disabled', true);
        console.log('POST addToCart ->', '{{ route("admin.pos.addToCart") }}', 'id:', id);
        $.post('{{ route("admin.pos.addToCart") }}', {_token:'{{ csrf_token() }}', product_id:id}, function(res){
            renderCart(res.cart);
        }).always(()=>{ $btn.prop('disabled', false); });
    });

    // Remove from cart
    $(document).on('click', '.remove-from-cart', function(){
        let $btn = $(this), id = $btn.data('id');
        $btn.prop('disabled', true);
        console.log('POST removeFromCart ->', '{{ route("admin.pos.removeFromCart") }}', 'id:', id);
        $.post('{{ route("admin.pos.removeFromCart") }}', {_token:'{{ csrf_token() }}', product_id:id}, function(res){
            renderCart(res.cart);
        }).always(()=>{ $btn.prop('disabled', false); });
    });

    // Clear cart
    $('#clear-cart').click(function(){
        let $btn=$(this); $btn.prop('disabled',true);
        console.log('POST clearCart ->', '{{ route("admin.pos.clearCart") }}');
        $.post('{{ route("admin.pos.clearCart") }}', {_token:'{{ csrf_token() }}'}, function(res){
            renderCart(res.cart);
        }).always(()=>{ $btn.prop('disabled',false); });
    });

    // Confirm order
    $('#confirm-order').click(function(){
        if(!confirm('Confirm this POS order?')) return;
        let $btn=$(this); $btn.prop('disabled',true);
        console.log('POST confirmOrder ->', '{{ route("admin.pos.confirmOrder") }}');
        $.post('{{ route("admin.pos.confirmOrder") }}', {_token:'{{ csrf_token() }}'}, function(res){
            alert(res.message);
            renderCart({});
            renderSelectedCustomer(null);
        }).always(()=>{ $btn.prop('disabled',false); });
    });

    // Render cart table
    function renderCart(cart){
        let html='', total=0, hasItems=false;
        for(let id in cart){
            let item = cart[id];
            let itemTotal = item.price * item.quantity;
            total += itemTotal; hasItems=true;
            html += `<tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>$${itemTotal.toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-danger remove-from-cart" data-id="${id}">Remove</button></td>
                    </tr>`;
        }
        if(!hasItems){
            html = `<tr><td colspan="5" class="text-center">Cart is empty</td></tr>`;
        } else {
            html += `<tr><td colspan="3">Total</td><td colspan="2">$${total.toFixed(2)}</td></tr>`;
        }
        $('#cart-table tbody').html(html);
    }

    // Render selected customer
    function renderSelectedCustomer(customer){
        if(!customer){
            $('#selected-customer').html('<div class="text-muted">No customer selected</div>');
            return;
        }
        let html = `
            <div class="d-flex justify-content-between align-items-center border p-2">
                <div>
                    <strong>${customer.name}</strong><br>
                    <small>${customer.email || ''} ${customer.mobile ? ' | ' + customer.mobile : ''}</small>
                </div>
                <div>
                    <button id="clear-customer" class="btn btn-sm btn-outline-danger">Clear</button>
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
                                    <div class="text-muted">No customers found</div>
                                    <button class="btn btn-sm btn-success create-customer">Create</button>
                                </li>`;
                    } else {
                        data.forEach(c => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div><strong>${c.name}</strong><br><small>${c.email || ''} ${c.mobile ? ' | ' + c.mobile : ''}</small></div>
                                        <button class="btn btn-sm btn-primary select-customer" data-id="${c.id}" data-name="${c.name}">Select</button>
                                    </li>`;
                        });
                    }
                    $('#customer-results').html(html);
                })
                .fail(function(){
                    $('#customer-results').html('<li class="list-group-item text-center text-danger">Search error</li>');
                });
        }, 300);
    });

    // Create customer from search box
    $(document).on('click', '.create-customer', function(){
        let name = $('#customer-search').val().trim();
        if(!name) return alert('Name is required');
        $.post('{{ route("admin.pos.createCustomer") }}', {_token:'{{ csrf_token() }}', name: name}, function(res){
            renderSelectedCustomer(res.customer);
            $('#customer-results').empty();
            $('#customer-search').val('');
        }).fail(function(xhr){
            alert('Failed to create customer');
            console.error(xhr);
        });
    });

    // Select customer
    $(document).on('click', '.select-customer', function(){
        let id = $(this).data('id');
        $.post('{{ route("admin.pos.selectCustomer") }}', {_token:'{{ csrf_token() }}', user_id:id}, function(res){
            renderSelectedCustomer(res.customer);
            $('#customer-results').empty();
            $('#customer-search').val('');
        }).fail(function(){ alert('Failed to select customer'); });
    });

    // Clear selected customer
    $(document).on('click', '#clear-customer', function(){
        $.post('{{ route("admin.pos.clearCustomer") }}', {_token:'{{ csrf_token() }}'}, function(res){
            renderSelectedCustomer(null);
        }).fail(function(){ alert('Failed to clear selected customer'); });
    });

});
</script>
@endpush
