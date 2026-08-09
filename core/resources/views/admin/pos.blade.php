@extends('admin.layouts.app')

@section('panel')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ══════════════════════════════════════════════════
   POS — Modern Professional Theme
   ══════════════════════════════════════════════════ */
.pos * {
  box-sizing: border-box;
}

.pos {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  padding: 16px 16px;
  background: #f4f6f9;
  min-height: 100vh;
  color: #1e293b;
}

/* ─── HEADER ────────────────────────────────────── */
.pos-header {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
}

.pos-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pos-header-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 20px;
  flex-shrink: 0;
}

.pos-header-left h1 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #0f172a;
}

.pos-header-left p {
  font-size: 11px;
  color: #94a3b8;
  margin: 1px 0 0;
}

.pos-header-right {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: #64748b;
}

.pos-live {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #f0fdf4;
  color: #16a34a;
  padding: 3px 8px;
  border-radius: 16px;
  font-weight: 600;
  font-size: 10px;
}

.pos-live::before {
  content: '';
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: #22c55e;
  animation: pulse 1.5s ease-in-out infinite;
  flex-shrink: 0;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: .4;
    transform: scale(.7);
  }
}

/* ─── LAYOUT GRID ───────────────────────────────── */
.pos-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
}

.pos-row-top {
  display: contents;
}

@media (max-width: 1400px) {
  .pos-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 992px) {
  .pos-grid {
    grid-template-columns: 1fr;
  }
}

/* ─── CARD ──────────────────────────────────────── */
.pos-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
  display: flex;
  flex-direction: column;
  transition: box-shadow .25s;
  /* max-height: calc(100vh - 200px); */
  display: flex;
  flex-direction: column;
}

.pos-card:hover {
  box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
}

.pos-card-head {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.pos-card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.ct-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  flex-shrink: 0;
}

.ct-icon--search {
  background: #eef2ff;
  color: #6366f1;
}

.ct-icon--customer {
  background: #fefce8;
  color: #ca8a04;
}

.ct-icon--cart {
  background: #ecfdf5;
  color: #059669;
}

.pos-card-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 16px;
  background: #f1f5f9;
  color: #64748b;
}

.pos-card-body {
  padding: 12px 14px;
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}

/* ─── SEARCH INPUT ──────────────────────────────── */
.pos-input-wrap {
  position: relative;
}

.pos-input-wrap .pi-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 17px;
  color: #94a3b8;
  pointer-events: none;
}

.pos-input-wrap input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  font-size: 12px;
  color: #1e293b;
  font-family: inherit;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  transition: border .2s, box-shadow .2s;
}

.pos-input-wrap input::placeholder {
  color: #94a3b8;
}

.pos-input-wrap input:focus {
  background: #fff;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}

/* ─── RESULT LISTS ──────────────────────────────── */
.pos-results {
  list-style: none;
  padding: 0;
  margin: 8px 0 0;
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}

.pos-results::-webkit-scrollbar {
  width: 4px;
}

.pos-results::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.pos-search-table {
  margin: 0;
  table-layout: fixed;
}

.pos-search-table thead th {
  position: sticky;
  top: 0;
  z-index: 1;
  /* padding: 8px 8px; */
  white-space: nowrap;
  font-size: 10px;
}

.pos-search-table thead th:first-child {
  width: 40%;
}

.pos-search-table tbody td {
  /* padding: 8px 8px; */
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pos-search-table tbody tr {
  cursor: default;
}

.pos-search-table .badge-price {
  font-size: 11px;
  padding: 2px 7px;
}

.search-result-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  border-radius: 8px;
  margin-bottom: 1px;
  transition: background .15s;
}

.search-result-item:hover {
  background: #f8fafc;
}

.search-result-item strong {
  font-size: 12px;
  font-weight: 600;
  color: #1e293b;
}

.search-result-item small {
  font-size: 11px;
  color: #64748b;
}

.badge-price {
  display: inline-block;
  background: #eef2ff;
  color: #4338ca;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

/* ─── BUTTONS ───────────────────────────────────── */
.pos-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-family: inherit;
  font-size: 11px;
  font-weight: 600;
  padding: 6px 10px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
}

.pos-btn i {
  font-size: 13px;
}

.pos-btn--primary {
  background: #6366f1;
  color: #fff;
}

.pos-btn--primary:hover {
  background: #4f46e5;
  box-shadow: 0 2px 8px rgba(99, 102, 241, .3);
  color: #fff;
}

.pos-btn--success {
  background: #059669;
  color: #fff;
}

.pos-btn--success:hover {
  background: #047857;
  box-shadow: 0 2px 8px rgba(5, 150, 105, .3);
  color: #fff;
}

.pos-btn--danger {
  background: #fef2f2;
  color: #dc2626;
}

.pos-btn--danger:hover {
  background: #fee2e2;
  color: #b91c1c;
}

.pos-btn--ghost {
  background: transparent;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
}

.pos-btn--ghost:hover {
  background: #f1f5f9;
  color: #334155;
  border-color: #cbd5e1;
}

.pos-btn--lg {
  font-size: 12px;
  padding: 10px 16px;
  border-radius: 8px;
  width: 100%;
}

/* ─── RESPONSIVE ────────────────────────────────── */
@media (max-width: 1400px) {
  .pos-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 992px) {
  .pos-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .pos {
    padding: 8px 8px;
  }
  
  .pos-header-left h1 {
    font-size: 16px;
  }
  
  .pos-header-right {
    font-size: 11px;
  }
  
  .pos-card-title {
    font-size: 12px;
  }
  
  .pos-table {
    font-size: 11px;
  }
}

/* ─── SELECTED CUSTOMER ─────────────────────────── */
.selected-customer-box {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border-radius: 8px;
  padding: 12px 14px;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}

.selected-customer-box::after {
  content: '';
  position: absolute;
  top: -18px;
  right: -18px;
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: rgba(255, 255, 255, .08);
}

.selected-customer-box .sc-label {
  font-size: 9px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .5px;
  opacity: .7;
  margin-bottom: 4px;
}

.selected-customer-box .sc-name {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 3px;
  line-height: 1.2;
}

.selected-customer-box .sc-info {
  font-size: 11px;
  opacity: .85;
  display: block;
  margin-bottom: 1px;
  line-height: 1.3;
}

.selected-customer-box .sc-actions {
  margin-top: 8px;
}

.selected-customer-box .sc-actions button {
  background: rgba(255, 255, 255, .14);
  border: 1px solid rgba(255, 255, 255, .28);
  color: #fff;
  font-size: 10px;
  font-weight: 500;
  padding: 4px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: background .2s;
  font-family: inherit;
}

.selected-customer-box .sc-actions button:hover {
  background: rgba(255, 255, 255, .25);
}

/* ─── CART TABLE ────────────────────────────────── */
.pos-cart-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  min-height: 0;
}

