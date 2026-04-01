@extends($activeTemplate . 'layouts.checkout')
@php
    $locations = getBangladeshLocationData();
@endphp
@php
    $couponAmount = $coupon['amount'] ?? 0;
    $shippingCharge = $shippingMethod->charge ?? 0;
    $totalAmount = $subtotal + $shippingCharge - $couponAmount;
@endphp

@section('blade')
    <div class="checkout-container">
        <div class="row g-4">
            <!-- Left Column - Cart Items and Delivery Info -->
            <div class="col-lg-8">
                @if (!blank($cartData))
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5 class="title">@lang('Your Order')</h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="cart-items">
                                @foreach ($cartData as $cartItem)
                                    <x-dynamic-component :component="frontendComponent('cart-item')" :cartItem="$cartItem" />
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-cart text-center py-5">
                        <div class="empty-cart-icon mb-4">
                            <i class="las la-shopping-cart"></i>
                        </div>
                        <h5 class="mb-3">@lang('Your cart is empty')</h5>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="las la-arrow-left me-2"></i> @lang('Continue Shopping')
                        </a>
                    </div>
                @endif

                @if (!blank($cartData))
                    <form action="{{ route('user.checkout.complete') }}" method="POST" id="orderForm">
                        @csrf

                        <!-- Delivery Information -->
                        <div class="checkout-card mb-4">
                            <div class="checkout-card-header">
                                <h5 class="title">@lang('Delivery Information')</h5>
                            </div>
                            <div class="checkout-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Full Name') <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ auth()->user() ? auth()->user()->fullname : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Phone Number') <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="phone" class="form-control"
                                                value="{{ auth()->user() ? auth()->user()->mobile : '' }}">
                                        </div>
                                    </div>
                                    @guest
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">@lang('Email Address') <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" name="guest_email" class="form-control"
                                                    placeholder="guest@example.com" required>
                                            </div>
                                        </div>
                                    @endguest
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Delivery Address') <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="address" class="form-control" rows="3" placeholder="@lang('Street address, apartment, floor, etc.')"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group" style="display: none;">
                                            <label class="form-label">@lang('Division') <span
                                                    class="text-danger">*</span></label>
                                            <select name="division_id" id="divisionSelect" class="form-control">
                                                <option value="">@lang('Select Division')</option>
                                                @foreach ($locations['divisions'] as $division)
                                                    <option value="{{ $division['id'] }}">{{ $division['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group" style="display: none;">
                                            <label class="form-label">@lang('District') <span
                                                    class="text-danger">*</span></label>
                                            <select name="district_id" id="districtSelect" class="form-control">
                                                <option value="">@lang('Select District')</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group" style="display: none;">
                                            <label class="form-label">@lang('Area/Upazila')</label>
                                            <select name="area_name" id="areaSelect" class="form-control">
                                                <option value="">@lang('Select Area')</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group" style="display: none;">
                                            <label class="form-label">@lang('Postcode')</label>
                                            <input type="text" name="postcode" id="postcodeInput" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div class="checkout-card mb-4">
                            <div class="checkout-card-header">
                                <h5 class="title">@lang('Shipping Method')</h5>
                            </div>
                            <div class="checkout-card-body">
                                <div class="shipping-methods">
                                    @foreach ($shippingMethods as $item)
                                        <div class="shipping-method-item">
                                            <label class="shipping-method-label" for="method-{{ $item->id }}">
                                                <div class="form-check">
                                                    <input onclick="handleGetCharge({{ $item->charge }})"
                                                        class="form-check-input" type="radio" name="shipping_method_id"
                                                        value="{{ $item->id }}" id="method-{{ $item->id }}"
                                                        data-resource="{{ $item }}">
                                                    <span class="shipping-method-name">{{ __($item->name) }}</span>
                                                </div>
                                                <span class="shipping-method-price">{{ showAmount($item->charge) }}</span>
                                            </label>

                                            <div class="shipping-method-details mt-2">
                                                <div class="detail-item">
                                                    <i class="las la-clock"></i>
                                                    <span>@lang('Delivered in') {{ $item->shipping_time }}
                                                        @lang('days')</span>
                                                </div>
                                                @if (strip_tags($item->description))
                                                    <div class="detail-item">
                                                        <i class="las la-info-circle"></i>
                                                        <div class="description">@php echo $item->description @endphp</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
            </div>

            <!-- Right Column - Order Summary and Payment -->
            <div class="col-lg-4">
                <div class="checkout-sidebar">
                    <!-- Product Search -->
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5 class="title">@lang('Search Products')</h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="form-group mb-0">
                                <input type="search" id="productSearchInput" class="form-control" placeholder="@lang('Search products by name...')">
                            </div>
                            <div id="searchResults" class="search-results mt-3"></div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5 class="title">@lang('Order Summary')</h5>
                        </div>
                        <div class="checkout-card-body">
                            <!-- Coupon Section (NOT a form - prevents nesting issues) -->
                            <div class="mb-3 pb-3" style="border-bottom: 1px solid #f0f0f0;">
                                <div id="couponForm" class="d-flex gap-2">
                                    <input type="text" id="couponCodeInput" class="form-control form-control-sm" placeholder="@lang('Coupon code')">
                                    <button type="button" class="btn btn-primary btn-sm" id="applyCouponBtn">@lang('Apply')</button>
                                </div>
                                <div id="couponMessage" class="mt-2 small"></div>
                            </div>

                            <div class="order-summary">
                                <div class="summary-item">
                                    <span>@lang('Subtotal')</span>
                                    <span id="cartSubtotal">{{ showAmount($subtotal) }}</span>
                                    <span class="d-none" id="subtotal">{{ $subtotal }}</span>
                                </div>

                                @isset($coupon)
                                    <div class="summary-item text-success">
                                        <span>
                                            @lang('Coupon Discount')
                                            <small>({{ $coupon['code'] }})</small>
                                        </span>
                                        <span>-<span id="couponAmount">{{ showAmount($couponAmount) }}</span></span>
                                    </div>
                                @endisset

                                <div class="summary-item">
                                    <span>
                                        @lang('Shipping')
                                        <i class="las la-question-circle" data-bs-toggle="tooltip"
                                            title="@lang('Based on selected shipping method')"></i>
                                    </span>
                                    <span>৳ <span class="delivery-fee" id="delivery-fee">{{ $shippingCharge }}</span>TK
                                    </span>
                                </div>

                                <div class="summary-item processing-fee-item d-none">
                                    <span>
                                        @lang('Processing Fee')
                                        <i class="las la-question-circle" data-bs-toggle="tooltip"
                                            title="@lang('Payment gateway processing charges')"></i>
                                    </span>
                                    <span class="processing-fee">0.00</span>
                                </div>

                                <div class="summary-divider"></div>

                                <div class="summary-item total">
                                    <span>@lang('Total')</span>
                                    <span>
                                        ৳ <span class="final-amount" id="total">{{ $totalAmount }}</span> TK
                                    </span>
                                </div>

                                <div class="conversion-notice bg-light p-3 mt-3 rounded-2 d-none">
                                    <p class="mb-1">
                                        <small>@lang('Amount in') <span class="gateway-currency fw-bold"></span>:</small>
                                    </p>
                                    <h5 class="in-currency mb-0 fw-bold"></h5>
                                    <p class="small mt-2 mb-0 text-muted">
                                        <i class="las la-info-circle"></i>
                                        @lang('Final amount will be shown on payment page')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="checkout-card mb-4">
                        <div class="checkout-card-header">
                            <h5 class="title">@lang('Payment Method')</h5>
                        </div>
                        <div class="checkout-card-body">
                            <div class="payment-methods">
                                @if (gs('cod') && $hasPhysicalProduct)
                                    <div class="payment-method-item cod-method">
                                        <label class="payment-method-label">
                                            <div class="form-check">
                                                <input class="form-check-input online_payment" type="radio"
                                                    name="gateway" value="0" data-gateway="cod"
                                                    data-currency="{{ gs('cur_text') }}">
                                                <span class="payment-method-name">
                                                    <i class="las la-money-bill-wave"></i>
                                                    @lang('Cash on Delivery')
                                                </span>
                                            </div>
                                            <img src="{{ asset($activeTemplateTrue . 'images/cod.png') }}"
                                                class="payment-method-icon" alt="COD">
                                        </label>
                                    </div>

                                    <div class="payment-method-divider">
                                        <span>@lang('OR')</span>
                                    </div>
                                @endif

                                <div class="online-payment-methods">
                                    @foreach ($gatewayCurrencies as $item)
                                        <div class="payment-method-item">
                                            <label class="payment-method-label" for="data-{{ $loop->index }}">
                                                <div class="form-check">
                                                    <input class="form-check-input online_payment" type="radio"
                                                        name="gateway" value="{{ $item->method_code }}"
                                                        id="data-{{ $loop->index }}"
                                                        data-gateway="{{ $item }}">
                                                    <span class="payment-method-name">{{ __($item->name) }}</span>
                                                </div>
                                                <img src="{{ getImage(getFilePath('gateway') . '/' . @$item->method->image, getFileSize('gateway')) }}"
                                                    class="payment-method-icon" alt="{{ __($item->name) }}">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Hidden currency field with default value -->
                            <input type="hidden" name="currency" value="{{ gs('cur_text') }}" id="currencyField">

                            <button type="submit" class="btn btn--base w-100 mt-4 py-3 checkout-btn">
                                <span class="btn-text">@lang('Complete Order')</span>
                                <i class="las la-lock ms-2"></i>
                            </button>

                            <div class="secure-checkout-note text-center mt-3">
                                <i class="las la-lock"></i>
                                <span>@lang('Secure SSL Encryption')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            @endif
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderForm = document.getElementById('orderForm');
            const shippingMethods = document.querySelectorAll('input[name="shipping_method_id"]');
            const paymentMethods = document.querySelectorAll('input[name="gateway"]');

            // Auto-select first shipping and payment method if available
            if (shippingMethods.length > 0 && !Array.from(shippingMethods).some(m => m.checked)) {
                shippingMethods[0].checked = true;
                shippingMethods[0].dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (paymentMethods.length > 0 && !Array.from(paymentMethods).some(m => m.checked)) {
                paymentMethods[0].checked = true;
                paymentMethods[0].dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Validate on form submission
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();

                console.log('Form validation triggered...');

                if (validateForm()) {
                    // Double-check that payment method is selected
                    const selectedGateway = document.querySelector('input[name="gateway"]:checked');
                    if (!selectedGateway) {
                        alert('Please select a payment method before confirming');
                        return;
                    }

                    // Disable button and show processing
                    const submitBtn = this.querySelector('.checkout-btn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>@lang("Processing...")';
                    }
                    
                    console.log('Form validation passed. Final totals:', {
                        subtotal: window.subtotal,
                        shipping: window.currentShippingCharge,
                        discount: window.couponAmount,
                        total: (window.subtotal + window.currentShippingCharge - window.couponAmount),
                        gateway: selectedGateway.value,
                        currency: document.getElementById('currencyField').value
                    });
                    
                    // Submit the form immediately - no delay needed
                    this.submit();
                } else {
                    console.warn('Form validation failed - check above errors');
                    // Re-enable button if validation fails
                    const submitBtn = this.querySelector('.checkout-btn');
                    if (submitBtn && submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '@lang("Complete Order")';
                    }
                }
            });

            function validateForm() {
                // Reset previous errors
                clearErrors();

                let isValid = true;

                // Validate delivery information
                const name = document.querySelector('input[name="name"]');
                const phone = document.querySelector('input[name="phone"]');
                const address = document.querySelector('textarea[name="address"]');

                if (!name || !name.value.trim()) {
                    if (name) showError(name, 'Full name is required');
                    isValid = false;
                }

                if (!phone || !phone.value.trim()) {
                    if (phone) showError(phone, 'Phone number is required');
                    isValid = false;
                } else if (!/^[0-9]{11,15}$/.test(phone.value)) {
                    if (phone) showError(phone, 'Please enter a valid phone number');
                    isValid = false;
                }

                if (!address || !address.value.trim()) {
                    if (address) showError(address, 'Delivery address is required');
                    isValid = false;
                }

                // Validate shipping method
                let shippingSelected = false;
                shippingMethods.forEach(method => {
                    if (method.checked) shippingSelected = true;
                });

                if (!shippingSelected) {
                    const shippingContainer = document.querySelector('.shipping-methods');
                    if (shippingContainer) {
                        showError(shippingContainer, 'Please select a shipping method');
                    }
                    isValid = false;
                }

                // Validate payment method
                let paymentSelected = false;
                paymentMethods.forEach(method => {
                    if (method.checked) paymentSelected = true;
                });

                if (!paymentSelected) {
                    const paymentContainer = document.querySelector('.payment-methods');
                    if (paymentContainer) {
                        showError(paymentContainer, 'Please select a payment method');
                    }
                    isValid = false;
                }

                // Debug info
                console.log('Order Form Validation:', {
                    name: name ? name.value.trim() : '',
                    phone: phone ? phone.value.trim() : '',
                    address: address ? address.value.trim().substring(0, 20) : '',
                    shippingSelected: shippingSelected,
                    paymentSelected: paymentSelected,
                    overallValid: isValid
                });

                return isValid;
            }

            function showError(element, message) {
                // Create error element if it doesn't exist
                let errorElement = element.nextElementSibling;
                if (!errorElement || !errorElement.classList.contains('error-message')) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'error-message text-danger small mt-1';
                    element.parentNode.appendChild(errorElement);
                }

                errorElement.textContent = message;
                element.classList.add('is-invalid');

                // Scroll to the first error
                if (element.scrollIntoView) {
                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            function clearErrors() {
                // Remove all error messages
                document.querySelectorAll('.error-message').forEach(el => el.remove());

                // Remove invalid classes
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            }

            // Real-time validation for fields - with safety checks
            let nameInput = document.querySelector('input[name="name"]');
            let phoneInput = document.querySelector('input[name="phone"]');
            let addressInput = document.querySelector('textarea[name="address"]');
            
            if (nameInput) {
                nameInput.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        showError(this, 'Full name is required');
                    }
                });
            }

            if (phoneInput) {
                phoneInput.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        showError(this, 'Phone number is required');
                    } else if (!/^[0-9]{11,15}$/.test(this.value)) {
                        showError(this, 'Please enter a valid phone number');
                    }
                });
            }

            if (addressInput) {
                addressInput.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        showError(this, 'Delivery address is required');
                    }
                });
            }

            // Shipping method selection validation
            shippingMethods.forEach(method => {
                method.addEventListener('change', function() {
                    const shippingContainer = document.querySelector('.shipping-methods');
                    const error = shippingContainer.querySelector('.error-message');
                    if (error) error.remove();
                });
            });

            // Payment method selection validation
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    const paymentContainer = document.querySelector('.payment-methods');
                    const error = paymentContainer.querySelector('.error-message');
                    if (error) error.remove();
                });
            });
        });

        function handleGetCharge(charge) {
            // This function is deprecated - using jQuery event handler instead
            // But keeping it for backward compatibility with inline onclick
            const chargeValue = parseFloat(charge) || 0;
            window.currentShippingCharge = chargeValue;
            
            // Trigger the jQuery handler to recalculate totals properly
            $('[name="shipping_method_id"]:checked').trigger('change');
        }
    </script>

    <script>
        // Declare variables at global scope
        window.subtotal = parseFloat("{{ $subtotal }}") || 0;
        window.couponAmount = parseFloat("{{ $couponAmount }}") || 0;
        window.currentShippingCharge = parseFloat("{{ $shippingMethods->first()->charge ?? 0 }}") || 0;
        window.defaultCurrency = "{{ gs('cur_text') }}";

        // Global function to calculate and update totals
        window.updateTotals = function() {
            // Ensure we have valid values
            let subtotal = parseFloat(window.subtotal) || 0;
            let shipping = parseFloat(window.currentShippingCharge) || 0;
            let discount = parseFloat(window.couponAmount) || 0;
            
            // Calculate new total: subtotal + shipping - discount
            let newTotal = subtotal + shipping - discount;
            
            // Safety check - ensure total is not negative
            if (newTotal < 0) {
                newTotal = 0;
            }

            // Update displays with proper formatting
            $('.delivery-fee').text(shipping.toFixed(2));
            $('#total').text(newTotal.toFixed(2));
            $('.final-amount').text(newTotal.toFixed(2));
            
            // Also update the processing fee calculation if payment gateway is selected
            console.log('Totals Updated:', {
                subtotal: subtotal,
                shipping: shipping,
                discount: discount,
                total: newTotal
            });

            return newTotal;
        };

        (function($) {
            "use strict";

            // Initialize tooltips (with safety check)
            if (typeof $ !== 'undefined' && $.fn.tooltip) {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // Set default currency
            $('#currencyField').val(window.defaultCurrency);

            // Listen for cart updates from other scripts (e.g., global cart handler)
            document.addEventListener('cart:updated', function(e) {
                const amount = parseFloat(e?.detail?.subtotal) || 0;
                window.subtotal = amount;

                // Debug: show cart update event in console
                console.debug('cart:updated event received:', {
                    new_subtotal: window.subtotal,
                    shipping: window.currentShippingCharge,
                    coupon: window.couponAmount
                });

                // Update visible fields
                $('#subtotal').text(window.subtotal.toFixed(2));
                $('#cartSubtotal').text(window.subtotal.toFixed(2));
                $('.cartSubtotal').text(Math.abs(window.subtotal).toFixed(2));

                // Recalculate totals and payment fees
                let newTotal = window.updateTotals();
                if ($('[name="gateway"]:checked').length) {
                    window.calculation($('[name="gateway"]:checked').data('gateway'), newTotal);
                }
            });

            // Shipping method change handler
            $('[name="shipping_method_id"]').on('change', function() {
                // Get the charge from the shipping method price text
                let charge = parseFloat($(this).closest('.shipping-method-item').find('.shipping-method-price').text().replace(/[^0-9.]/g, '')) || 0;
                
                // Update global shipping charge
                window.currentShippingCharge = charge;
                
                // Update the display
                $('.delivery-fee').text(charge.toFixed(2));
                
                // Recalculate totals
                let newTotal = window.updateTotals();
                
                // Log for debugging
                console.log('Shipping method changed:', {
                    charge: charge,
                    new_total: newTotal,
                    coupon_discount: window.couponAmount
                });
                
                // Recalculate payment fees
                if ($('[name="gateway"]:checked').length) {
                    window.calculation($('[name="gateway"]:checked').data('gateway'), newTotal);
                }
            });

            // Payment method change handler
            $('[name="gateway"]').on('change', function() {
                let gateway = $(this).data('gateway');
                let currency = gateway == 'cod' ? window.defaultCurrency : gateway.currency;
                $('#currencyField').val(currency);

                // Toggle processing fee display
                $('.processing-fee-item').toggleClass('d-none', gateway == 'cod');

                // Get current total and calculate with fees
                let currentTotal = window.subtotal + window.currentShippingCharge - window.couponAmount;
                
                console.log('Payment method changed:', {
                    gateway_type: gateway == 'cod' ? 'COD' : 'Online',
                    base_amount: currentTotal,
                    currency: currency
                });
                
                window.calculation(gateway, currentTotal);
            });

            // Coupon handler - Click listener (not form submit to avoid conflicts)
            $('#applyCouponBtn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                let couponCode = $('#couponCodeInput').val().trim();
                
                if (!couponCode) {
                    showCouponMessage('Please enter a coupon code', 'error');
                    return;
                }

                // Disable button during processing
                $('#applyCouponBtn').prop('disabled', true).text('@lang("Applying...")');
                
                // Get current subtotal from the page
                let subtotalAmount = window.subtotal || 0;
                console.log('🎟️ Applying coupon:', {
                    code: couponCode,
                    subtotal: subtotalAmount
                });

                $.ajax({
                    url: '{{ route("user.coupon.apply") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        code: couponCode,
                        subtotal: subtotalAmount
                    },
                    success: function(response) {
                        if (response.success) {
                            // Parse discount amount properly
                            let discountAmount = parseFloat(response.discount_amount) || 0;
                            
                            // Update global coupon amount 
                            window.couponAmount = discountAmount;
                            
                            console.log('✅ Coupon Applied:', {
                                code: response.coupon_code,
                                discount: discountAmount
                            });
                            
                            showCouponMessage(response.message, 'success');
                            $('#couponCodeInput').prop('disabled', true);
                            $('#applyCouponBtn').text('✓ @lang("Applied")').prop('disabled', true);
                            
                            // Find or create coupon row in order summary - LIVE UPDATE
                            let $couponRow = $('.order-summary').find('.summary-item.text-success');
                            
                            if ($couponRow.length > 0) {
                                // Update existing coupon discount amount LIVE
                                $couponRow.find('#couponAmount').text(discountAmount.toFixed(2));
                            } else {
                                // Create and insert new coupon discount row
                                let couponLabel = '@lang("Coupon Discount")';
                                let newCouponRow = '<div class="summary-item text-success">' +
                                    '<span>' + couponLabel + ' <small>(' + response.coupon_code + ')</small></span>' +
                                    '<span>-<span id="couponAmount">' + discountAmount.toFixed(2) + '</span></span>' +
                                    '</div>';
                                
                                // Insert after subtotal row
                                $('.order-summary').find('.summary-item').first().after(newCouponRow);
                            }
                            
                            // Recalculate totals - CRITICAL STEP
                            let finalTotal = window.updateTotals();
                            
                            // Recalculate payment fees if gateway selected
                            let $selectedGateway = $('[name="gateway"]:checked');
                            if ($selectedGateway.length > 0) {
                                let gatewayData = $selectedGateway.data('gateway');
                                window.calculation(gatewayData, finalTotal);
                            }
                        } else {
                            showCouponMessage(response.message || 'Coupon code is not valid', 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error applying coupon';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 400) {
                            errorMsg = 'Invalid coupon code';
                        } else if (xhr.status === 422) {
                            errorMsg = 'Coupon validation failed';
                        }
                        
                        showCouponMessage(errorMsg, 'error');
                    },
                    complete: function() {
                        $('#applyCouponBtn').prop('disabled', false).text('@lang("Apply")');
                    }
                });
            });

            function showCouponMessage(message, type) {
                let htmlClass = type === 'success' ? 'alert-success' : 'alert-danger';
                $('#couponMessage').html('<div class="alert ' + htmlClass + ' mb-0">' + message + '</div>');
                
                if (type === 'success') {
                    setTimeout(function() {
                        $('#couponMessage').fadeOut();
                    }, 3000);
                }
            }

            // Calculate payment processing fees - exposed globally
            window.calculation = function(gateway, amount) {
                // Ensure amount is valid
                if (typeof amount !== 'number' || amount < 0) {
                    amount = window.subtotal + window.currentShippingCharge - window.couponAmount;
                }

                // Handle COD case
                if (gateway == 'cod') {
                    gateway = {
                        percent_charge: 0,
                        fixed_charge: 0,
                        currency: window.defaultCurrency,
                        method: {
                            crypto: ''
                        },
                        min_amount: 0,
                        max_amount: 999999,
                        rate: 1
                    };
                    $('.conversion-notice').addClass('d-none');
                }

                // Calculate charges
                let percentCharge = parseFloat(gateway.percent_charge) || 0;
                let fixedCharge = parseFloat(gateway.fixed_charge) || 0;
                let totalPercentCharge = amount * (percentCharge / 100);
                let totalCharge = totalPercentCharge + fixedCharge;
                let totalAmount = amount + totalCharge;

                // Ensure totalAmount is valid
                if (totalAmount < 0) {
                    totalAmount = amount;
                }

                // Update displays
                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                $(".gateway-currency").text(gateway.currency);

                // Handle currency conversion display
                if (gateway.currency != window.defaultCurrency && gateway.method.crypto != 1) {
                    $(".conversion-notice").removeClass('d-none');
                    $('.in-currency').text((totalAmount * gateway.rate).toFixed(2));
                } else {
                    $(".conversion-notice").addClass('d-none');
                }

                // Check payment gateway limits
                let shouldDisable = false;
                if (gateway != 'cod') {
                    let minAmount = parseFloat(gateway.min_amount) || 0;
                    let maxAmount = parseFloat(gateway.max_amount) || Infinity;
                    shouldDisable = amount < minAmount || amount > maxAmount;
                }
                
                $(".checkout-btn").prop('disabled', shouldDisable);
                
                // Show warning message if amount is outside limits
                if (shouldDisable && gateway != 'cod') {
                    let warningMsg = 'Amount ' + amount.toFixed(2) + ' is outside payment gateway limits. Please use another payment method.';
                    console.warn(warningMsg);
                }
                
                console.log('Payment Calculation:', {
                    gateway_type: (gateway == 'cod' ? 'COD' : 'Online'),
                    gateway_currency: gateway.currency,
                    base_amount: amount,
                    percent_charge: percentCharge,
                    fixed_charge: fixedCharge,
                    total_charge: totalCharge,
                    final_amount: totalAmount,
                    button_disabled: shouldDisable
                });
            };

            // Initialize on page load
            $(document).ready(function() {
                console.log('Initializing checkout page totals:', {
                    subtotal: window.subtotal,
                    shipping: window.currentShippingCharge,
                    discount: window.couponAmount,
                    default_currency: window.defaultCurrency
                });
                
                // First, recalculate all totals
                window.updateTotals();
                
                // Then, ensure payment gateway is selected and properly configured
                if ($('[name="gateway"]:checked').length) {
                    let selectedGateway = $('[name="gateway"]:checked');
                    let gatewayData = selectedGateway.data('gateway');
                    let currency = (gatewayData == 'cod') ? window.defaultCurrency : gatewayData.currency;
                    
                    // Set currency field explicitly
                    $('#currencyField').val(currency);
                    
                    // Calculate fees
                    let totalAmount = window.updateTotals();
                    window.calculation(gatewayData, totalAmount);
                    
                    console.log('Payment gateway initialized:', {
                        gateway: selectedGateway.val(),
                        currency: currency,
                        total: totalAmount
                    });
                } else {
                    // Auto-select first payment method if none selected
                    let $firstPayment = $('[name="gateway"]').first();
                    if ($firstPayment.length) {
                        $firstPayment.prop('checked', true);
                        let gatewayData = $firstPayment.data('gateway');
                        let currency = (gatewayData == 'cod') ? window.defaultCurrency : gatewayData.currency;
                        $('#currencyField').val(currency);
                        
                        let totalAmount = window.updateTotals();
                        window.calculation(gatewayData, totalAmount);
                    }
                }
            });

            // Already handled by vanilla JS addEventListener above
            // No need for duplicate jQuery handler

        })(jQuery);
    </script>
    <script>
    const bdData = @json($locations);
    bdData.postcodes = @json(json_decode(file_get_contents(resource_path('data/bd-postcodes.json')), true)['postcodes']);

    $('#divisionSelect').on('change', function () {
    const divisionId = $(this).val();

    // reset
    $('#districtSelect').html('<option value="">Select District</option>');
    $('#areaSelect').html('<option value="">Select Area</option>');
    $('#postcodeInput').val('');

    const districts = bdData.districts.filter(d => d.division_id == divisionId);
    districts.forEach(d => $('#districtSelect').append(
        `<option value="${d.id}">${d.name}</option>`
    ));
});

