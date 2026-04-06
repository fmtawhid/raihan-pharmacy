@extends('admin.layouts.app')

@section('panel')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ═════════════════════════════════════════════════
   ADD STOCK — Bootstrap + Minimal Custom Styles
   ════════════════════════════════════════════════= */

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
  background: #e7f4f1;
  border-color: #0d6d42;
  color: #0d6d42;
}

.qty-btn:active {
  transform: scale(0.95);
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

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

.ct-icon--stock {
  background: #dbeafe;
  color: #0369a1;
}
</style>

<div class="p-3">
  <div class="row g-3">
    {{-- ADD STOCK INTERFACE --}}
    <div class="col-12">
      <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
          <div class="d-flex align-items-center gap-2 small fw-600 mb-3">
            <span class="ct-icon ct-icon--stock"><i class="la la-plus-circle"></i></span>
            Add Stock - Purchase Order
          </div>

          {{-- PURCHASE INFO --}}
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-600">Purchaser</label>
              <select id="purchaser-select" class="form-select form-select-sm">
                <option value="" selected>Select Purchaser...</option>
                @foreach($purchasers as $purchaser)
                  <option value="{{ $purchaser->id }}">{{ $purchaser->name }}</option>
                @endforeach
                <option value="new">+ Create New Purchaser</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">Batch Number</label>
              <input type="text" id="batch-no" class="form-control form-control-sm" placeholder="e.g. BATCH-2024-001">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">Purchase Date</label>
              <input type="date" id="purchased-at" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          {{-- SEARCH --}}
          <div class="search-input-wrapper">
            <i class="la la-search pi-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
            <input type="text" id="product-search" placeholder="Search products by name or SKU…" class="form-control form-control-sm" style="padding-left: 36px;">
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

        {{-- ITEMS TABLE --}}
        <div class="card-body p-2 flex-grow-1" style="overflow-y: auto; min-height: 0;">
          <table class="table table-sm table-hover mb-0" id="stock-table" style="table-layout: fixed;">
            <thead class="table-light">
              <tr style="font-size: 0.75rem;">
                <th style="width: 35%;">Product</th>
                <th style="width: 12%; text-align:center;">Current Stock</th>
                <th style="width: 12%; text-align:center;">Quantity</th>
                <th style="width: 15%; text-align:right;">Purchase Price</th>
                <th style="width: 15%; text-align:right;">Total Cost</th>
                <th style="width: 11%; text-align:center;">×</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <div id="empty-table" class="text-center py-5 text-muted">
            <i class="la la-inbox" style="font-size: 2rem; margin-bottom: 0.625rem; display: block;"></i>
            <p style="font-size: 12px; margin: 0;">No items added. Search and add products above.</p>
          </div>
        </div>

        {{-- SUMMARY --}}
        <div class="card-body p-2 border-top">
          <div class="row">
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Items:</span>
                <span id="total-items" class="h6 mb-0" style="color: #0369a1;">0</span>
              </div>
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Quantity:</span>
                <span id="total-quantity" class="h6 mb-0" style="color: #0369a1;">0</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Cost:</span>
                <span id="total-cost" class="h6 mb-0" style="color: #16a34a;">৳0.00</span>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button id="clear-items" class="btn btn-outline-danger btn-sm flex-grow-1">
              <i class="la la-trash"></i> Clear All
            </button>
            <button id="save-purchase" class="btn btn-success btn-sm flex-grow-1" disabled style="opacity:.45;cursor:not-allowed;">
              <i class="la la-save"></i> Save Purchase
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Purchaser Modal -->
<div id="create-purchaser-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Purchaser</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="create-purchaser-form">
        <div class="modal-body">
          <div class="mb-3">
            <label for="purchaser-name" class="form-label">Purchaser Name *</label>
            <input type="text" class="form-control" id="purchaser-name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="purchaser-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="purchaser-email" name="email">
          </div>
          <div class="mb-3">
            <label for="purchaser-phone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="purchaser-phone" name="phone">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Create Purchaser</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('script')
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

  let productCache = {};
  let stockItems = {};
  
  // ── LOAD PRODUCTS ON PAGE LOAD ──────────
  function loadProducts() {
    console.log('═══════════════════════════════════════════════════');
    console.log('📦 LOADING PRODUCTS FOR ADD STOCK - Starting...');
    console.log('═══════════════════════════════════════════════════');
    
    let apiUrl = '{{ route("admin.pos.getProducts") }}';
    console.log('Route URL:', apiUrl);
    
    // Load all products by fetching all pages
    loadAllProductPages(apiUrl, 1, 500);
  }

  function loadAllProductPages(apiUrl, page, perPage) {
    $.ajax({
      url: apiUrl,
      type: 'GET',
      data: {
        page: page,
        per_page: perPage
      },
      dataType: 'json',
      timeout: 30000,
      success: function(res) {
        console.log('✅ AJAX SUCCESS - Response received (Page ' + page + ')');
        console.log('Response structure:', res);
        
        if (!res.products) {
          console.error('❌ ERROR: products field missing!');
          console.error('Available fields:', Object.keys(res));
          toastr.error('Invalid response structure');
          return;
        }
        
        console.log('✓ Products array found, length:', res.products.length);
        
        if (res.products && res.products.length > 0) {
          res.products.forEach(function(p) {
            productCache[p.id] = p;
          });
          console.log('✓ Cached page ' + page + ': ' + res.products.length + ' products (Total: ' + Object.keys(productCache).length + ')');
          
          // Check if there are more pages
          if (res.pagination && res.pagination.current_page < res.pagination.total_pages) {
            console.log('➡️ Loading next page (' + (page + 1) + '/' + res.pagination.total_pages + ')...');
            // Load next page
            loadAllProductPages('{{ route("admin.pos.getProducts") }}', page + 1, perPage);
          } else {
            console.log('✓ All products loaded! Total: ' + Object.keys(productCache).length + ' products');
          }
        } else {
          console.warn('⚠️ WARNING: Empty products array on page ' + page + '!');
        }
      },
      error: function(xhr, status, error) {
        if (page === 1) {
          // Only show error on first page load failure
          console.error('═══════════════════════════════════════════════════');
          console.error('❌ AJAX ERROR - Failed to load products');
          console.error('═══════════════════════════════════════════════════');
          console.error('Status:', status);
          console.error('Error:', error);
          console.error('HTTP Status Code:', xhr.status);
          console.error('Response Text:', xhr.responseText);
          
          if (xhr.status === 0) {
            console.error('🔧 DIAGNOSIS: Network error - server may be down');
          } else if (xhr.status === 404) {
            console.error('🔧 DIAGNOSIS: Route not found - check route name');
            console.error('Expected route: admin.pos.getProducts');
          } else if (xhr.status === 403) {
            console.error('🔧 DIAGNOSIS: Permission denied');
          } else if (xhr.status === 500) {
            console.error('🔧 DIAGNOSIS: Server error - check Laravel logs');
          }
          
          toastr.error('Failed to load products');
        } else {
          // For subsequent pages, just log warning
          console.warn('⚠️ Failed to load page ' + page + ', stopping pagination');
        }
      }
    });
  }

  console.log('🚀 Page loaded, calling loadProducts()...');
  loadProducts();

  // ── SEARCH HANDLER ──────────────
  $('#product-search').on('input', function() {
    let query = $(this).val().trim().toLowerCase();
    
    console.log('🔍 Search input:', '"' + query + '"', '| Cache size:', Object.keys(productCache).length);
    
    if (!query) {
      $('#search-suggestions').removeClass('show');
      return;
    }

    let results = [];
    for (let id in productCache) {
      let p = productCache[id];
      if (p.name.toLowerCase().includes(query) || 
          (p.sku && p.sku.toLowerCase().includes(query))) {
        results.push(p);
      }
    }

    console.log('  → Found:', results.length, 'results');
    if (results.length === 0 && Object.keys(productCache).length > 0) {
      console.warn('  → Cache has products but no matches');
    } else if (Object.keys(productCache).length === 0) {
      console.warn('  ⚠️ Cache is EMPTY - run checkCache() in console');
    }

    if (results.length === 0) {
      $('#search-results-container').html('<div class="search-no-results" style="padding: 30px 20px;"><i class="la la-search"></i><p>No products found</p></div>');
    } else {
      let html = '';
      results.forEach(function(p) {
        let price = p.sale_price ?? p.regular_price ?? 0;
        html += `<div class="suggestion-item" data-product-id="${p.id}" role="button">
          <div class="suggestion-item-info">
            <strong>${p.name}</strong>
            <small>SKU: ${p.sku} • Stock: ${p.in_stock}</small>
          </div>
          <div class="suggestion-item-price">৳${Number(price).toFixed(2)}</div>
        </div>`;
      });
      $('#search-results-container').html(html);
    }
    $('#search-suggestions').addClass('show');
  });

  // ── CLICK SEARCH RESULT ──────────
  $(document).on('click', '.suggestion-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    let productId = $(this).data('product-id');
    let product = productCache[productId];

    if (!product) {
      toastr.error('Product not found');
      return;
    }

    // Add to items
    if (!stockItems[productId]) {
      stockItems[productId] = {
        id: product.id,
        name: product.name,
        sku: product.sku,
        current_stock: product.in_stock,
        quantity: 1,
        purchase_price: 0
      };
      toastr.success(`"${product.name}" added!`);
    } else {
      toastr.info('Already added to list');
    }

    renderStockTable();
    $('#product-search').val('').focus();
    $('#search-suggestions').removeClass('show');
  });

  // ── RENDER STOCK TABLE ──────────
  function renderStockTable() {
    let html = '';
    let itemCount = 0;
    let totalQty = 0;
    let totalCost = 0;

    for (let id in stockItems) {
      let item = stockItems[id];
      let cost = item.quantity * item.purchase_price;
      totalQty += item.quantity;
      totalCost += cost;
      itemCount++;

      html += `<tr data-product-id="${id}">
        <td style="padding: 0.5rem;"><strong>${item.name}</strong><br><small style="color:#6c757d">SKU: ${item.sku}</small></td>
        <td style="text-align:center; padding: 0.5rem;"><span style="background:#e0f2fe;color:#0369a1;padding:4px 8px;border-radius:4px;font-weight:600;font-size:11px">${item.current_stock}</span></td>
        <td style="text-align:center; padding: 0.5rem;">
          <input type="number" class="qty-input" data-product-id="${id}" value="${item.quantity}" min="1" style="width:50px;padding:6px;border:1px solid #dee2e6;border-radius:4px;font-size:11px;text-align:center;font-weight:600;">
        </td>
        <td style="text-align:right; padding: 0.5rem;"><input type="number" class="price-input" data-product-id="${id}" value="${item.purchase_price}" min="0" step="0.01" style="width:80px;padding:6px;border:1px solid #dee2e6;border-radius:4px;font-size:11px;text-align:right;"></td>
        <td style="text-align:right; padding: 0.5rem; font-weight:600; color:#16a34a;">৳${cost.toFixed(2)}</td>
        <td style="text-align:center; padding: 0.5rem;"><button class="btn btn-sm btn-danger remove-item" data-product-id="${id}" style="padding:2px 4px;font-size:10px;"><i class="la la-trash"></i></button></td>
      </tr>`;
    }

    if (itemCount === 0) {
      $('#stock-table tbody').html('<tr><td colspan="6"><!-- empty --></td></tr>');
      $('#empty-table').show();
      $('#save-purchase').prop('disabled', true).css({opacity: '.45', cursor: 'not-allowed'});
    } else {
      $('#stock-table tbody').html(html);
      $('#empty-table').hide();
      $('#save-purchase').prop('disabled', false).css({opacity: '1', cursor: 'pointer'});
    }

    $('#total-items').text(itemCount);
    $('#total-quantity').text(totalQty);
    $('#total-cost').text('৳' + totalCost.toFixed(2));
  }

  // ── QUANTITY INPUT CHANGE ──────
  $(document).on('change', '.qty-input', function() {
    let productId = $(this).data('product-id');
    let quantity = parseInt($(this).val()) || 1;
    
    if (quantity < 1) quantity = 1;
    stockItems[productId].quantity = quantity;
    renderStockTable();
  });

  // ── PRICE INPUT CHANGE ──────
  $(document).on('change', '.price-input', function() {
    let productId = $(this).data('product-id');
    let price = parseFloat($(this).val()) || 0;
    
    if (price < 0) price = 0;
    stockItems[productId].purchase_price = price;
    renderStockTable();
  });

  // ── REMOVE ITEM ──────
  $(document).on('click', '.remove-item', function() {
    let productId = $(this).data('product-id');
    delete stockItems[productId];
    renderStockTable();
  });

  // ── CLEAR ALL ITEMS ──────
  $('#clear-items').on('click', function() {
    Swal.fire({
      title: 'Clear All Items?',
      text: 'This will remove all items from the list',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'Yes, Clear All',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        stockItems = {};
        renderStockTable();
        toastr.info('All items cleared');
      }
    });
  });

  // ── SAVE PURCHASE ──────
  $('#save-purchase').on('click', function() {
    let purchaserId = $('#purchaser-select').val();
    let batchNo = $('#batch-no').val().trim();
    let purchasedAt = $('#purchased-at').val();

    if (!purchaserId) {
      toastr.error('Please select a purchaser');
      return;
    }

    if (!batchNo) {
      toastr.error('Please enter batch number');
      return;
    }

    if (Object.keys(stockItems).length === 0) {
      toastr.error('Please add at least one item');
      return;
    }

    let items = [];
    for (let id in stockItems) {
      items.push({
        id: stockItems[id].id,
        quantity: stockItems[id].quantity,
        purchase_price: stockItems[id].purchase_price
      });
    }

    let $btn = $(this);
    $btn.prop('disabled', true).html('<i class="la la-spinner fa-spin"></i> Processing...');

    $.ajax({
      url: '{{ route("admin.products.save.stock.purchase") }}',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        _token: '{{ csrf_token() }}',
        items: items,
        purchaser_id: purchaserId,
        batch_no: batchNo,
        purchased_at: purchasedAt
      }),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          Swal.fire({
            title: 'Success!',
            html: '<p>' + res.message + '</p>' +
                  '<p style="color:#6c757d;font-size:14px;">Total Items: <strong>' + res.data.total_items + '</strong></p>' +
                  '<p style="color:#6c757d;font-size:14px;">Total Quantity: <strong>' + res.data.total_quantity + '</strong></p>' +
                  '<p style="color:#16a34a;font-size:14px;">Total Cost: <strong>৳' + res.data.total_cost.toFixed(2) + '</strong></p>',
            icon: 'success',
            confirmButtonColor: '#10b981'
          }).then((result) => {
            stockItems = {};
            $('#batch-no').val('');
            $('#purchaser-select').val('');
            renderStockTable();
            loadProducts();
            toastr.success('Stock added successfully!');
          });
        } else {
          toastr.error(res.message || 'Failed to save purchase');
          $btn.prop('disabled', false).html('<i class="la la-save"></i> Save Purchase');
        }
      },
      error: function(xhr) {
        console.error('Error:', xhr);
        let errorMsg = 'Failed to save purchase';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        toastr.error(errorMsg);
        $btn.prop('disabled', false).html('<i class="la la-save"></i> Save Purchase');
      }
    });
  });

  // ── NEW PURCHASER CREATION ──────
  $('#purchaser-select').on('change', function() {
    if ($(this).val() === 'new') {
      let modal = new bootstrap.Modal(document.getElementById('create-purchaser-modal'));
      modal.show();
    }
  });

  $('#create-purchaser-form').on('submit', function(e) {
    e.preventDefault();
    // TODO: Implement purchaser creation
    toastr.info('Purchaser creation not yet implemented');
  });

  // ── SEARCH BOX INTERACTIONS ──────
  // Close dropdown when clicking outside search area
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.search-input-wrapper').length) {
      $('#search-suggestions').removeClass('show');
    }
  });

  // Close dropdown on ESC key
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      $('#search-suggestions').removeClass('show');
      $('#product-search').blur();
    }
  });

  // Prevent closing dropdown when clicking inside it
  $(document).on('click', '.search-suggestions', function(e) {
    e.stopPropagation();
  });

  // ═══ DEBUG UTILITIES (Window Functions) ═══
  window.checkCache = function() {
    console.log('════════════════════════════════════════');
    console.log('🔍 CACHE STATUS CHECK');
    console.log('════════════════════════════════════════');
    console.log('Total products in cache:', Object.keys(productCache).length);
    console.log('Cache contents (first 10 IDs):', Object.keys(productCache).slice(0, 10).join(', '));
    
    if (Object.keys(productCache).length === 0) {
      console.error('❌ Cache is EMPTY - Products not loaded!');
      console.log('Try running: loadProducts() to reload');
    } else {
      console.log('✅ Cache loaded successfully');
      let firstId = Object.keys(productCache)[0];
      let sample = productCache[firstId];
      console.log('Sample product:', sample);
      console.log('');
      console.log('First 5 products in cache:');
      Object.keys(productCache).slice(0, 5).forEach(function(id, idx) {
        let p = productCache[id];
        console.log(`  ${idx+1}. ${p.name} (ID:${p.id}, SKU:${p.sku}, Stock:${p.in_stock})`);
      });
    }
  };

  window.testSearch = function(query = 'test') {
    console.log('');
    console.log('🧪 TEST SEARCH - Query:', '"' + query + '"');
    console.log('Cache size:', Object.keys(productCache).length);
    
    if (Object.keys(productCache).length === 0) {
      console.error('❌ Cache is empty! Cannot test search.');
      console.log('Load products first: loadProducts()');
      return [];
    }
    
    let results = [];
    for (let id in productCache) {
      let p = productCache[id];
      let nameMatch = p.name.toLowerCase().includes(query.toLowerCase());
      let skuMatch = p.sku && p.sku.toLowerCase().includes(query.toLowerCase());
      if (nameMatch || skuMatch) {
        results.push(p);
      }
    }
    
    console.log(`✅ Found ${results.length} results:`);
    results.slice(0, 5).forEach(function(p, i) {
      console.log(`  ${i+1}. ${p.name} (SKU: ${p.sku}, Stock: ${p.in_stock})`);
    });
    
    return results;
  };

  window.runDiagnostics = function() {
    console.log('');
    console.log('╔════════════════════════════════════════════════════╗');
    console.log('║  ADD STOCK - COMPLETE DIAGNOSTIC REPORT            ║');
    console.log('╚════════════════════════════════════════════════════╝');
    console.log('');
    
    console.log('📦 CACHE STATUS:');
    let cacheSize = Object.keys(productCache).length;
    console.log('  Total products:', cacheSize);
    if (cacheSize === 0) {
      console.error('  ❌ Cache is EMPTY!');
    } else {
      console.log('  ✅ Cache OK');
      let sample = productCache[Object.keys(productCache)[0]];
      console.log('  Fields:', Object.keys(sample).join(', '));
    }
    console.log('');
    
    console.log('🛒 ITEMS IN TABLE:');
    let itemCount = Object.keys(stockItems).length;
    console.log('  Total items added:', itemCount);
    if (itemCount > 0) {
      Object.keys(stockItems).forEach(function(id) {
        let item = stockItems[id];
        console.log(`    - ${item.name}: Qty=${item.quantity}, Price=৳${item.purchase_price}`);
      });
    }
    console.log('');
    
    console.log('🔍 SEARCH TEST:');
    let test1 = testSearch('a');
    console.log('  Test "a":', test1.length, 'results');
    console.log('');
    
    console.log('💻 AVAILABLE COMMANDS:');
    console.log('  checkCache()          - Show cache status');
    console.log('  testSearch("query")   - Test search');
    console.log('  runDiagnostics()      - Full report (this command)');
    console.log('');
  };

  console.log('✅ Add Stock page initialized');
  console.log('   Run: runDiagnostics() for full system status');
  renderStockTable();
});
</script>
@endpush