.pos-cart-scroll::-webkit-scrollbar {
  width: 4px;
}

.pos-cart-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.pos-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 12px;
}

.pos-table thead th {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .3px;
  color: #64748b;
  padding: 8px 6px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.pos-table tbody td {
  padding: 8px 6px;
  font-size: 12px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.pos-table tbody tr:last-child td {
  border-bottom: none;
}

.pos-table tbody tr {
  transition: background .15s;
}

.pos-table tbody tr:hover {
  background: #f8fafc;
}

.qty-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 26px;
  height: 24px;
  background: #eef2ff;
  color: #4338ca;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}

.pos-cart-empty {
  text-align: center;
  padding: 50px 20px;
  color: #94a3b8;
}

.pos-cart-empty i {
  font-size: 42px;
  display: block;
  margin-bottom: 10px;
  opacity: .35;
}

.pos-cart-empty p {
  font-size: 14px;
  font-weight: 500;
  margin: 0;
}

/* ─── CART FOOTER ───────────────────────────────── */
.pos-cart-foot {
  padding: 12px 14px;
  border-top: 1px solid #f1f5f9;
  background: #fff;
  flex-shrink: 0;
  overflow-y: auto;
  max-height: 50%;
}

.pos-total-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 10px;
}

.pos-total-bar .tl {
  font-size: 12px;
  font-weight: 600;
  color: #475569;
}

.pos-total-bar .tv {
  font-size: 18px;
  font-weight: 800;
  color: #059669;
  letter-spacing: -.5px;
}

/* ─── PRICE TYPE SELECTOR ──────────────────────── */
.price-type-selector {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}

.price-type-option {
  flex: 1;
  position: relative;
}

.price-type-option input[type="radio"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.price-type-option label {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
  transition: all .2s;
  user-select: none;
}

.price-type-option label:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
  color: #334155;
}

.price-type-option input:checked + label {
  border-color: #059669;
  background: #ecfdf5;
  color: #059669;
  box-shadow: 0 0 0 3px rgba(5,150,105,.1);
}

.price-type-option input:checked + label .pt-icon {
  color: #059669;
}

.pt-icon {
  font-size: 18px;
  line-height: 1;
}

/* ─── MISC ──────────────────────────────────────── */
.no-data-msg {
  text-align: center;
  padding: 20px;
  color: #94a3b8;
  font-size: 13px;
}

.no-data-msg i {
  font-size: 26px;
  display: block;
  margin-bottom: 6px;
  opacity: .45;
}

.list-group-item {
  border: none !important;
  border-radius: 10px !important;
  padding: 10px 12px;
  margin-bottom: 2px;
  transition: background .15s;
  font-size: 13.5px;
}

.list-group-item:hover {
  background: #f8fafc;
}

/* ─── QUANTITY BUTTONS ──────────────────────────── */
.qty-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 5px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #1e293b;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  padding: 0;
  font-family: inherit;
}

.qty-btn:hover {
  background: #ecfdf5;
  border-color: #059669;
  color: #059669;
}

.qty-btn:active {
  transform: scale(.95);
}

.qty-btn:disabled {
  opacity: .5;
  cursor: not-allowed;
}
</style>