$('#districtSelect').on('change', function () {
    const districtId = $(this).val();

    // reset
    $('#areaSelect').html('<option value="">Select Area</option>');
    $('#postcodeInput').val('');

    let areas;

    if (districtId === '1') {                                 // Dhaka City
        areas = bdData.dhaka.map(a => ({ name: a.name }));
    } else {
        areas = bdData.upazilas.filter(u => u.district_id == districtId);
    }

    areas.sort((a, b) => a.name.localeCompare(b.name));

    areas.forEach(a => $('#areaSelect').append(
        `<option value="${a.name}">${a.name}</option>`
    ));
});

$('#areaSelect').on('change', function () {
    const area     = $(this).val();
    const district = $('#districtSelect').val();

    // match either upazila or postOffice (case-insensitive)
    const matches = bdData.postcodes.filter(p =>
        p.district_id == district &&
        (p.upazila.toLowerCase()   === area.toLowerCase() ||
         p.postOffice.toLowerCase()=== area.toLowerCase())
    );

    if (matches.length === 1) {
        // single match – autofill readonly
        $('#postcodeInput')
            .val(matches[0].postCode)
            .prop('readonly', true);
    } else if (matches.length > 1) {
        // multiple – turn the input into a dropdown for user to pick
        let $select = $('<select>', {
            name: 'postcode',
            id:   'postcodeInput',
            class:'form-control',
            required: true
        }).append('<option value="">Select Postcode</option>');

        matches.forEach(m => $select.append(
            `<option value="${m.postCode}">${m.postOffice} (${m.postCode})</option>`
        ));

        $('#postcodeInput').replaceWith($select);
    } else {
        // no match – allow manual entry
        $('#postcodeInput')
            .replaceWith('<input type="text" name="postcode" id="postcodeInput" class="form-control" required>')
            .val('')
            .prop('readonly', false);
    }
});
</script>

    <script>
        (function($){
            'use strict';

            const csrf = '{{ csrf_token() }}';
            let searchTimer;

            $('#productSearchInput').on('input', function(){
                clearTimeout(searchTimer);
                const q = $(this).val().trim();

                $('#searchResults').empty();

                if (q.length < 2) return;

                searchTimer = setTimeout(function(){
                    $.get("{{ route('product.search') }}", { q: q }).done(function(res){
                        $('#searchResults').html(res.html);
                    }).fail(function(){
                        $('#searchResults').html('<div class="text-danger small">@lang("Search failed")</div>');
                    });
                }, 300);
            });

            $(document).on('click', '.search-add-to-cart', function(e){
                e.preventDefault();
                const id = $(this).data('id');
                const $btn = $(this);

                $btn.prop('disabled', true).text('@lang("Adding...")');

                $.post(`{{ route('cart.add', '') }}/${id}`, { _token: csrf, quantity: 1 })
                    .done(function(response){
                        if (response.status === 'success') {
                            notify('success', response.message);

                            if (typeof setPartialCart === 'function' && response.partialCartData) {
                                setPartialCart(response.partialCartData);
                            }

                            if (typeof setCartCount === 'function' && response.cartItemCount !== undefined) {
                                setCartCount(response.cartItemCount);
                            }

                            if (typeof setCartSubtotal === 'function' && response.cartSubtotal !== undefined) {
                                setCartSubtotal(response.cartSubtotal);
                            }

                            // Clear search box and results completely
                            $('#searchResults').empty().html('');
                            $('#productSearchInput').val('');

                            // Update mini cart / sidebar (existing helper)
                            if (typeof setPartialCart === 'function' && response.partialCartData) {
                                setPartialCart(response.partialCartData);
                            }

                            // If we're on the cart page, update the main cart items and totals without reloading
                            try {
                                // Replace cart items if the response contains them
                                if (response.partialCartData) {
                                    const $partial = $('<div>').html(response.partialCartData);
                                    // Fix: Look for cart items in the partial response
                                    let itemsHtml = $partial.find('.cart-items').html() || $partial.find('[data-cart-items]').html();
                                    if (!itemsHtml) {
                                        // If no wrapper found, use the whole partial as items
                                        itemsHtml = response.partialCartData;
                                    }
                                    if (itemsHtml && $('.cart-items').length) {
                                        $('.cart-items').html(itemsHtml);
                                        // Re-run lazy loader for newly injected images
                                        if (typeof lazyload === 'function') lazyload();
                                    }
                                }

                                // Update subtotal and related totals
                                let newSubtotal = parseFloat(response.cartSubtotal) || 0;

                                // Update global subtotal variable
                                window.subtotal = newSubtotal;

                                // Update visible elements
                                $('#subtotal').text(window.subtotal.toFixed(2));
                                $('#cartSubtotal').text(window.subtotal.toFixed(2));
                                $('.cartSubtotal').text(Math.abs(window.subtotal).toFixed(2));

                                // Recalculate totals (uses window.subtotal, window.currentShippingCharge, window.couponAmount)
                                if (typeof window.updateTotals === 'function') {
                                    window.updateTotals();
                                    // Also recalc payment processing if a gateway is checked
                                    if ($('[name="gateway"]:checked').length) {
                                        window.calculation($('[name="gateway"]:checked').data('gateway'), window.updateTotals());
                                    }
                                }

                            } catch (e) {
                                console.warn('Error updating cart UI:', e);
                            }

                        } else {
                            notify(response.status || 'error', response.message || '@lang("Unable to add to cart")');
                        }
                    })
                    .fail(function(xhr){
                        const msg = xhr.responseJSON?.message || '@lang("Something went wrong")';
                        notify('error', msg);
                    })
                    .always(function(){
                        $btn.prop('disabled', false).text('@lang("Add")');
                    });
            });

        })(jQuery);
    </script>

