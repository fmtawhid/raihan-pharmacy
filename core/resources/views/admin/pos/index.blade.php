@extends('admin.layouts.app')

@section('panel')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ═════════════════════════════════════════════════
   POS — Bootstrap + Minimal Custom Styles
   ════════════════════════════════════════════════= */

/* Search Dropdown - Custom Only */
.search-input-wrapper {
  position: relative;
  width: 100%;
  z-index: 1001;
}

.search-suggestions {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #dee2e6;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  z-index: 1000;
  overflow: hidden;
  padding: 0;
  margin: 0;
  max-height: 450px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.search-suggestions.show {
  display: flex;
  flex-direction: column;
  animation: slideDown 0.15s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.search-results-header {
  background: #fff;
  padding: 0.625rem 1rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #495057;
}

.search-results-container {
  flex: 1;
  overflow-y: auto;
  padding: 0;
  min-height: 0;
  -webkit-overflow-scrolling: touch;
}

.suggestion-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background 0.15s ease;
  font-size: 0.8125rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  min-height: 55px;
  will-change: background;
}

.suggestion-item:hover {
  background: #f8f9fa;
}

.suggestion-item-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.suggestion-item strong {
  display: block;
  color: #212529;
  font-weight: 600;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.suggestion-item small {
  color: #6c757d;
  font-size: 0.6875rem;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.suggestion-item-price {
  flex-shrink: 0;
  background: linear-gradient(135deg, #e7f4f1, #d1f2e3);
  color: #0d6d42;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-weight: 700;
  font-size: 0.8125rem;
  white-space: nowrap;
  text-align: center;
  min-width: 50px;
}

.search-no-results {
  text-align: center;
  padding: 3.75rem 1.25rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.search-no-results i {
  font-size: 3rem;
  display: block;
  margin-bottom: 0.75rem;
  opacity: 0.35;
}

.search-no-results p {
  font-size: 0.875rem;
  font-weight: 500;
  margin: 0;
}

/* Icons in headers */
.ct-icon {
  width: 28px;
  height: 28px;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.ct-icon--search {
  background: #e7f4f1;
  color: #0d6d42;
}

.ct-icon--customer {
  background: #fef3c7;
  color: #ca8a04;
}

.ct-icon--cart {
  background: #dcfce7;
  color: #16a34a;
}

/* Quantity pill */
.qty-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 26px;
  height: 24px;
  background: #e7f4f1;
  color: #0d6d42;
  border-radius: 0.375rem;
  font-size: 0.6875rem;
  font-weight: 600;
}

/* Quantity buttons */
.qty-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 0.3125rem;
  border: 1.5px solid #dee2e6;
  background: #f8f9fa;
  color: #212529;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
  font-family: inherit;
}

.qty-btn:hover {
  background: #dcfce7;
  border-color: #16a34a;
  color: #16a34a;
}

.qty-btn:active {
  transform: scale(0.95);
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Products grid  */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(95px, 1fr));
  gap: 0.5rem;
  margin-top: 0.625rem;
}

.product-item {
  background: #f8f9fa;
  border: 1.5px solid #dee2e6;
  border-radius: 0.5rem;
  padding: 0.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.product-item:hover {
  border-color: #0d6d42;
  background: #dcfce7;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
}

.product-item-image {
  width: 100%;
  height: 60px;
  background: #dee2e6;
  border-radius: 0.375rem;
  margin-bottom: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #6c757d;
}

.product-item-name {
  font-size: 0.625rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.25rem;
  line-height: 1.2;
  min-height: 20px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-item-price {
  font-size: 0.625rem;
  color: #16a34a;
  font-weight: 700;
  margin-bottom: 0.375rem;
}

.product-item-btn {
  width: 100%;
  padding: 0.25rem 0.375rem;
  font-size: 0.5625rem;
  border: none;
  border-radius: 0.25rem;
  background: #0d6d42;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.product-item-btn:hover {
  background: #0a5835;
}

.product-item-btn:active {
  transform: scale(0.95);
}

.pos-products-section {
  display: none !important;
}

/* No data message */
.no-data-msg {
  text-align: center;
  padding: 1.25rem;
  color: #6c757d;
  font-size: 0.8125rem;
}

.no-data-msg i {
  font-size: 1.625rem;
  display: block;
  margin-bottom: 0.375rem;
  opacity: 0.45;
}

/* Remove spinner arrows from number inputs */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>

<div class="p-3">

  {{-- ═══ MAIN LAYOUT GRID ═══ --}}
  <div class="row g-3">

    {{-- ═══ LEFT: Search + Auto-loaded Products ═══ --}}
    <div class="col-12 pos-products-section">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="d-flex align-items-center gap-2 small fw-600">
            <span class="ct-icon ct-icon--search"><i class="la la-search"></i></span>
            Browse Products
          </span>
          <span class="badge bg-light text-dark">18</span>
        </div>
        <div class="card-body p-2">
          <div id="products-container" class="products-grid"></div>
          <div id="search-placeholder" class="no-data-msg" style="display:none"><i class="la la-search"></i> No products found</div>
        </div>
      </div>
    </div>

    {{-- ═══ RIGHT: SHOPPING CART ═══ --}}
    <div class="col-12">
      <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
          <div class="d-flex align-items-center gap-2 small fw-600 mb-2">
            <span class="ct-icon ct-icon--cart"><i class="la la-shopping-cart"></i></span>
            Cart
          </div>

          <div class="search-input-wrapper">
            <i class="la la-search pi-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
            <input type="text" id="product-search" placeholder="Search products…" class="form-control form-control-sm" style="padding-left: 36px;">
            <!-- Search Results Dropdown -->
            <div id="search-suggestions" class="search-suggestions">
              <div class="search-results-header">
                <i class="la la-search"></i>
                <span>Search Results</span>
              </div>
              <div id="search-results-container" class="search-results-container"></div>
            </div>
          </div>
        </div>

        <div class="card-body p-2 border-bottom d-flex gap-2 flex-wrap">
          <button id="select-customer-btn" class="btn btn-sm btn-success flex-grow-1" style="min-width: 100px;">
            <i class="la la-user"></i> Select
          </button>
          <button id="create-customer-btn" class="btn btn-sm btn-info flex-grow-1" style="min-width: 100px;">
            <i class="la la-user-plus"></i> Create
          </button>
          <button id="clear-cart" class="btn btn-sm btn-outline-danger flex-grow-1" style="min-width: 60px;">
            <i class="la la-trash"></i> Clear
          </button>
        </div>
        <div id="selected-customer" class="p-2 border-bottom d-none flex-shrink-0"></div>

        <div class="card-body p-2 flex-grow-1" style="overflow-y: auto; min-height: 0;">
          <table class="table table-sm table-hover mb-0" id="cart-table" style="table-layout: fixed;">
            <thead class="table-light">
              <tr style="font-size: 0.75rem;">
                <th style="width: 40%;">Product</th>
                <th style="width: 20%; text-align:center;">Qty</th>
                <th style="width: 10%; text-align:center; font-size: 0.7rem;">Stock</th>
                <th style="width: 12%; text-align:right;">Price</th>
                <th style="width: 12%; text-align:right;">W</th>
                <th style="width: 16%; text-align:right;">Total</th>
                <th style="width: 10%; text-align:center;">×</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

                <div class="card-body p-2 border-top">

          <div class="row g-3 align-items-stretch">

            <!-- Price Type -->
            <div class="col-md-4">
              <div style="background: linear-gradient(135deg, #e7f4f1, #f0fdf9); border: 1.5px solid #6ee7b7; border-radius: 0.75rem; padding: 1.25rem; height: 100%; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.06);">
                <label class="form-label small fw-700 mb-3 d-block" style="color: #0d5f42; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="la la-tag" style="margin-right: 0.375rem; font-size: 1rem;"></i>Price Type
                </label>
                <div class="d-flex gap-3 flex-column">
                  <div class="form-check">
                    <input type="radio" name="price_type" id="pt_regular" value="regular" class="form-check-input" style="width: 1.1rem; height: 1.1rem; margin-top: 0.2rem;">
                    <label for="pt_regular" class="form-check-label" style="cursor: pointer; font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-left: 0.5rem;">
                      <i class="la la-tag" style="color: #059669; margin-right: 0.5rem;"></i>Regular
                    </label>
                  </div>
                  <div class="form-check">
                    <input type="radio" name="price_type" id="pt_wholesale" value="wholesale" class="form-check-input" style="width: 1.1rem; height: 1.1rem; margin-top: 0.2rem;">
                    <label for="pt_wholesale" class="form-check-label" style="cursor: pointer; font-weight: 600; color: #1e293b; font-size: 0.9rem; margin-left: 0.5rem;">
                      <i class="la la-boxes" style="color: #f0b83d; margin-right: 0.5rem;"></i>Wholesale
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Discount -->
            <div class="col-md-4">
              <div style="background: linear-gradient(135deg, #fef3c7, #fefce8); border: 1.5px solid #fcd34d; border-radius: 0.75rem; padding: 1.25rem; height: 100%; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.06);">
                <div class="small fw-700 text-dark mb-3" style="color: #b45309; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="la la-percent" style="margin-right: 0.375rem; font-size: 1rem;"></i>Discount
                </div>
                <div class="d-flex gap-3 mb-3 pb-3 border-bottom" style="border-color: #fcd34d !important;">
                  <div class="form-check">
                    <input type="radio" name="discount_type" id="dt_percentage" value="percentage" class="form-check-input" style="width: 1.1rem; height: 1.1rem; margin-top: 0.2rem;">
                    <label for="dt_percentage" class="form-check-label small" style="cursor: pointer; font-weight: 600; color: #1e293b; margin-left: 0.5rem;">Percent %</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" name="discount_type" id="dt_fixed" value="fixed" class="form-check-input" style="width: 1.1rem; height: 1.1rem; margin-top: 0.2rem;">
                    <label for="dt_fixed" class="form-check-label small" style="cursor: pointer; font-weight: 600; color: #1e293b; margin-left: 0.5rem;">Fixed Amount</label>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <input type="number" id="discount-input" placeholder="0.00" min="0" step="0.01" class="form-control form-control-sm" disabled style="border: 1.5px solid #fcd34d; font-weight: 600; font-size: 0.9rem; background: white;">
                  <span id="discount-unit" class="input-group-text small fw-700" style="min-width: 50px; text-align: center; background: white; border: 1.5px solid #fcd34d; color: #b45309; font-size: 0.9rem; border-radius: 0.375rem;">%</span>
                </div>
              </div>
            </div>

            <!-- Cart Total -->
            <div class="col-md-4">
              <div style="background: linear-gradient(135deg, #f0fdf9, #ccfbf1); border: 2px solid #5eead4; border-radius: 0.75rem; padding: 1.5rem; height: 100%; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 20px rgba(5, 150, 105, 0.12);">
                
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span style="color: #1e293b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Subtotal</span>
                    <span style="color: #059669; font-size: 1rem; font-weight: 800;" id="cart-subtotal">৳0.00</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span style="color: #1e293b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Items</span>
                    <span style="color: #059669; font-size: 1rem; font-weight: 800;" id="cart-items">0</span>
                  </div>
                  
                  <div id="cart-discount-row" class="d-flex justify-content-between align-items-start mb-3" style="display: none; padding: 0.75rem; background: rgba(153, 27, 27, 0.1); border-radius: 0.5rem; border-left: 3px solid #dc2626;">
                    <span style="color: #1e293b; font-size: 0.8rem; font-weight: 700;">Discount</span>
                    <div style="text-align: right;">
                      <div style="color: #991b1b; font-size: 0.95rem; font-weight: 800; line-height: 1.2;" id="cart-discount">-৳0.00</div>
                    </div>
                  </div>
                </div>
                
                <div>
                  <div class="border-top" style="border-color: #5eead4; margin: 1rem 0;"></div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span style="color: #0d5f42; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total</span>
                    <span style="color: #059669; font-size: 1.5rem; font-weight: 900; letter-spacing: 0.5px;" id="cart-grand-total">৳0.00</span>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Button -->
          <div class="mt-4">
            <button id="confirm-order" class="btn btn-success w-100" disabled style="opacity: .45; cursor: not-allowed; font-size: 0.95rem; padding: 0.9rem 1.25rem; font-weight: 700; letter-spacing: 0.3px; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
              <i class="la la-check-circle" style="margin-right: 0.5rem;"></i> Confirm Order
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<!-- Create Customer Modal -->
<div id="create-customer-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Customer</h5>
        <button type="button" class="btn-close" onclick="closeCreateCustomerModal()" aria-label="Close"></button>
      </div>
      <form id="create-customer-form">
        <div class="modal-body">
          <div class="mb-3">
            <label for="cc-name" class="form-label">Full Name *</label>
            <input type="text" class="form-control" id="cc-name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="cc-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="cc-email" name="email">
          </div>
          <div class="mb-3">
            <label for="cc-phone" class="form-label">Phone Number (Optional)</label>
            <input type="tel" class="form-control" id="cc-phone" name="mobile">
          </div>
          <div class="mb-3">
            <label for="cc-address" class="form-label">Address</label>
            <textarea class="form-control" id="cc-address" name="address" rows="2" placeholder="Enter full address"></textarea>
          </div>
          <div class="alert alert-info small mb-0">
            <i class="la la-info-circle"></i> <strong>Note:</strong> Login password will be set to the phone number.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateCustomerModal()">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Create Customer</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


@push('script')
<!-- SweetAlert2 & Toastr -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
toastr.options = {
  closeButton: true,
  newestOnTop: true,
  progressBar: true,
  positionClass: "toast-top-right",
  timeOut: "4000",
  showMethod: "fadeIn",
  hideMethod: "fadeOut"
};


$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': '{{ csrf_token() }}'
  }
});

$(document).ready(function() {

  // ═══ INSTANT FRONTEND CART (Session-free) ═══
  // Cart stored in memory, updated instantly, saved only on confirm
  let cart = {}; // Local cart object
  let productCache = {}; // Cache all products for instant access
  let selectedPriceType = null;
  let discountType = null;
  let discountAmount = 0;

  // Load initial cart from session (one-time only)
  function initializeCart() {
    $.get('{{ route("admin.pos.getCart") }}', function(res) {
      cart = res.cart || {};
      renderCart(cart, null);
    }).fail(() => {
      console.log('Cart initialized empty');
      cart = {};
    });
  }

  // Load products and customer on page load
  function loadInitialData() {
    loadProducts();
    $.get('{{ route("admin.pos.getCustomer") }}').done(function(res) {
      renderSelectedCustomer(res.customer);
    });
  }

  // Initialize on page load
  initializeCart();
  loadInitialData();

  // ── Helper function to add product from search ──────
  function addToCartFromSearch(productId) {
    // Validate product exists
    if (!productCache[productId]) {
      console.error('Product not found in cache:', productId);
      toastr.error('Product not found');
      return false;
    }

    let p = productCache[productId];
    
    if (cart[productId]) {
      // Product already in cart - increment
      cart[productId].quantity++;
      toastr.info('Quantity increased!');
    } else {
      // Add new product to cart
      cart[productId] = {
        id: productId,
        name: p.name,
        brand_name: p.brand_name ?? null,
        price: p.sale_price ?? p.regular_price ?? 0,
        wholesale_price: p.wholesale_price ?? null,
        stock: p.in_stock ?? 0,
        quantity: 1,
        _addedAt: Date.now()
      };
      toastr.success(`"${p.name}" added to cart!`);
    }
    
    // Re-render cart with current price type
    renderCart(cart, selectedPriceType);
    return true;
  }

  // ── Render search suggestions overlay ────────────
  function renderSearchSuggestions(products) {
    let html = '';
    
    if (!products || products.length === 0) {
      html = `<div class="search-no-results" style="padding: 30px 20px; text-align: center;">
        <i class="la la-search" style="font-size: 32px; color: #6c757d; display: block; margin-bottom: 8px; opacity: .5;"></i>
        <p style="color: #6c757d; font-size: 13px; margin: 0;">No products found</p>
      </div>`;
    } else {
      // Render all results without limiting - backend already limits to 120
      products.forEach(function(p) {
        let price = p.sale_price ?? p.regular_price ?? 0;
        html += `<div class="suggestion-item" data-product-id="${p.id}" role="button" tabindex="0">
          <div class="suggestion-item-info">
            <strong>${p.name}</strong>
            <small>${p.sku ? 'SKU: ' + p.sku : 'Product'}${p.in_stock ? ' • Stock: ' + p.in_stock : ''}</small>
          </div>
          <div class="suggestion-item-price">৳${Number(price).toFixed(2)}</div>
        </div>`;
      });
      
      // Show indicator if more results available (from backend)
      if (products.length >= 120) {
        html += `<div class="text-center text-muted small p-2" style="border-top: 1px solid #dee2e6; font-size: 0.75rem;">Max 120 results shown. Type more to filter.</div>`;
      }
    }
    
    $('#search-results-container').html(html);
    $('#search-suggestions').addClass('show');
  }

  // ── Open create customer modal ────────────────────
  function openCreateCustomerModal() {
    let modal = new bootstrap.Modal(document.getElementById('create-customer-modal'));
    modal.show();
  }

  // ── Close create customer modal ───────────────────
  function closeCreateCustomerModal() {
    let modal = bootstrap.Modal.getInstance(document.getElementById('create-customer-modal'));
    if (modal) modal.hide();
    $('#create-customer-form')[0].reset();
  }

  // ── Create customer button click ──────────────────
  $('#create-customer-btn').on('click', function() {
    openCreateCustomerModal();
  });

  // ── Handle create customer form submission ────────
  $('#create-customer-form').on('submit', function(e) {
    e.preventDefault();
    let $btn = $(this).find('button[type="submit"]');
    let originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Creating...');
    
    let formData = {
      name: $('#cc-name').val(),
      email: $('#cc-email').val() || null,
      mobile: $('#cc-phone').val() || null,
      address: $('#cc-address').val() || null
    };

    $.post('{{ route("admin.pos.createCustomer") }}', formData)
      .done(function(res) {
        if (res.customer) {
          toastr.success('Customer created and selected!');
          renderSelectedCustomer(res.customer);
          closeCreateCustomerModal();
          $btn.prop('disabled', false).html(originalText);
        } else {
          toastr.error(res.message || 'Failed to create customer');
          $btn.prop('disabled', false).html(originalText);
        }
      })
      .fail(function(xhr) {
        let msg = 'Failed to create customer';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        toastr.error(msg);
        $btn.prop('disabled', false).html(originalText);
      });
  });

  // ── Close modal when clicking backdrop ────────────
  // (Bootstrap handles this automatically with data-bs-dismiss)

  $('input[name="price_type"]').on('change', function () {
    selectedPriceType = $(this).val();
    // Enable confirm button with dark gradient
    $('#confirm-order').prop('disabled', false).css({ 
      opacity: 1, 
      cursor: 'pointer',
      background: 'linear-gradient(135deg, #059669, #047857)',
      boxShadow: '0 4px 12px rgba(5, 150, 105, .25)'
    });
    // Re-render totals with selected price type (instant, no AJAX)
    renderCart(cart, selectedPriceType);
  });

  // ── Discount type selector ──────────────────────
  $('input[name="discount_type"]').on('change', function() {
    discountType = $(this).val();
    $('#discount-input').prop('disabled', false);
    $('#discount-unit').text(discountType === 'percentage' ? '%' : '৳');
    $('#discount-input').val('0');
    discountAmount = 0;
    updateDiscountDisplay();
  });

  // ── Discount amount input ───────────────────────
  $('#discount-input').on('input', function() {
    discountAmount = parseFloat($(this).val()) || 0;
    updateDiscountDisplay();
  });

  function loadProducts() {
    $.get('{{ route("admin.pos.getProducts") }}', {
      page: 1,
      per_page: 18
    }).done(function(res) {
      // Cache all product data for instant access on add-to-cart
      res.products.forEach(function(p) {
        productCache[p.id] = p;
      });
      renderProductsGrid(res.products);
    }).fail(function() {
      toastr.error('Failed to load products');
    });
  }

  // ── Product search (INSTANT - Fast like add_stock) ──────────────────────────────
  let searchRequestTimeout;
  let lastSearchQuery = null;
  let lastSearchRequestId = 0;
  let searchCache = {}; // Cache search results by query
  
  $('#product-search').on('input', function() {
    clearTimeout(searchRequestTimeout);
    let query = $(this).val().trim().toLowerCase();
    
    // Hide suggestions if search is empty
    if (!query) {
      $('#search-suggestions').removeClass('show');
      lastSearchQuery = null;
      return;
    }

    // Track this search request with unique ID
    lastSearchQuery = query;
    let currentRequestId = ++lastSearchRequestId;
    
    // ══ INSTANT RENDER FROM LOCAL CACHE (0ms - NO DEBOUNCE) ══
    // Filter from already loaded products in real-time (FAST)
    let cachedResults = [];
    for (let id in productCache) {
      let p = productCache[id];
      if (p.name.toLowerCase().includes(query) || 
          (p.sku && p.sku.toLowerCase().includes(query))) {
        cachedResults.push(p);
      }
    }
    
    // Render results instantly
    let html = '';
    if (cachedResults.length === 0) {
      html = `<div class="search-no-results" style="padding: 30px 20px; text-align: center;">
        <i class="la la-search" style="font-size: 32px; color: #6c757d; display: block; margin-bottom: 8px; opacity: .5;"></i>
        <p style="color: #6c757d; font-size: 13px; margin: 0;">No products found</p>
      </div>`;
    } else {
      cachedResults.forEach(function(p) {
        let price = p.sale_price ?? p.regular_price ?? 0;
        let brandDisplay = p.brand_name ? `<small>${p.brand_name}</small><br>` : '';
        html += `<div class="suggestion-item" data-product-id="${p.id}" role="button" tabindex="0">
          <div class="suggestion-item-info">
            <strong>${p.name}</strong>
            <small>${brandDisplay}SKU: ${p.sku || 'N/A'} • Stock: ${p.in_stock || 0}</small>
          </div>
          <div class="suggestion-item-price">৳${Number(price).toFixed(2)}</div>
        </div>`;
      });
      
      if (cachedResults.length >= 120) {
        html += `<div class="text-center text-muted small p-2" style="border-top: 1px solid #dee2e6; font-size: 0.75rem;">Max 120 results shown. Type more to filter.</div>`;
      }
    }
    
    $('#search-results-container').html(html);
    $('#search-suggestions').addClass('show');
    
    // ══ CHECK SEARCH RESULT CACHE (instant return if found) ══
    if (searchCache[query]) {
      console.log('✓ Cache HIT for query:', query, '- Results:', searchCache[query].length, '- Time: 0ms');
      // Render backend cached results (which are always more complete than local cache)
      html = '';
      searchCache[query].forEach(function(p) {
        let price = p.sale_price ?? p.regular_price ?? 0;
        let brandDisplay = p.brand_name ? `<small>${p.brand_name}</small><br>` : '';
        html += `<div class="suggestion-item" data-product-id="${p.id}" role="button" tabindex="0">
          <div class="suggestion-item-info">
            <strong>${p.name}</strong>
            <small>${brandDisplay}SKU: ${p.sku || 'N/A'} • Stock: ${p.in_stock || 0}</small>
          </div>
          <div class="suggestion-item-price">৳${Number(price).toFixed(2)}</div>
        </div>`;
      });
      $('#search-results-container').html(html);
      return;  // ← IMPORTANT: Do NOT make backend request
    }
    
    console.log('✗ Cache MISS for query:', query, '- Making backend request...');
    
    // ══ BACKEND SEARCH (minimal 200ms delay to batch requests) ══
    searchRequestTimeout = setTimeout(function() {
      // Check if this request is still valid
      if (lastSearchQuery !== query || lastSearchRequestId !== currentRequestId) {
        return;
      }
      
      // Only show loading if cache results are fewer than 5 items
      if (cachedResults.length < 5) {
        $('#search-results-container').html(`<div style="padding: 20px; text-align: center; color: #6c757d;"><i class="la la-spinner fa-spin" style="font-size: 20px; margin-right: 8px;"></i> Searching...</div>`);
      }
      
      $.get('{{ route("admin.pos.searchProducts") }}', { query })
        .done(function(data) {
          // Only render if this is still the latest request
          if (lastSearchRequestId === currentRequestId) {
            // ✓ Store in cache for future searches
            searchCache[query] = data || [];
            
            // Cache searched products to product cache too
            if (data && data.length > 0) {
              data.forEach(function(p) {
                productCache[p.id] = p;
              });
            }
            
            // Render backend results instantly
            let html = '';
            if (!data || data.length === 0) {
              html = `<div class="search-no-results" style="padding: 30px 20px; text-align: center;">
                <i class="la la-search" style="font-size: 32px; color: #6c757d; display: block; margin-bottom: 8px; opacity: .5;"></i>
                <p style="color: #6c757d; font-size: 13px; margin: 0;">No products found</p>
              </div>`;
            } else {
              data.forEach(function(p) {
                let price = p.sale_price ?? p.regular_price ?? 0;
                let brandDisplay = p.brand_name ? `<small>${p.brand_name}</small><br>` : '';
                html += `<div class="suggestion-item" data-product-id="${p.id}" role="button" tabindex="0">
                  <div class="suggestion-item-info">
                    <strong>${p.name}</strong>
                    <small>${brandDisplay}SKU: ${p.sku || 'N/A'} • Stock: ${p.in_stock || 0}</small>
                  </div>
                  <div class="suggestion-item-price">৳${Number(price).toFixed(2)}</div>
                </div>`;
              });
            }
            $('#search-results-container').html(html);
          }
        })
        .fail(function(xhr) {
          // Ignore errors for stale requests
          if (lastSearchRequestId === currentRequestId) {
            toastr.error('Search failed');
          }
        });
    }, 100); // Reduced to 100ms for faster response
  });

  // ── Close search on escape key ──────────────────────────
  let selectedSuggestionIndex = -1;
  
  $('#product-search').on('keydown', function(e) {
    let $items = $('#search-results-container .suggestion-item');
    
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, $items.length - 1);
      highlightSuggestion();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
      highlightSuggestion();
    } else if (e.key === 'Enter' && selectedSuggestionIndex >= 0) {
      e.preventDefault();
      $items.eq(selectedSuggestionIndex).click();
    }
  });
  
  function highlightSuggestion() {
    let $items = $('#search-results-container .suggestion-item');
    $items.removeClass('highlighted').css('background', '');
    
    if (selectedSuggestionIndex >= 0 && selectedSuggestionIndex < $items.length) {
      $items.eq(selectedSuggestionIndex)
        .addClass('highlighted')
        .css('background', '#f0f0f0')
        .scrollIntoView({ block: 'nearest' });
    }
  }
  
  // Reset selection when typing new query
  $('#product-search').on('input', function() {
    selectedSuggestionIndex = -1;
  });
  
  // ── Close search on escape key ──────────────────────────
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#search-suggestions').hasClass('show')) {
      e.preventDefault();
      $('#product-search').val('').focus();
      $('#search-suggestions').removeClass('show');
    }
  });

  // ── Close search overlay on outside click ────────
  $(document).on('click', function(e) {
    // If click is outside search wrapper, close dropdown
    if (!$(e.target).closest('.search-input-wrapper').length && 
        $('#search-suggestions').hasClass('show')) {
      $('#search-suggestions').removeClass('show');
    }
  });

  // ── Click on suggestion to add to cart ───────────────────────
  $(document).on('click', '.suggestion-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    let productId = $(this).data('product-id');
    
    // Verify product exists in cache
    if (!productCache[productId]) {
      toastr.error('Product not available');
      return;
    }
    
    // Add to cart
    addToCartFromSearch(productId);
    
    // Clear search immediately
    $('#product-search').val('').focus();
    $('#search-suggestions').removeClass('show');
  });

  // ── ADD TO CART (Instant - Uses Cached Frontend Data) ────────
  $(document).on('click', '.product-item', function() {
    let $item = $(this),
      id = $item.data('id');
    let $btn = $item.find('.product-item-btn');
    
    // If already in cart, just increment
    if (cart[id]) {
      cart[id].quantity++;
      renderCart(cart, selectedPriceType);
      toastr.success('Quantity increased!');
      return;
    }

    // Use cached product data (instant - no backend call)
    if (productCache[id]) {
      let p = productCache[id];
      cart[id] = {
        id: id,
        name: p.name,
        brand_name: p.brand_name ?? null,
        price: p.sale_price ?? p.regular_price ?? 0,
        wholesale_price: p.wholesale_price ?? null,
        stock: p.in_stock ?? 0,
        quantity: 1,
        _addedAt: Date.now() // Track insertion order
      };
      renderCart(cart, selectedPriceType);
      toastr.success(`"${p.name}" added to cart!`);
    } else {
      toastr.error('Product not found in cache');
    }
    
    $('#product-search').val(''); // Clear search bar after adding
  });

  // ── REMOVE FROM CART (Instant) ──────────────────
  $(document).on('click', '.remove-from-cart', function() {
    let $btn = $(this),
      id = $btn.data('id');
    let productName = $btn.closest('tr').find('td:first').text();
    
    // Remove instantly from local cart
    delete cart[id];
    
    // Render and notify
    renderCart(cart, selectedPriceType);
    toastr.info(`"${productName}" removed from cart`);
  });

  // ── INCREMENT QUANTITY (Instant) ─────────────────
  $(document).on('click', '.qty-plus', function() {
    let id = $(this).data('id');
    if (cart[id]) {
      cart[id].quantity++;
      let $input = $(`tr[data-product-id="${id}"] .qty-input`);
      $input.val(cart[id].quantity);
      updateCartRowTotal(id);
      updateCartTotalOnly();
    }
  });

  // ── DECREMENT QUANTITY (Instant) ─────────────────
  $(document).on('click', '.qty-minus', function() {
    let id = $(this).data('id');
    if (cart[id]) {
      if (cart[id].quantity > 1) {
        cart[id].quantity--;
        let $input = $(`tr[data-product-id="${id}"] .qty-input`);
        $input.val(cart[id].quantity);
        updateCartRowTotal(id);
        updateCartTotalOnly();
      } else {
        delete cart[id];
        $(`tr[data-product-id="${id}"]`).fadeOut(200, function() {
          $(this).remove();
          updateCartTotalOnly();
        });
      }
    }
  });

  // ── DIRECT QUANTITY INPUT (INSTANT - NO CELL RE-RENDER) ──────────────────
  $(document).on('input', '.qty-input', function() {
    let $input = $(this),
      id = $input.data('id'),
      newQty = parseInt($(this).val()) || 0;
    
    // Allow empty input while typing
    if (!$(this).val() || $(this).val() === '') return;
    
    if (newQty < 1) return;
    if (newQty > 9999) {
      $(this).val(9999);
      newQty = 9999;
    }
    
    if (cart[id]) {
      cart[id].quantity = newQty;
      updateCartCostOnly(id);
      updateCartTotalOnly();
    }
  });

  // ── EDITABLE PRICE INPUT ──────────────────────
  $(document).on('input', '.price-input', function() {
    let $input = $(this),
      id = $input.data('id'),
      newPrice = parseFloat($(this).val()) || 0;
    
    if (newPrice < 0) return;
    
    if (cart[id]) {
      cart[id].price = newPrice;
      updateCartCostOnly(id);
      updateCartTotalOnly();
    }
  });

  $(document).on('change', '.price-input', function() {
    let newPrice = parseFloat($(this).val()) || 0;
    if (newPrice < 0) {
      $(this).val('0');
    }
  });

  // ── EDITABLE WHOLESALE PRICE INPUT ─────────────
  $(document).on('input', '.wholesale-price-input', function() {
    let $input = $(this),
      id = $input.data('id'),
      newPrice = parseFloat($(this).val()) || 0;
    
    if (newPrice < 0) return;
    
    if (cart[id]) {
      cart[id].wholesale_price = newPrice;
      updateCartCostOnly(id);
      updateCartTotalOnly();
    }
  });

  $(document).on('change', '.wholesale-price-input', function() {
    let newPrice = parseFloat($(this).val()) || 0;
    if (newPrice < 0) {
      $(this).val('0');
    }
  });

  // ── UPDATE ONLY COST CELL (NO QTY INPUT RE-RENDER) ──────────────────
  function updateCartCostOnly(productId) {
    let item = cart[productId];
    if (!item) return;
    
    let useWholesale = (selectedPriceType === 'wholesale') && item.wholesale_price;
    let unitPrice = useWholesale ? Number(item.wholesale_price) : Number(item.price);
    let itemTotal = unitPrice * item.quantity;
    
    let $row = $(`tr[data-product-id="${productId}"]`);
    let $totalCell = $row.find('td').eq(5);
    
    // Update ONLY total cost cell - DON'T touch qty input
    let wholesaleLabel = useWholesale ? '<sup style="font-size:9px;background:#0d5f42;color:#f0b83d;padding:2px 4px;border-radius:2px;margin-right:2px;font-weight:700">W</sup>' : '';
    $totalCell.html('<strong style="color:#16a34a">' + wholesaleLabel + '৳' + itemTotal.toFixed(2) + '</strong>');
  }

  // ── UPDATE CART ROW TOTAL (ONLY ON BLUR/CHANGE) ──────────────────
  function updateCartRowTotal(productId) {
    updateCartCostOnly(productId);
  }

  // ── DIRECT QUANTITY INPUT ON BLUR (FINAL VALIDATION) ──────────────────
  $(document).on('blur', '.qty-input', function() {
    let $input = $(this),
      id = $input.data('id'),
      newQty = parseInt($input.val()) || 1;
    
    // Validate on blur
    if (newQty < 1) newQty = 1;
    if (newQty > 9999) newQty = 9999;
    
    $input.val(newQty);
    
    if (cart[id]) {
      cart[id].quantity = newQty;
      updateCartCostOnly(id);
      updateCartTotalOnly();
    }
  });

  // ── DIRECT QUANTITY INPUT (Display only - Update on Order Confirm) ──────────────
  $(document).on('change', '.qty-input', function() {
    // This is handled by blur event now, so just skip
    return;
  });

  // ── Calculate subtotal from cart object ─────────
  function calculateCartSubtotal() {
    let subtotal = 0;
    for (let id in cart) {
      let item = cart[id];
      let useWholesale = (selectedPriceType === 'wholesale') && item.wholesale_price;
      let unitPrice = useWholesale ? Number(item.wholesale_price) : Number(item.price);
      let itemTotal = unitPrice * item.quantity;
      subtotal += itemTotal;
    }
    return subtotal;
  }

  // ── Calculate total items in cart ──────────────
  function calculateTotalItems() {
    let totalItems = 0;
    for (let id in cart) {
      totalItems += cart[id].quantity;
    }
    return totalItems;
  }

  // ── Update cart total only (no full re-render) ──
  function updateCartTotalOnly() {
    let cartSubtotal = calculateCartSubtotal();
    let totalItems = calculateTotalItems();
    
    let actualDiscount = 0;
    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (cartSubtotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, cartSubtotal);
    }
    
    let finalTotal = cartSubtotal - actualDiscount;
    
    // Update subtotal
    $('#cart-subtotal').text('৳' + cartSubtotal.toFixed(2));
    
    // Update total items
    $('#cart-items').text(totalItems);
    
    // Update discount row (show only if there's a discount)
    if (actualDiscount > 0) {
      let discountLabel = discountType === 'percentage' ? `${discountAmount.toFixed(2)}% OFF` : 'Fixed Amount';
      $('#cart-discount').html('-৳' + actualDiscount.toFixed(2) + '<br><small style="opacity: 0.8; font-size: 0.75rem;">' + discountLabel + '</small>');
      $('#cart-discount-row').show();
    } else {
      $('#cart-discount-row').hide();
    }
    
    // Update grand total
    $('#cart-grand-total').text('৳' + finalTotal.toFixed(2));
  }

  // ── Update discount display ────────────────────
  function updateDiscountDisplay() {
    let cartTotal = calculateCartSubtotal();
    let actualDiscount = 0;
    
    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (cartTotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, cartTotal);
    }
    
    $('#discount-display').text('৳' + actualDiscount.toFixed(2));
    updateCartTotal(); // Update cart total with discount
  }

  // ── Update cart total with discount ─────────────
  function updateCartTotal() {
    let cartSubtotal = calculateCartSubtotal();
    let totalItems = calculateTotalItems();
    let actualDiscount = 0;
    
    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (cartSubtotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, cartSubtotal);
    }
    
    let finalTotal = cartSubtotal - actualDiscount;
    
    // Update subtotal
    $('#cart-subtotal').text('৳' + cartSubtotal.toFixed(2));
    
    // Update total items
    $('#cart-items').text(totalItems);
    
    // Update discount row (show only if there's a discount)
    if (actualDiscount > 0) {
      let discountLabel = discountType === 'percentage' ? `${discountAmount.toFixed(2)}% OFF` : 'Fixed Amount';
      $('#cart-discount').html('-৳' + actualDiscount.toFixed(2) + '<br><small style="opacity: 0.8; font-size: 0.75rem;">' + discountLabel + '</small>');
      $('#cart-discount-row').show();
    } else {
      $('#cart-discount-row').hide();
    }
    
    // Update grand total
    $('#cart-grand-total').text('৳' + finalTotal.toFixed(2));
  }

  // ── Get current cart total (from cart object, not DOM) ──
  function getCartTotal() {
    return calculateCartSubtotal();
  }

  // ── CLEAR CART (Instant) ────────────────────────
  $(document).on('click', '#clear-cart', function() {
    Swal.fire({
      title: 'Clear Cart?',
      text: 'Are you sure you want to clear the entire shopping cart?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6366f1',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: '<i class="la la-trash"></i> Yes, Clear It!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // Clear local cart instantly
        cart = {};
        selectedPriceType = null;
        discountType = null;
        discountAmount = 0;
        $('input[name="price_type"]').prop('checked', false);
        $('input[name="discount_type"]').prop('checked', false);
        $('#discount-input').val('0').prop('disabled', true);
        updateDiscountDisplay();
        $('#confirm-order').prop('disabled', true).css({ 
          opacity: .45, 
          cursor: 'not-allowed',
          background: 'linear-gradient(135deg, #404854, #2c323d)',
          boxShadow: 'none'
        });
        renderCart(cart, null);
        toastr.success('Cart cleared successfully!');
      }
    });
  });

  // ── SERIALIZE CART (Convert to Invoice-like Format) ──────────────
  function serializeCart(cartObj) {
    let serialized = [];
    for (let productId in cartObj) {
      let item = cartObj[productId];
      serialized.push({
        product_id: parseInt(productId),
        name: item.name,
        sku: item.sku || null,
        price: parseFloat(item.price) || 0,
        wholesale_price: item.wholesale_price ? parseFloat(item.wholesale_price) : null,
        quantity: parseInt(item.quantity) || 1,
        total: parseFloat(item.total) || (parseFloat(item.price) * parseInt(item.quantity))
      });
    }
    return serialized;
  }

  // ── CONFIRM ORDER (Send to Backend) ──────────────
  $('#confirm-order').click(function() {
    if (Object.keys(cart).length === 0) {
      toastr.warning('Please add products to cart first!');
      return;
    }

    if (!selectedPriceType) {
      toastr.warning('Please select a price type (Regular or Wholesale) first!');
      return;
    }

    // Calculate total using local cart
    let confirmTotal = getCartTotal();
    let actualDiscount = 0;
    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (confirmTotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, confirmTotal);
    }
    
    let finalTotal = confirmTotal - actualDiscount;
    let priceLabel = selectedPriceType === 'wholesale' ? 'Wholesale Price' : 'Regular Price';
    let discountLabel = discountType ? (discountType === 'percentage' ? discountAmount.toFixed(2) + '%' : '৳' + discountAmount.toFixed(2)) : 'None';

    Swal.fire({
      title: 'Confirm Order?',
      icon: 'info',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: '<i class="la la-check-circle"></i> Confirm Order',
      cancelButtonText: 'Cancel',
      html: '<div style="text-align:left;margin-top:20px">' +
            '<p><strong>Cart Items:</strong> ' + Object.keys(cart).length + '</p>' +
            '<p><strong>Price Type:</strong> <span style="color:#059669;font-weight:600">' + priceLabel + '</span></p>' +
            '<p><strong>Subtotal:</strong> ৳' + confirmTotal.toFixed(2) + '</p>' +
            (actualDiscount > 0 ? '<p style="color:#f59e0b"><strong>Discount:</strong> -৳' + actualDiscount.toFixed(2) + ' (' + discountLabel + ')</p>' : '') +
            '<p style="font-size:16px;margin-top:10px"><strong>Total:</strong> <span style="color:#059669">৳' + finalTotal.toFixed(2) + '</span></p></div>',
    }).then((result) => {
      if (result.isConfirmed) {
        let $btn = $('#confirm-order');
        $btn.prop('disabled', true);
        $btn.html('<i class="la la-spinner fa-spin"></i> Processing...');

        // Send complete cart to backend
        console.log('Sending order to backend:', {
          cart: cart,
          price_type: selectedPriceType,
          discount_type: discountType,
          discount_amount: discountAmount
        });
        
        $.ajax({
          url: '{{ route("admin.pos.confirmOrder") }}',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            _token: '{{ csrf_token() }}',
            cart: serializeCart(cart),
            price_type: selectedPriceType,
            discount_type: discountType || null,
            discount_amount: discountAmount || 0
          }),
          dataType: 'json',
          success: function(res) {
            console.log('Order response received:', res);
            
            if (res.status === 'success') {
            // Add discount info to invoice
            res.invoice.discount_type = discountType;
            res.invoice.discount_amount = discountAmount;
            printPosInvoice(res.invoice);

            Swal.fire({
              title: 'Order Confirmed!',
              html: '<p style="font-size:16px;margin:15px 0"><strong>' + res.message +
                '</strong></p><p style="color:#64748b;font-size:14px">Order Number: <strong>#' + res.order_number +
                '</strong></p><p style="color:#64748b;font-size:14px">Total Amount: <strong>৳' + res.total_amount.toFixed(2) + '</strong></p>',
              icon: 'success',
              confirmButtonColor: '#059669',
              confirmButtonText: '<i class="la la-print"></i> Print Again',
              showCancelButton: true,
              cancelButtonText: '<i class="la la-check"></i> Done',
              cancelButtonColor: '#6366f1',
              focusCancel: true,
            }).then((result2) => {
              if (result2.isConfirmed) {
                res.invoice.discount_type = discountType;
                res.invoice.discount_amount = discountAmount;
                printPosInvoice(res.invoice);
              }
              
              // Reset everything
              cart = {};
              selectedPriceType = null;
              discountType = null;
              discountAmount = 0;
              $('input[name="price_type"]').prop('checked', false);
              $('input[name="discount_type"]').prop('checked', false);
              $('#discount-input').val('0').prop('disabled', true);
              updateDiscountDisplay();
              $('#confirm-order').prop('disabled', true).css({ 
                opacity: '.45', 
                cursor: 'not-allowed',
                background: 'linear-gradient(135deg, #404854, #2c323d)',
                boxShadow: 'none'
              }).html('<i class="la la-check-circle"></i> Confirm & Complete Order');
              renderCart(cart, null);
              renderSelectedCustomer(null);
              $('#product-search').val('');
              loadProducts();
              toastr.success('Order completed successfully!');
            });
          } else {
            // Handle error response from backend
            console.error('Order failed with status:', res.status, res.message);
            Swal.fire({
              title: 'Order Failed!',
              text: res.message || 'Failed to process order',
              icon: 'error',
              confirmButtonColor: '#dc2626'
            });
            toastr.error(res.message || 'Order failed');
            $btn.prop('disabled', false);
            $btn.html('<i class="la la-check-circle"></i> Confirm & Complete Order');
          }
          },
          error: function(xhr) {
            console.error('Order confirmation error:', xhr);
            let errorMsg = 'Failed to process order';
            
            // Try to extract error message from response
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMsg = xhr.responseJSON.message;
            } else if (xhr.statusText) {
              errorMsg = xhr.statusText;
            } else if (xhr.status === 0) {
              errorMsg = 'Network error - check server connection';
            }
            
            Swal.fire({
              title: 'Error!',
              text: errorMsg,
              icon: 'error',
              confirmButtonColor: '#dc2626'
            });
            toastr.error(errorMsg);
            $btn.prop('disabled', false);
            $btn.html('<i class="la la-check-circle"></i> Confirm & Complete Order');
          }
        });

      }
    });
  });

  // ── Print POS Invoice (receipt-style) ───────────
  function printPosInvoice(inv) {
    let sy = inv.currency_sym || '৳';
    let itemsHtml = '';
    inv.items.forEach(function(item, i) {
      itemsHtml += `<tr>
        <td>${i + 1}</td>
        <td>${item.name}</td>
        <td>${item.qty}</td>
        <td>${Number(item.price).toFixed(2)}</td>
        <td>${Number(item.total).toFixed(2)}</td>
      </tr>`;
    });

    let receiptHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${inv.order_number}</title>
</head>
<body onload="window.print()">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            html, body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
                color: #000;
                width: 76mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white;
            }
            body {
                padding: 10px !important;
            }
            .receipt-container {
                width: 100% !important;
                margin: 5px 0 !important;
                padding: 10px 11px !important;
                box-sizing: border-box;
            }
            table, table thead, table tbody, table tr, table th, table td {
                page-break-inside: avoid !important;
            }
        }
        html, body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            width: 80mm;
            margin: 0;
            padding: 0;
            background: white;
        }
        body {
            padding: 0;
        }
        .receipt-container {
            width: 100%;
            margin: 5px 0;
            padding: 10px 11px;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
            line-height: 1.3;
            letter-spacing: 0.5px;
        }
        .store-info {
            font-size: 9px;
            font-weight: 600;
            line-height: 1.5;
            margin: 0;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .store-info div {
            margin: 1px 0;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .invoice-header {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 6px;
            line-height: 1.5;
        }
        .invoice-header p {
            margin: 2px 0;
            font-weight: 600;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            table-layout: fixed;
        }
        table thead th {
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            padding: 3px 2px;
            border-bottom: 1px solid #000;
            word-break: break-word;
        }
        table tbody td {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 2px;
            border-bottom: 1px dotted #ccc;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        table thead th:nth-child(2),
        table tbody td:nth-child(2) {
            white-space: normal;
            max-width: 30mm;
            word-wrap: break-word;
            overflow: hidden;
        }
        table tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }
        td:nth-child(1) { width: 7%; text-align: center; }
        td:nth-child(2) { width: 50%; text-align: left; }
        td:nth-child(3) { width: 10%; text-align: center; }
        td:nth-child(4) { width: 16%; text-align: right; }
        td:nth-child(5) { width: 17%; text-align: right; }
        .summary-section {
            font-size: 11px;
            font-weight: 600;
            margin: 6px 0;
            line-height: 1.5;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            padding: 2px 0;
            font-weight: 600;
        }
        .summary-row span:first-child { flex: 1; }
        .summary-row span:last-child { text-align: right; white-space: nowrap; }
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            margin: 4px 0;
        }
        .footer-section {
            text-align: center;
            font-size: 9px;
            font-weight: 600;
            margin-top: 6px;
            line-height: 1.4;
        }
        .thanks {
            font-weight: bold;
            font-size: 10px;
            margin: 4px 0;
        }
    </style>

    <div class="receipt-container">
        <div class="text-center">
            <div class="store-name">${inv.store_name}</div>
            <div class="store-info">
                <div>KHAJUR BAGAN,ASHULIA,SAVAR,DHAKA-1341 </div>
                <div>Phone: +8801911997241</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="invoice-header">
            <p><strong>Invoice No:</strong> ${inv.order_number}</p>
            <p><strong>Date:</strong> ${inv.date}</p>
            <p><strong>Customer:</strong> ${inv.customer_name}</p>
        </div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>S.L</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>৳${Number(inv.subtotal).toFixed(2)}</span>
            </div>
            ${inv.discount_amount && inv.discount_amount > 0 ? `<div class="summary-row" style="color: #000;">
                <span><strong>Discount (${inv.discount_type === 'percentage' ? inv.discount_amount + '%' : '৳' + Number(inv.discount_amount).toFixed(2)}):</strong></span>
                <span><strong style="font-weight: 900;">-৳${inv.discount_type === 'percentage' ? ((Number(inv.subtotal) * inv.discount_amount) / 100).toFixed(2) : Number(inv.discount_amount).toFixed(2)}</strong></span>
            </div>` : ''}

            <div class="summary-total" style="font-size: 13px;">
                <div class="summary-row" style="font-size: 14px; font-weight: 900; letter-spacing: 0.5px;">
                    <span>Net Total:</span>
                    <span style="font-size: 15px; font-weight: 900; letter-spacing: 0.5px;">৳${Number(inv.grand_total).toFixed(2)}</span>
                </div>
            </div>

            <div class="summary-row">
                <span>Total Item:</span>
                <span>${inv.items.length} Item</span>
            </div>

            <div class="summary-row">
                <span>Paid Amount:</span>
                <span>৳${Number(inv.grand_total).toFixed(2)}</span>
            </div>

            <div class="summary-row">
                <span>Payment:</span>
                <span>cash</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="footer-section">
            <p>Physical damage, burn case not<br>valid for warranty</p>
            <p class="thanks">*** Thank You ***</p>
            <p>Sold by: ${inv.sold_by}<br>${inv.date}</p>
        </div>
    </div>
</body>
</html>`;


    let printWin = window.open('', '_blank', 'width=350,height=600');
    printWin.document.write(receiptHtml);
    printWin.document.close();
    printWin.onload = function() {
      printWin.focus();
      printWin.print();
      // Close after printing (small delay for the print dialog)
      setTimeout(function() {
        printWin.close();
      }, 1000);
    };
  }

  // ── RENDER CART (Lightning Fast - No DB Query) ──
  function renderCart(cartObj, priceType) {
    let html = '',
      subtotal = 0,
      hasItems = false;
    
    // Sort items by insertion order (_addedAt timestamp)
    let sortedIds = Object.keys(cartObj).sort((a, b) => {
      return (cartObj[a]._addedAt || 0) - (cartObj[b]._addedAt || 0);
    });
    
    sortedIds.forEach(id => {
      let item = cartObj[id];
      let useWholesale = (priceType === 'wholesale') && item.wholesale_price;
      let unitPrice = useWholesale ? Number(item.wholesale_price) : Number(item.price);
      let itemTotal = unitPrice * item.quantity;
      subtotal += itemTotal;
      hasItems = true;
      
      let stockDisplay = item.stock ? `<span style="color:#059669;font-weight:600">${item.stock}</span>` : '<span style="color:#dc2626;font-weight:600">0</span>';
      // Display full name (40 chars) + brand name
      let displayName = item.name.length > 40 ? item.name.substring(0, 40) + '...' : item.name;
      let brandDisplay = item.brand_name ? `<small style="color:#6c757d;font-size:10px;display:block;margin-top:2px">${item.brand_name}</small>` : '';
      
      // Hide W column if not wholesale
      let wColumnClass = (priceType === 'wholesale') ? '' : 'd-none';
      let wDisplayValue = (priceType === 'wholesale') ? `<input type="number" class="wholesale-price-input" data-id="${id}" value="${item.wholesale_price ? Number(item.wholesale_price).toFixed(2) : 0}" step="0.01" min="0" style="width:65px;padding:3px;border:1px solid #dee2e6;border-radius:3px;font-size:10px;text-align:right;font-weight:600;font-family:inherit">` : '<span style="color:#6c757d;font-size:9px">—</span>';
      
      html += `<tr data-product-id="${id}">
        <td style="padding: 0.5rem; overflow: hidden; text-overflow: ellipsis;"><strong title="${item.name}">${displayName}</strong>${brandDisplay}</td>
        <td style="text-align:center; padding: 0.5rem; white-space: nowrap;">
          <div style="display:flex;align-items:center;justify-content:center;gap:3px">
            <button class="qty-btn qty-minus" data-id="${id}" title="−">−</button>
            <input type="number" class="qty-input" data-id="${id}" value="${item.quantity}" min="1" max="9999" style="width:45px;padding:4px 2px;border:1px solid #dee2e6;border-radius:4px;font-size:11px;text-align:center;font-weight:600;font-family:inherit">
            <button class="qty-btn qty-plus" data-id="${id}" title="+">+</button>
          </div>
        </td>
        <td style="text-align:center;font-size:11px; padding: 0.5rem; white-space: nowrap;">${stockDisplay}</td>
        <td style="text-align:right;font-size:11px; padding: 0.5rem; white-space: nowrap;" class="${(priceType === 'wholesale') ? 'd-none' : ''}"><input type="number" class="price-input" data-id="${id}" value="${Number(item.price).toFixed(2)}" step="0.01" min="0" style="width:65px;padding:3px;border:1px solid #dee2e6;border-radius:3px;font-size:10px;text-align:right;font-weight:600;font-family:inherit"></td>
        <td style="text-align:right;font-size:11px; padding: 0.5rem; white-space: nowrap;" class="${wColumnClass}">${wDisplayValue}</td>
        <td style="text-align:right;font-size:11px; padding: 0.5rem; white-space: nowrap;"><strong style="color:#16a34a">${useWholesale ? '<sup style="font-size:9px;background:#0d5f42;color:#f0b83d;padding:2px 4px;border-radius:2px;margin-right:2px;font-weight:700">W</sup>' : ''}৳${itemTotal.toFixed(2)}</strong></td>
        <td style="text-align:center; padding: 0.5rem; white-space: nowrap;"><button class="btn btn-sm btn-danger remove-from-cart" data-id="${id}" style="padding:2px 4px;font-size:10px;" title="Remove"><i class="la la-trash"></i></button></td>
      </tr>`;
    });
    
    // Table header adjustment
    let headerHtml = `<tr style="font-size: 0.75rem;">
      <th style="width: 40%;">Product</th>
      <th style="width: 20%; text-align:center;">Qty</th>
      <th style="width: 10%; text-align:center; font-size: 0.7rem;">Stock</th>
      <th style="width: 12%; text-align:right;" class="${(priceType === 'wholesale') ? 'd-none' : ''}">Unit Price (Editable)</th>
      <th style="width: 12%; text-align:right;" class="${(priceType === 'wholesale') ? '' : 'd-none'}">W (Editable)</th>
      <th style="width: 16%; text-align:right;">Total</th>
      <th style="width: 10%; text-align:center;">×</th>
    </tr>`;
    $('#cart-table thead tr').replaceWith(headerHtml);
    
    if (!hasItems) {
      html = `<tr><td colspan="7"><div class="text-center py-5 text-muted"><i class="la la-shopping-cart" style="font-size:2rem;margin-bottom:0.625rem;display:block"></i><p style="font-size:12px;margin:0">Cart empty</p></div></td></tr>`;
      $('#cart-subtotal').text('৳0.00');
      $('#cart-items').text('0');
      $('#cart-discount-row').hide();
      $('#cart-grand-total').text('৳0.00');
    } else {
      // Calculate discount
      let actualDiscount = 0;
      if (discountType === 'percentage' && discountAmount > 0) {
        actualDiscount = (subtotal * discountAmount) / 100;
      } else if (discountType === 'fixed' && discountAmount > 0) {
        actualDiscount = Math.min(discountAmount, subtotal);
      }
      
      let finalTotal = subtotal - actualDiscount;
      let totalItems = calculateTotalItems();
      
      // Update subtotal
      $('#cart-subtotal').text('৳' + subtotal.toFixed(2));
      
      // Update total items
      $('#cart-items').text(totalItems);
      
      // Update discount row (show only if there's a discount)
      if (actualDiscount > 0) {
        let discountLabel = discountType === 'percentage' ? `${discountAmount.toFixed(2)}% OFF` : 'Fixed Amount';
        $('#cart-discount').html('-৳' + actualDiscount.toFixed(2) + '<br><small style="opacity: 0.8; font-size: 0.75rem;">' + discountLabel + '</small>');
        $('#cart-discount-row').show();
      } else {
        $('#cart-discount-row').hide();
      }
      
      // Update grand total
      $('#cart-grand-total').text('৳' + finalTotal.toFixed(2));
    }
    
    $('#cart-table tbody').html(html);
  }

  // ── Render selected customer ────────────────────
  function renderSelectedCustomer(customer) {
    if (!customer) {
      $('#selected-customer').addClass('d-none').html('');
      return;
    }
    let html = `
      <div class="alert alert-primary mb-0 p-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted fw-600 d-block mb-1"><i class="la la-user-check"></i> Selected Customer</small>
            <div class="fw-bold" style="font-size: 14px;">${customer.name || 'N/A'}</div>
            ${customer.email ? '<small class="text-muted d-block"><i class="la la-envelope"></i> ' + customer.email + '</small>' : ''}
            ${customer.mobile ? '<small class="text-muted d-block"><i class="la la-phone"></i> ' + customer.mobile + '</small>' : ''}
          </div>
          <button id="clear-customer" class="btn btn-sm btn-outline-primary" title="Change customer"><i class="la la-exchange"></i></button>
        </div>
      </div>`;
    $('#selected-customer').removeClass('d-none').html(html).show();
  }

  // ── Select customer from search result ─────────
  window.selectCustomerFromSearch = function(userId, name) {
    $.post('{{ route("admin.pos.selectCustomer") }}', {
      _token: '{{ csrf_token() }}',
      user_id: userId
    }, function(res) {
      if (res.customer) {
        renderSelectedCustomer(res.customer);
        setTimeout(function() {
          Swal.close();
        }, 100);
        toastr.success('Customer selected!');
      } else {
        toastr.error('Failed to select customer');
      }
    }).fail(function() {
      toastr.error('Error selecting customer');
    });
  };

  // ── Render products grid ────────────────────────
  function renderProductsGrid(products) {
    if (!products || products.length === 0) {
      $('#products-container').html('<div style="text-align:center;padding:30px;color:#94a3b8;grid-column:1/-1"><i class="la la-inbox" style="font-size:32px;margin-bottom:10px;display:block"></i>No products available</div>');
      return;
    }
    let html = '';
    products.forEach(product => {
      let price = product.sale_price ? Number(product.sale_price).toFixed(2) : Number(product.regular_price).toFixed(2);
      let stock = product.stock || 0;
      html += `
        <div class="product-item" data-id="${product.id}" data-stock="${stock}" style="cursor:pointer">
          <div class="product-item-image"><i class="la la-box"></i></div>
          <div class="product-item-name">${product.name}</div>
          <div class="product-item-price">৳${Number(price).toFixed(2)}</div>
          <div style="font-size:9px;color:#94a3b8;margin-bottom:6px">Stock: <strong style="color:#059669">${stock}</strong></div>
          <button class="product-item-btn" data-id="${product.id}" style="pointer-events:none"><i class="la la-plus"></i>Add</button>
        </div>
        `;
    });
    $('#products-container').html(html);
  }

  // ── Select customer button (opens dialog) ──────
  $('#select-customer-btn').on('click', function() {
    Swal.fire({
      title: 'Select Customer',
      html: `
        <input type="text" id="swal-customer-search" class="swal2-input" placeholder="Search or create customer…" autofocus>
        <div id="swal-customer-results" style="max-height: 300px; overflow-y: auto; margin-top: 10px;"></div>
      `,
      icon: 'info',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: '<i class="la la-check-circle"></i> Confirm',
      cancelButtonText: 'Cancel',
      willOpen() {
        let searchTimer;
        $('#swal-customer-search').on('input', function() {
          clearTimeout(searchTimer);
          let query = $(this).val().trim();
          
          if (!query || query.length < 1) {
            $('#swal-customer-results').html('');
            return;
          }

          searchTimer = setTimeout(function() {
            $.get('{{ route("admin.pos.searchCustomers") }}', {
              query
            }).done(function(data) {
              let html = '';
              if (!data || data.length === 0) {
                html = `<div style="text-align:center;padding:10px;color:#94a3b8;font-size:12px"><i class="la la-search"></i> No customers found</div>
                  <button type="button" style="width:100%;padding:8px;margin:5px 0;background:#059669;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-family:inherit" onclick="
                    let name = document.getElementById('swal-customer-search').value;
                    let email = prompt('Email (optional):', '');
                    let mobile = prompt('Mobile (optional):', '');
                    $.post('{{ route("admin.pos.createCustomer") }}', {
                      _token: '{{ csrf_token() }}',
                      name: name,
                      email: email || null,
                      mobile: mobile || null
                    }, function(res) {
                      renderSelectedCustomer(res.customer);
                      Swal.close();
                      toastr.success('Customer created and selected!');
                    });
                  "><i class="la la-plus"></i> Create New Customer</button>`;
              } else {
                data.forEach(c => {
                  html += `<div style="padding:8px;border-bottom:1px solid #e2e8f0;cursor:pointer;background:white" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'" onclick="
                    selectCustomerFromSearch(${c.id}, '${c.name.replace(/'/g, "\\'")}')
                  ">
                    <div style="font-weight:600;color:#1e293b;font-size:12px">${c.name || 'N/A'}</div>
                    <div style="font-size:10px;color:#94a3b8">${c.email || '—'} ${c.mobile ? '| ' + c.mobile : ''}</div>
                  </div>`;
                });
              }
              $('#swal-customer-results').html(html);
            }).fail(function() {
              $('#swal-customer-results').html('<div style="color:#dc2626;text-align:center;padding:10px">Search failed</div>');
            });
          }, 300);
        });
      }
    });
  });

  // ── Clear selected customer ─────────────────────
  $(document).on('click', '#clear-customer', function(e) {
    e.preventDefault();
    $.post('{{ route("admin.pos.clearCustomer") }}', {
      _token: '{{ csrf_token() }}'
    }, function(res) {
      renderSelectedCustomer(null);
      toastr.info('Customer cleared. Select a new customer.');
    }).fail(function() {
      toastr.error('Failed to clear customer');
    });
  });

});
</script>
@endpush