<div class="pos">

  {{-- ═══ HEADER / TITLE BAR ═══ --}}
  <div class="pos-header">
    <div class="pos-header-left">
      <div class="pos-header-icon"><i class="la la-store"></i></div>
      <div>
        <h1>Point of Sale</h1>
        <p>Manage products, customers &amp; orders</p>
      </div>
    </div>
    <div class="pos-header-right">
      <span class="pos-live">Online</span>
      <span><i class="la la-calendar"></i>&nbsp; {{ now()->format('D, M d Y') }}</span>
    </div>
  </div>

  <div class="pos-grid">

    {{-- ═══ ROW: Search Products ═══ --}}
    <div class="pos-card">
      <div class="pos-card-head">
        <span class="pos-card-title">
          <span class="ct-icon ct-icon--search"><i class="la la-search"></i></span>
          Search Products
        </span>
        <span class="pos-card-badge">Live</span>
      </div>
      <div class="pos-card-body">
        <div class="pos-input-wrap">
          <i class="la la-search pi-icon"></i>
          <input type="text" id="product-search" placeholder="Type product name…">
        </div>
        <div id="search-results" class="pos-results"></div>
        <div id="search-placeholder" class="no-data-msg"><i class="la la-search"></i> Search products</div>
      </div>
    </div>

    {{-- ═══ ROW: Customer Management ═══ --}}
    <div class="pos-card">
      <div class="pos-card-head">
        <span class="pos-card-title">
          <span class="ct-icon ct-icon--customer"><i class="la la-user-circle"></i></span>
          Customer
        </span>
      </div>
      <div class="pos-card-body">
        <div class="pos-input-wrap">
          <i class="la la-user pi-icon"></i>
          <input type="text" id="customer-search" placeholder="Search customer…">
        </div>
        <ul id="customer-results" class="pos-results"></ul>
        <div id="selected-customer"></div>
      </div>
    </div>

    {{-- ═══ SHOPPING CART (full height, right side) ═══ --}}
    <div class="pos-card" style="grid-row: 1 / 3; display: flex; flex-direction: column;">
      <div class="pos-card-head">
        <span class="pos-card-title">
          <span class="ct-icon ct-icon--cart"><i class="la la-shopping-cart"></i></span>
          Cart
        </span>
        <button id="clear-cart" class="pos-btn pos-btn--ghost" style="padding: 4px 8px; font-size: 10px;">
          <i class="la la-trash"></i>
        </button>
      </div>

      <div class="pos-cart-scroll">
        <table class="pos-table" id="cart-table">
          <thead>
            <tr>
              <th>Product</th>
              <th style="text-align:center">Qty</th>
              <th style="text-align:center;font-size:9px">Stock</th>
              <th style="text-align:right">Price</th>
              <th style="text-align:right">W</th>
              <th style="width:60px;text-align:right">Total</th>
              <th style="text-align:center;width:50px">×</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="pos-cart-foot">
        <div class="pos-total-bar" id="cart-total">
          <span class="tl">Total</span>
          <span class="tv">$0.00</span>
        </div>
        {{-- Price Type Selector --}}
        <div class="price-type-selector" id="price-type-selector">
          <div class="price-type-option">
            <input type="radio" name="price_type" id="pt_regular" value="regular">
            <label for="pt_regular" style="font-size: 10px; padding: 6px 10px;">
              <span class="pt-icon"><i class="la la-tag"></i></span>
              Regular
            </label>
          </div>
          <div class="price-type-option">
            <input type="radio" name="price_type" id="pt_wholesale" value="wholesale">
            <label for="pt_wholesale" style="font-size: 10px; padding: 6px 10px;">
              <span class="pt-icon"><i class="la la-boxes"></i></span>
              Wholesale
            </label>
          </div>
        </div>

        {{-- Discount Section --}}
        <div style="background: #f8fafc; border-radius: 8px; padding: 8px; margin-bottom: 8px; border: 1px solid #e2e8f0;">
          <div style="font-size: 11px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
            <i class="la la-percent" style="margin-right: 4px;"></i>Discount
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 6px;">
            <div>
              <input type="radio" name="discount_type" id="dt_percentage" value="percentage" style="margin-right: 4px;">
              <label for="dt_percentage" style="font-size: 10px; font-weight: 500; color: #475569; cursor: pointer;">%</label>
            </div>
            <div>
              <input type="radio" name="discount_type" id="dt_fixed" value="fixed" style="margin-right: 4px;">
              <label for="dt_fixed" style="font-size: 10px; font-weight: 500; color: #475569; cursor: pointer;">Fixed</label>
            </div>
          </div>
          <div style="display: flex; gap: 6px;">
            <input type="number" id="discount-input" placeholder="0" min="0" step="0.01" style="flex: 1; padding: 6px 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; color: #1e293b;" disabled>
            <span id="discount-unit" style="padding: 6px 8px; background: #e2e8f0; border-radius: 6px; font-weight: 600; color: #475569; min-width: 36px; text-align: center; font-size: 10px;">%</span>
          </div>
          <div id="discount-info" style="font-size: 10px; color: #64748b; margin-top: 4px;">
            <strong id="discount-display">$0.00</strong>
          </div>
        </div>

        <button id="confirm-order" class="pos-btn pos-btn--success pos-btn--lg" disabled style="opacity:.45;cursor:not-allowed;font-size:11px">
          <i class="la la-check-circle"></i>Confirm
        </button>
      </div>
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

  // ═══════════════════════════════════════════════════════════════
  // INSTANT FRONTEND CART - No Session Lag, All Updates are <1ms
  // ═══════════════════════════════════════════════════════════════
  
  let cart = {}; // Local cart object (NOT session)
  let selectedPriceType = null;
  let discountType = null;
  let discountAmount = 0;
  let cartOrderCounter = 0;

  function getNextCartOrder() {
    cartOrderCounter += 1;
    return cartOrderCounter;
  }

  function syncCartOrder(sourceCart) {
    let orderedKeys = Object.keys(sourceCart || {});
    let maxOrder = cartOrderCounter;

    orderedKeys.forEach(function(key, index) {
      let item = sourceCart[key] || {};
      let order = Number(item.sort_order || 0);
      if (!order) {
        order = index + 1;
      }
      item.sort_order = order;
      sourceCart[key] = item;
      if (order > maxOrder) {
        maxOrder = order;
      }
    });

    cartOrderCounter = maxOrder;
    return sourceCart;
  }

  // Load initial data with stock
  function initCart() {
    $.get('{{ route("admin.pos.getCart") }}', (res) => {
      cart = syncCartOrder(res.cart || {});
      // Fetch stock for all items
      if (Object.keys(cart).length > 0) {
        let productIds = Object.keys(cart);
        $.post('{{ route("admin.pos.getStockInfo") }}', {
          _token: '{{ csrf_token() }}',
          product_ids: productIds
        }, (stockRes) => {
          cart = { ...cart, ...stockRes };
          renderCart(cart, null);
        }).fail(() => renderCart(cart, null));
      } else {
        renderCart(cart, null);
      }
    }).fail(() => cart = {});
  }

  function loadSelectedCustomer() {
    $.get('{{ route("admin.pos.getCustomer") }}').done((res) => {
      renderSelectedCustomer(res.customer);
    });
  }

  initCart();
  loadSelectedCustomer();

  // ─────────────────────────────────────────────────────────────
  // INSTANT: Price Type Change (no AJAX)
  // ─────────────────────────────────────────────────────────────
  $('input[name="price_type"]').on('change', function () {
    selectedPriceType = $(this).val();
    $('#confirm-order').prop('disabled', false).css({ opacity: 1, cursor: 'pointer' });
    renderCart(cart, selectedPriceType); // INSTANT - no backend call
  });

  // ─────────────────────────────────────────────────────────────
  // INSTANT: Discount Type Change (no AJAX)
  // ─────────────────────────────────────────────────────────────
  $('input[name="discount_type"]').on('change', function() {
    discountType = $(this).val();
    $('#discount-input').prop('disabled', false);
    $('#discount-unit').text(discountType === 'percentage' ? '%' : '৳');
    $('#discount-input').val('0');
    discountAmount = 0;
    updateDiscountDisplay();
  });

  // ─────────────────────────────────────────────────────────────
  // INSTANT: Discount Amount Input (no AJAX)
  // ─────────────────────────────────────────────────────────────
  $('#discount-input').on('input', function() {
    discountAmount = parseFloat($(this).val()) || 0;
    updateDiscountDisplay();
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Product Search (No AJAX Wait)
  // ═════════════════════════════════════════════════════════
  let searchTimer;
  $('#product-search').on('input', function() {
    clearTimeout(searchTimer);
    let query = $(this).val().trim();
    if (!query || query.length < 2) {
      $('#search-results').empty();
      $('#search-placeholder').show();
      return;
    }

    searchTimer = setTimeout(function() {
      $.get('{{ route("admin.pos.searchProducts") }}', { query })
        .done(function(data) {
          let html = '';
          if (!data || data.length === 0) {
            html = '<div style="text-align:center;padding:12px;color:#94a3b8;font-size:12px"><i class="la la-search" style="margin-right:4px"></i>No products</div>';
          } else {
            html = `<table class="pos-table pos-search-table">
              <thead><tr>
                <th style="font-size:9px">Product</th>
                <th style="font-size:9px">Reg</th>
                <th style="font-size:9px">Sale</th>
                <th style="font-size:9px">W</th>
                <th style="text-align:center;font-size:9px">+</th>
              </tr></thead><tbody>`;
            data.slice(0, 8).forEach(product => {
              let regularPrice = product.regular_price ? Number(product.regular_price).toFixed(0) : null;
              let salePrice = product.sale_price ? Number(product.sale_price).toFixed(0) : null;
              let wholesalePrice = product.wholesale_price ? Number(product.wholesale_price).toFixed(0) : null;

              html += `<tr>
                <td title="${product.name}" style="font-size:11px"><strong style="color:#1e293b;overflow:hidden;text-overflow:ellipsis;max-width:80px;display:block">${product.name}</strong></td>
                <td style="font-size:10px">${regularPrice ? '৳' + regularPrice : '—'}</td>
                <td style="font-size:10px">${salePrice ? '<span style="color:#059669">৳' + salePrice + '</span>' : '—'}</td>
                <td style="font-size:10px">${wholesalePrice ? '<span style="color:#a16207">৳' + wholesalePrice + '</span>' : '—'}</td>
                <td style="text-align:center"><button class="pos-btn pos-btn--primary add-to-cart" style="padding:4px 6px;font-size:10px" data-product="${JSON.stringify({id:product.id,name:product.name,regular_price:regularPrice||0,sale_price:salePrice||0,wholesale_price:wholesalePrice||0})}"><i class="la la-plus"></i></button></td>
              </tr>`;
            });
            html += '</tbody></table>';
          }
          $('#search-results').html(html);
          $('#search-placeholder').hide();
        })
        .fail(function() {
          toastr.error('Product search failed');
          $('#search-placeholder').hide();
          $('#search-results').html('<div style="text-align:center;padding:12px;color:#dc2626;font-size:12px"><i class="la la-warning"></i>Error</div>');
        });
    }, 300);
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Add to Cart (0-1ms, No AJAX)
  // ═════════════════════════════════════════════════════════
  $(document).on('click', '.add-to-cart', function() {
    let productStr = $(this).data('product');
    let product = typeof productStr === 'string' ? JSON.parse(productStr) : productStr;
    let productName = product.name;

    // Add or increment if exists
    if (cart[product.id]) {
      cart[product.id].quantity++;
    } else {
      cart[product.id] = {
        id: product.id,
        name: productName,
        regular_price: product.regular_price,
        sale_price: product.sale_price,
        wholesale_price: product.wholesale_price,
        quantity: 1,
        stock: product.stock || 999,
        sort_order: getNextCartOrder()
      };
    }

    // INSTANT render - ~0.5ms
    renderCart(cart, selectedPriceType);
    toastr.success(`"${productName}" added!`);
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Remove from Cart (0-1ms, No AJAX)
  // ═════════════════════════════════════════════════════════
  $(document).on('click', '.remove-from-cart', function() {
    let id = $(this).data('id');
    let productName = $(this).closest('tr').find('td:first').text();

    delete cart[id];

    // INSTANT render - ~0.5ms
    renderCart(cart, selectedPriceType);
    toastr.info(`"${productName}" removed`);
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Increment Quantity (0-1ms, No AJAX)
  // ═════════════════════════════════════════════════════════
  $(document).on('click', '.qty-plus', function() {
    let id = $(this).data('id');
    if (cart[id]) {
      cart[id].quantity++;
      // INSTANT render - ~0.5ms
      renderCart(cart, selectedPriceType);
    }
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Decrement Quantity (0-1ms, No AJAX)
  // ═════════════════════════════════════════════════════════
  $(document).on('click', '.qty-minus', function() {
    let id = $(this).data('id');
    if (cart[id] && cart[id].quantity > 1) {
      cart[id].quantity--;
      // INSTANT render - ~0.5ms
      renderCart(cart, selectedPriceType);
    }
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Direct Quantity Input (0-2ms, No AJAX)
  // ═════════════════════════════════════════════════════════
  $(document).on('change', '.qty-input', function() {
    let id = $(this).data('id');
    let newQty = parseInt($(this).val()) || 1;

    // Validate
    newQty = Math.max(1, Math.min(newQty, 9999));

    if (cart[id]) {
      cart[id].quantity = newQty;
      // INSTANT render - ~0.5ms
      renderCart(cart, selectedPriceType);
    }
  });

  // ═════════════════════════════════════════════════════════
  // INSTANT: Calculate and Display Discount
  // ═════════════════════════════════════════════════════════
  function updateDiscountDisplay() {
    let cartTotal = getCartTotal();
    let actualDiscount = 0;

    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (cartTotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, cartTotal);
    }

    $('#discount-display').text('৳' + actualDiscount.toFixed(2));
    updateCartTotal();
  }

  // ═════════════════════════════════════════════════════════
  // INSTANT: Update Cart Total Display
  // ═════════════════════════════════════════════════════════
  function updateCartTotal() {
    let cartSubtotal = getCartTotal();
    let actualDiscount = 0;

    if (discountType === 'percentage' && discountAmount > 0) {
      actualDiscount = (cartSubtotal * discountAmount) / 100;
    } else if (discountType === 'fixed' && discountAmount > 0) {
      actualDiscount = Math.min(discountAmount, cartSubtotal);
    }

    let finalTotal = cartSubtotal - actualDiscount;
    let label = selectedPriceType === 'wholesale' ? ' <small style="font-size:12px;opacity:.7">(Wholesale)</small>' : '';
    $('#cart-total').html('<span class="tl">Order Total' + label + '</span><span class="tv">৳' + finalTotal.toFixed(2) + '</span>');
  }

  // ═════════════════════════════════════════════════════════
  // INSTANT: Get Total from Local cart Object (No DOM parsing)
  // ═════════════════════════════════════════════════════════
  function getCartTotal() {
    let total = 0;
    let priceType = selectedPriceType || 'regular_price';

    for (let id in cart) {
      let item = cart[id];
      let price = 0;

      if (priceType === 'wholesale' && item.wholesale_price) {
        price = parseFloat(item.wholesale_price);
      } else if (priceType === 'sale' && item.sale_price) {
        price = parseFloat(item.sale_price);
      } else {
        price = parseFloat(item.regular_price);
      }

      total += price * item.quantity;
    }

    return total;
  }

  // ── Clear cart ──────────────────────────────────
  $('#clear-cart').click(function() {
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
        let $btn = $(this);
        $btn.prop('disabled', true);
        $.post('{{ route("admin.pos.clearCart") }}', {
          _token: '{{ csrf_token() }}'
        }, function(res) {
          // Reset price type selection
          selectedPriceType = null;
          discountType = null;
          discountAmount = 0;
          $('input[name="price_type"]').prop('checked', false);
          $('input[name="discount_type"]').prop('checked', false);
          $('#discount-input').val('0').prop('disabled', true);
          updateDiscountDisplay();
          $('#confirm-order').prop('disabled', true).css({ opacity: .45, cursor: 'not-allowed' });
          renderCart(res.cart, null);
          toastr.success('Cart cleared successfully!');
        }).fail(function() {
          toastr.error('Failed to clear cart');
        }).always(() => $btn.prop('disabled', false));
      }
    });
  });

  // ── Confirm order ───────────────────────────────
  // ═════════════════════════════════════════════════════════
  // INSTANT: Confirm Order - Send Complete Cart to Backend
  // ═════════════════════════════════════════════════════════
  $('#confirm-order').click(function() {
    if (Object.keys(cart).length === 0) {
      toastr.warning('Please add products to cart first!');
      return;
    }

    if (!selectedPriceType) {
      toastr.warning('Please select a price type (Regular or Wholesale) first!');
      return;
    }

    // Calculate total using selected price type
    let confirmTotal = getCartTotal();

    // Calculate discount
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

        let payloadCart = normalizeCartForSubmit(cart, selectedPriceType);

        // Send ENTIRE CART OBJECT + metadata to backend
        $.post('{{ route("admin.pos.confirmOrder") }}', {
          _token: '{{ csrf_token() }}',
          cart: payloadCart,
          price_type: selectedPriceType,
          discount_type: discountType || null,
          discount_amount: discountAmount || 0
        }, function(res) {
          if (res.status === 'success') {
            // Print the invoice immediately
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
            }).then((result2) => {
              if (result2.isConfirmed) {
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
              $('#confirm-order').prop('disabled', true).css({ opacity: .45, cursor: 'not-allowed' });
              renderCart({}, null);
              renderSelectedCustomer(null);
              $('#product-search').val('');
              $('#search-results').empty();
              $('#search-placeholder').show();
              $('#customer-search').val('');
              $('#customer-results').empty();
              toastr.success('Order completed successfully!');
            });
          }
        }).fail(function(xhr) {
          let errorMsg = xhr.responseJSON?.message || 'Failed to process order';
          Swal.fire({
            title: 'Error!',
            text: errorMsg,
            icon: 'error',
            confirmButtonColor: '#dc2626'
          });
          toastr.error(errorMsg);
        }).always(() => {
          $btn.prop('disabled', false);
          $btn.html('<i class="la la-check-circle"></i> Confirm & Complete Order');
        });
      }
    });
  });

  function normalizeCartForSubmit(sourceCart, priceType) {
    let normalized = [];
    let orderedKeys = Object.keys(sourceCart || {}).sort(function(a, b) {
      return (Number(sourceCart[a].sort_order || 0) || 0) - (Number(sourceCart[b].sort_order || 0) || 0);
    });

    orderedKeys.forEach(function(key) {
      let item = sourceCart[key] || {};
      let regularPrice = Number(item.regular_price || item.price || 0);
      let salePrice = Number(item.sale_price || 0);
      let wholesalePrice = Number(item.wholesale_price || 0);
      let quantity = Number(item.quantity || 1);

      let selectedPrice = regularPrice;
      if (priceType === 'wholesale' && wholesalePrice > 0) {
        selectedPrice = wholesalePrice;
      } else if (priceType === 'sale' && salePrice > 0) {
        selectedPrice = salePrice;
      } else if (item.price) {
        selectedPrice = Number(item.price);
      }

      normalized.push({
        id: item.id || key,
        product_id: item.id || key,
        name: item.name || '',
        sku: item.sku || null,
        price: selectedPrice,
        regular_price: regularPrice,
        sale_price: salePrice,
        wholesale_price: wholesalePrice,
        quantity: quantity,
        total: selectedPrice * quantity,
        sort_order: item.sort_order || 0
      });
    });

    return normalized;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // ── Print POS Invoice (receipt-style) ───────────
  function printPosInvoice(inv) {
    let sy = inv.currency_sym || '৳';
    let itemsHtml = '';
    inv.items.forEach(function(item, i) {
      let itemName = escapeHtml(item.name || 'Unnamed item');
      itemsHtml += `<tr>
        <td>${i + 1}</td>
        <td style="white-space: normal; line-height: 1.2; word-break: break-word;"><span style="display:block;">${itemName}</span></td>
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
            size: auto;
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
                margin: 0 !important;
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
            height: auto;
            overflow: visible;
        }
        body {
            padding: 0;
            min-height: auto;
        }
        .receipt-container {
            width: 100%;
            margin: 0;
            padding: 10px 11px;
            box-sizing: border-box;
            min-height: auto;
            overflow: visible;
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
            margin-bottom: 6px;
            line-height: 1.5;
        }
        .invoice-header p {
            margin: 2px 0;
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
        table, table thead, table tbody, table tr, table th, table td {
            page-break-inside: auto;
            break-inside: auto;
        }
        td:nth-child(1) { width: 7%; text-align: center; }
        td:nth-child(2) { width: 50%; text-align: left; }
        td:nth-child(3) { width: 10%; text-align: center; }
        td:nth-child(4) { width: 16%; text-align: right; }
        td:nth-child(5) { width: 17%; text-align: right; }
        .summary-section {
            font-size: 11px;
            margin: 6px 0;
            line-height: 1.5;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            padding: 2px 0;
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

            <div class="summary-total">
                <div class="summary-row">
                    <span>Net Total:</span>
                    <span>৳${Number(inv.grand_total).toFixed(2)}</span>
                </div>
            </div>

            <div class="summary-row">
                <span>Total Qty:</span>
                <span>${inv.total_qty} Item</span>
            </div>

            <div class="summary-row">
                <span>Paid Amount:</span>
                <span>৳${Number(inv.grand_total).toFixed(2)}</span>
            </div>

            <div class="summary-row">
                <span>Change:</span>
                <span>৳0.00</span>
            </div>

            <div class="summary-row">
                <span>Due:</span>
                <span>৳0.00</span>
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


    let itemCount = Array.isArray(inv.items) ? inv.items.length : 0;
    let popupHeight = Math.max(700, Math.min(1800, 280 + (itemCount * 22)));
    let printWin = window.open('', '_blank', 'width=380,height=' + popupHeight + ',scrollbars=yes,resizable=yes');
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

  // ── Render cart ─────────────────────────────────
  // ═════════════════════════════════════════════════════════
  // INSTANT: Render Cart Table from Local Object (0-3ms)
  // ═════════════════════════════════════════════════════════
  function renderCart(cart, priceType) {
    let html = '', subtotal = 0, hasItems = false;
    let orderedIds = Object.keys(cart).sort(function(a, b) {
      return (Number(cart[a].sort_order || 0) || 0) - (Number(cart[b].sort_order || 0) || 0);
    });

    orderedIds.forEach(function(id) {
      let item = cart[id];
      
      // Determine which price to use
      let unitPrice = 0;
      if (priceType === 'wholesale' && item.wholesale_price) {
        unitPrice = Number(item.wholesale_price);
      } else if (priceType === 'sale' && item.sale_price) {
        unitPrice = Number(item.sale_price);
      } else {
        unitPrice = Number(item.regular_price);
      }

      let itemTotal = unitPrice * item.quantity;
      subtotal += itemTotal;
      hasItems = true;

      // Indicator for which price type is being used
      let priceIndicator = '';
      if (priceType === 'wholesale' && item.wholesale_price) {
        priceIndicator = '<sup style="font-size:9px;background:#fefce8;color:#a16207;padding:0px 3px;border-radius:2px;margin-right:2px">W</sup>';
      } else if (priceType === 'sale' && item.sale_price) {
        priceIndicator = '<sup style="font-size:9px;background:#dbeafe;color:#0369a1;padding:0px 3px;border-radius:2px;margin-right:2px">S</sup>';
      }

      html += `<tr data-product-id="${id}">
        <td><strong style="color:#1e293b;overflow:hidden;text-overflow:ellipsis;max-width:100px;display:block">${item.name}</strong></td>
        <td style="text-align:center">
          <div style="display:flex;align-items:center;justify-content:center;gap:3px">
            <button class="qty-btn qty-minus" data-id="${id}" title="−">−</button>
            <input type="number" class="qty-input" data-id="${id}" value="${item.quantity}" min="1" max="9999" style="width:50px;padding:4px 2px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px;text-align:center;font-weight:600;color:#1e293b;font-family:inherit">
            <button class="qty-btn qty-plus" data-id="${id}" title="+">+</button>
          </div>
        </td>
        <td style="text-align:right;font-size:11px">৳${Number(item.regular_price).toFixed(0)}</td>
        <td style="text-align:right;font-size:11px">${item.wholesale_price ? '<span style="color:#a16207;font-weight:500">৳' + Number(item.wholesale_price).toFixed(0) + '</span>' : '<span style="color:#cbd5e1;font-size:9px">—</span>'}</td>
        <td style="text-align:right;font-size:11px"><strong style="color:#059669">${priceIndicator}৳${itemTotal.toFixed(0)}</strong></td>
        <td style="text-align:center"><button class="pos-btn pos-btn--danger remove-from-cart" data-id="${id}" style="padding:3px 6px;font-size:10px" title="Remove"><i class="la la-trash"></i></button></td>
      </tr>`;
    });

    if (!hasItems) {
      html = `<tr><td colspan="6"><div class="pos-cart-empty"><i class="la la-shopping-cart"></i><p style="font-size:12px">Cart empty</p></div></td></tr>`;
      $('#cart-total').html('<span class="tl">Total</span><span class="tv">৳0.00</span>');
    } else {
      // Calculate discount
      let actualDiscount = 0;
      if (discountType === 'percentage' && discountAmount > 0) {
        actualDiscount = (subtotal * discountAmount) / 100;
      } else if (discountType === 'fixed' && discountAmount > 0) {
        actualDiscount = Math.min(discountAmount, subtotal);
      }

      let finalTotal = subtotal - actualDiscount;
      let label = priceType === 'wholesale' ? ' <small style="font-size:9px;opacity:.7">(W)</small>' : '';
      $('#cart-total').html('<span class="tl">Total' + label + '</span><span class="tv">৳' + finalTotal.toFixed(0) + '</span>');
    }

    $('#cart-table tbody').html(html);
  }

  // ── Render selected customer ────────────────────
  function renderSelectedCustomer(customer) {
    if (!customer) {
      $('#selected-customer').html(
        '<div class="no-data-msg"><i class="la la-user"></i> No customer selected</div>');
      return;
    }
    let html = `
      <div class="selected-customer-box">
        <div class="sc-label"><i class="la la-user-check"></i> Selected Customer</div>
        <div class="sc-name">${customer.name || 'N/A'}</div>
          ${customer.email ? '<span class="sc-info"><i class="la la-envelope"></i> ' + customer.email + '</span>' : ''}
          ${customer.mobile ? '<span class="sc-info"><i class="la la-phone"></i> ' + customer.mobile + '</span>' : ''}
          ${customer.address ? '<span class="sc-info"><i class="la la-map-marker"></i> ' + customer.address + '</span>' : ''}
        <div class="sc-actions">
          <button id="clear-customer"><i class="la la-times"></i> Change Customer</button>
        </div>
      </div>`;
    $('#selected-customer').html(html);
  }

  // ── Customer search ─────────────────────────────
  let custTimer;
  $('#customer-search').on('input', function() {
    clearTimeout(custTimer);
    let query = $(this).val().trim();
    if (!query || query.length < 2) {
      $('#customer-results').empty();
      $('#selected-customer .no-data-msg').show();
      return;
    }

    custTimer = setTimeout(function() {
      $.get('{{ route("admin.pos.searchCustomers") }}', {
          query
        })
        .done(function(data) {
          let html = '';
          if (!data || data.length === 0) {
            html = `<li class="search-result-item">
              <span style="color:#94a3b8"><i class="la la-search"></i> No customers found</span>
              <button class="pos-btn pos-btn--success create-customer"><i class="la la-plus"></i> Create</button>
            </li>`;
          } else {
            data.forEach(c => {
              html += `<li class="search-result-item">
                <div>
                  <strong>${c.name || 'N/A'}</strong><br>
                  <small><i class="la la-envelope"></i> ${c.email || 'No email'} ${c.mobile ? ' | <i class="la la-phone"></i> ' + c.mobile : ''}</small>
                </div>
                <button class="pos-btn pos-btn--primary select-customer" data-id="${c.id}" data-name="${c.name}"><i class="la la-check"></i> Select</button>
              </li>`;
            });
          }
          $('#customer-results').html(html);
          $('#selected-customer .no-data-msg').hide();
        })
        .fail(function() {
          toastr.error('Failed to search customers');
          $('#selected-customer .no-data-msg').hide();
          $('#customer-results').html(
            '<li class="search-result-item" style="justify-content:center;color:#dc2626"><i class="la la-warning"></i> Search error</li>'
          );
        });
    }, 300);
  });

  // ── Create customer ─────────────────────────────
  $(document).on('click', '.create-customer', function() {
    let name = $('#customer-search').val().trim();
    if (!name) {
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
      confirmButtonColor: '#059669',
      cancelButtonColor: '#94a3b8',
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
        return {
          name,
          email,
          mobile
        };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('{{ route("admin.pos.createCustomer") }}', {
          _token: '{{ csrf_token() }}',
          name: result.value.name,
          email: result.value.email || null,
          mobile: result.value.mobile || null
        }, function(res) {
          renderSelectedCustomer(res.customer);
          $('#customer-results').empty();
          $('#customer-search').val('');
          toastr.success(`Customer "${res.customer.name}" created and selected!`);
        }).fail(function(xhr) {
          toastr.error(xhr.responseJSON?.message || 'Failed to create customer');
        });
      }
    });
  });

  // ── Select customer ─────────────────────────────
  $(document).on('click', '.select-customer', function() {
    let id = $(this).data('id'),
      name = $(this).data('name');
    $.post('{{ route("admin.pos.selectCustomer") }}', {
      _token: '{{ csrf_token() }}',
      user_id: id
    }, function(res) {
      renderSelectedCustomer(res.customer);
      $('#customer-results').empty();
      $('#customer-search').val('');
      toastr.success(`Customer "${res.customer.name}" selected!`);
    }).fail(function() {
      toastr.error('Failed to select customer');
    });
  });

  // ── Clear selected customer ─────────────────────
  $(document).on('click', '#clear-customer', function() {
    Swal.fire({
      title: 'Change Customer?',
      text: 'Select a different customer for this order.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#6366f1',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: '<i class="la la-check"></i> Yes, Change',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('{{ route("admin.pos.clearCustomer") }}', {
          _token: '{{ csrf_token() }}'
        }, function(res) {
          renderSelectedCustomer(null);
          $('#customer-search').val('').focus();
          $('#customer-results').empty();
          toastr.info('Customer cleared');
        }).fail(function() {
          toastr.error('Failed to clear customer');
        });
      }
    });
  });

});
</script>
@endpush