@endpush

@push('style')
    <style>
        /* Progress Steps */
        .checkout-progress {
            padding: 15px 0;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .step-label {
            font-size: 13px;
            color: #999;
        }

        .step.active .step-number {
            background-color: hsl(var(--primary));
        }

        .step.completed .step-number {
            background-color: hsl(var(--success));
        }

        .step.active .step-label,
        .step.completed .step-label {
            color: hsl(var(--primary));
            font-weight: 500;
        }

        /* Checkout Cards */
        .checkout-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .checkout-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            background-color: #f9f9f9;
        }

        .checkout-card-header .title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: hsl(var(--dark));
        }

        .checkout-card-body {
            padding: 20px;
        }

        /* Cart Items in Checkout */
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .cart-item-checkout {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px dashed #eee;
        }

        .cart-item-checkout:last-child {
            border-bottom: none;
        }

        .cart-item-img {
            width: 70px;
            height: 70px;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: hsl(var(--dark));
        }

        .cart-item-price {
            font-weight: 600;
            color: hsl(var(--primary));
        }

        .cart-item-qty {
            color: #666;
            font-size: 14px;
        }

        /* Shipping Methods */
        .shipping-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .shipping-method-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
        }

        .shipping-method-item:hover {
            border-color: hsl(var(--primary));
        }

        .shipping-method-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .shipping-method-name {
            font-weight: 500;
            margin-left: 10px;
        }

        .shipping-method-price {
            font-weight: 600;
            color: hsl(var(--primary));
        }

        .shipping-method-details {
            padding-left: 28px;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .detail-item i {
            margin-right: 8px;
            margin-top: 3px;
            color: hsl(var(--primary));
        }

        .shipping-method-item input[type="radio"]:checked~.shipping-method-details {
            display: block;
        }

        /* Payment Methods */
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .payment-method-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .payment-method-item:hover {
            border-color: hsl(var(--primary));
        }

        .payment-method-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            cursor: pointer;
        }

        .payment-method-name {
            font-weight: 500;
            margin-left: 10px;
        }

        .payment-method-icon {
            height: 25px;
            width: auto;
        }

        .payment-method-divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }

        .payment-method-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .payment-method-divider span {
            position: relative;
            z-index: 2;
            background: #fff;
            padding: 0 15px;
            color: #999;
            font-size: 13px;
        }

        .cod-method {
            background-color: rgba(var(--primary-rgb), 0.05);
        }

        /* Order Summary */
        .order-summary {
            font-size: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .summary-item.total {
            font-weight: 600;
            font-size: 16px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e0e0e0;
        }

        .summary-divider {
            height: 1px;
            background-color: #f0f0f0;
            margin: 10px 0;
        }

        .conversion-notice {
            font-size: 14px;
        }

        /* Checkout Button */
        .checkout-btn {
            font-weight: 600;
            font-size: 16px;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.2);
        }

        .secure-checkout-note {
            font-size: 13px;
            color: #666;
        }

        .secure-checkout-note i {
            color: hsl(var(--success));
        }

        /* Responsive Adjustments */
        @media (max-width: 991px) {
            .checkout-sidebar {
                margin-top: 30px;
            }
        }

        @media (max-width: 575px) {
            .checkout-card-header .title {
                font-size: 16px;
            }

            .checkout-card-body {
                padding: 15px;
            }
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 14px;
        }

        textarea.form-control {
            min-height: 100px;
        }

        /* Product Search Results */
        .search-results {
            max-height: 280px;
            overflow-y: auto;
        }

        .search-result-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Empty Cart */
        .empty-cart {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-cart-icon {
            font-size: 50px;
            color: #e0e0e0;
        }
    </style>
@endpush
