@extends('admin.layouts.app')

@section('panel')
<div class="p-3">
  <div class="row g-3">
    {{-- ADD RETURN INTERFACE --}}
    <div class="col-12">
      <div class="card" style="display: flex; flex-direction: column;">
        <div class="card-header">
          <div class="d-flex align-items-center gap-2 small fw-600 mb-3">
            <span class="ct-icon" style="background: #fecaca; color: #dc2626;"><i class="la la-undo"></i></span>
            Add Return - Order Management
          </div>

          {{-- SEARCH ORDER --}}
          <div class="search-input-wrapper" style="position: relative;">
            <i class="la la-search pi-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
            <input type="text" id="order-search" placeholder="Search by Order ID or Customer Name..." class="form-control form-control-sm" style="padding-left: 36px;">
            <!-- Search Results Dropdown -->
            <div id="order-suggestions" class="search-suggestions" style="position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 0.375rem 0.375rem; z-index: 1000; overflow: hidden; padding: 0; margin: 0; max-height: 450px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); display: none;">
              <div class="search-results-header" style="background: #fff; padding: 0.625rem 1rem; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; font-size: 0.8125rem; font-weight: 600; color: #495057;">
                <i class="la la-search"></i>
                <span>Search Results</span>
              </div>
              <div id="order-results-container" style="flex: 1; overflow-y: auto; padding: 0; min-height: 0;"></div>
            </div>
          </div>
        </div>

        {{-- ORDER DETAILS + PRODUCTS --}}
        <div class="card-body p-2 flex-grow-1" style="overflow-y: auto; min-height: 0; display: none;" id="order-details-section">
          <div class="mb-3 p-3 bg-light rounded">
            <div class="row">
              <div class="col-md-3">
                <small class="text-muted fw-600">Order #</small><br>
                <strong id="display-order-id" style="font-size: 1.125rem; color: #0369a1;">-</strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted fw-600">Customer</small><br>
                <strong id="display-customer" style="color: #0d6d42;">-</strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted fw-600">Order Total</small><br>
                <strong id="display-total" style="color: #16a34a;">৳0.00</strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted fw-600">Order Date</small><br>
                <strong id="display-date" style="color: #6c757d;">-</strong>
              </div>
            </div>
          </div>

          {{-- PRODUCTS TABLE --}}
          <table class="table table-sm table-hover mb-0" id="return-table" style="table-layout: fixed;">
            <thead class="table-light">
              <tr style="font-size: 0.75rem;">
                <th style="width: 35%;">Product</th>
                <th style="width: 12%; text-align:center;">Ordered Qty</th>
                <th style="width: 12%; text-align:center;">Already Returned</th>
                <th style="width: 12%; text-align:center;">Return Qty</th>
                <th style="width: 15%; text-align:right;">Unit Price</th>
                <th style="width: 14%; text-align:right;">Refund Amount</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <div id="empty-products" class="text-center py-5 text-muted">
            <i class="la la-inbox" style="font-size: 2rem; margin-bottom: 0.625rem; display: block;"></i>
            <p style="font-size: 12px; margin: 0;">No order selected. Search and select an order above.</p>
          </div>
        </div>

        {{-- SUMMARY & ACTIONS --}}
        <div class="card-body p-2 border-top" id="return-summary-section" style="display: none;">
          <div class="row">
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Return Items:</span>
                <span id="total-return-items" class="h6 mb-0" style="color: #0369a1;">0</span>
              </div>
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Return Quantity:</span>
                <span id="total-return-qty" class="h6 mb-0" style="color: #0369a1;">0</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2" style="font-size: 0.875rem;">
                <span class="fw-600">Total Refund Amount:</span>
                <span id="total-refund" class="h6 mb-0" style="color: #dc2626;">৳0.00</span>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button id="clear-return" class="btn btn-outline-danger btn-sm flex-grow-1" style="display: none;">
              <i class="la la-trash"></i> Clear Returns
            </button>
            <button id="process-return" class="btn btn-danger btn-sm flex-grow-1" disabled style="opacity:.45;cursor:not-allowed;">
              <i class="la la-undo"></i> Process Return
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
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

.search-suggestions.show {
  display: flex !important;
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
}

.suggestion-item small {
  color: #6c757d;
  font-size: 0.6875rem;
  display: block;
}

.suggestion-item-status {
  flex-shrink: 0;
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-weight: 700;
  font-size: 0.75rem;
  white-space: nowrap;
}

.search-no-results {
  text-align: center;
  padding: 3.75rem 1.25rem;
  color: #6c757d;
  font-size: 0.875rem;
}
</style>

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
  let selectedOrder = null;
  let returnItems = {};
  let orderCache = [];

  // ── SEARCH ORDERS ──────────────────
  $('#order-search').on('input', function() {
    let query = $(this).val().trim().toLowerCase();
    
    if (!query) {
      $('#order-suggestions').removeClass('show');
      return;
    }

    $.ajax({
      url: '{{ route("admin.order.search") }}',
      type: 'GET',
      data: { query: query },
      dataType: 'json',
      success: function(res) {
        if (res.orders && res.orders.length > 0) {
          let html = '';
          res.orders.forEach(function(order) {
            html += `<div class="suggestion-item" data-order-id="${order.id}" role="button">
              <div class="suggestion-item-info">
                <strong>Order #${order.order_number}</strong>
                <small>Customer: ${order.user_name} • Total: ৳${Number(order.total_amount).toFixed(0)}</small>
              </div>
              <div class="suggestion-item-status">${order.status_text}</div>
            </div>`;
          });
          $('#order-results-container').html(html);
        } else {
          $('#order-results-container').html('<div class="search-no-results"><i class="la la-search"></i><p>No orders found</p></div>');
        }
        $('#order-suggestions').addClass('show');
      },
      error: function() {
        toastr.error('Failed to search orders');
      }
    });
  });

  // ── SELECT ORDER ───────────────────
  $(document).on('click', '.suggestion-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    let orderId = $(this).data('order-id');
    loadOrderDetails(orderId);
    
    $('#order-search').val('').focus();
    $('#order-suggestions').removeClass('show');
  });

  function loadOrderDetails(orderId) {
    $.ajax({
      url: '{{ route("admin.order.return.details") }}',
      type: 'GET',
      data: { order_id: orderId },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          selectedOrder = res.order;
          renderOrderDetails(res.order);
          renderReturnTable(res.order.items);
          $('#order-details-section').show();
          $('#return-summary-section').show();
        } else {
          toastr.error(res.message || 'Failed to load order');
        }
      },
      error: function() {
        toastr.error('Failed to load order details');
      }
    });
  }

  function renderOrderDetails(order) {
    $('#display-order-id').text('Order #' + order.order_number);
    $('#display-customer').text(order.user_name || 'Guest');
    $('#display-total').text('৳' + Number(order.total_amount).toFixed(2));
    $('#display-date').text(order.created_at);
  }

  function renderReturnTable(items) {
    let html = '';
    returnItems = {};

    items.forEach(function(item) {
      returnItems[item.id] = {
        id: item.id,
        product_id: item.product_id,
        name: item.product_name,
        ordered_qty: item.quantity,
        already_returned: item.already_returned || 0,
        return_qty: 0,
        unit_price: item.unit_price,
        refund: 0
      };

      let available = item.quantity - (item.already_returned || 0);
      
      html += `<tr data-item-id="${item.id}">
        <td style="padding: 0.5rem;"><strong>${item.product_name}</strong><br><small style="color:#6c757d">ID: ${item.product_id}</small></td>
        <td style="text-align:center; padding: 0.5rem;"><span style="background:#e0f2fe;color:#0369a1;padding:4px 8px;border-radius:4px;font-weight:600;font-size:11px">${item.quantity}</span></td>
        <td style="text-align:center; padding: 0.5rem;"><span style="background:#fee2e2;color:#dc2626;padding:4px 8px;border-radius:4px;font-weight:600;font-size:11px">${item.already_returned || 0}</span></td>
        <td style="text-align:center; padding: 0.5rem;">
          <input type="number" class="return-qty-input" data-item-id="${item.id}" value="0" min="0" max="${available}" style="width:50px;padding:6px;border:1px solid #dee2e6;border-radius:4px;font-size:11px;text-align:center;font-weight:600;">
        </td>
        <td style="text-align:right; padding: 0.5rem; font-weight:600;">৳${Number(item.unit_price).toFixed(0)}</td>
        <td style="text-align:right; padding: 0.5rem; font-weight:600; color:#dc2626;">৳<span class="item-refund">0.00</span></td>
      </tr>`;
    });

    if (html) {
      $('#return-table tbody').html(html);
      $('#empty-products').hide();
      $('#clear-return').show();
    } else {
      $('#return-table tbody').html('<tr><td colspan="6"><!-- empty --></td></tr>');
      $('#empty-products').show();
      $('#clear-return').hide();
    }
  }

  // ── RETURN QUANTITY CHANGE ────────
  $(document).on('change', '.return-qty-input', function() {
    let itemId = $(this).data('item-id');
    let returnQty = parseInt($(this).val()) || 0;
    
    if (returnItems[itemId]) {
      let available = returnItems[itemId].ordered_qty - returnItems[itemId].already_returned;
      
      if (returnQty > available) {
        $(this).val(available);
        returnQty = available;
      } else if (returnQty < 0) {
        $(this).val(0);
        returnQty = 0;
      }
      
      returnItems[itemId].return_qty = returnQty;
      returnItems[itemId].refund = returnQty * returnItems[itemId].unit_price;
      
      let refundAmount = returnItems[itemId].refund.toFixed(2);
      $(this).closest('tr').find('.item-refund').text(refundAmount);
      
      updateReturnSummary();
    }
  });

  function updateReturnSummary() {
    let totalItems = 0;
    let totalQty = 0;
    let totalRefund = 0;

    for (let id in returnItems) {
      if (returnItems[id].return_qty > 0) {
        totalItems++;
        totalQty += returnItems[id].return_qty;
        totalRefund += returnItems[id].refund;
      }
    }

    $('#total-return-items').text(totalItems);
    $('#total-return-qty').text(totalQty);
    $('#total-refund').text('৳' + totalRefund.toFixed(2));

    if (totalItems > 0) {
      $('#process-return').prop('disabled', false).css({opacity: '1', cursor: 'pointer'});
    } else {
      $('#process-return').prop('disabled', true).css({opacity: '.45', cursor: 'not-allowed'});
    }
  }

  // ── CLEAR RETURNS ──────────────────
  $('#clear-return').on('click', function() {
    Swal.fire({
      title: 'Clear Return Quantities?',
      text: 'This will reset all return quantities to 0',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'Yes, Clear',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $('.return-qty-input').val(0);
        $('.item-refund').text('0.00');
        for (let id in returnItems) {
          returnItems[id].return_qty = 0;
          returnItems[id].refund = 0;
        }
        updateReturnSummary();
        toastr.info('Return quantities cleared');
      }
    });
  });

  // ── PROCESS RETURN ────────────────
  $('#process-return').on('click', function() {
    let totalItems = Object.values(returnItems).filter(item => item.return_qty > 0).length;
    
    if (totalItems === 0) {
      toastr.error('Please add at least one item to return');
      return;
    }

    let items = [];
    for (let id in returnItems) {
      if (returnItems[id].return_qty > 0) {
        items.push({
          order_detail_id: returnItems[id].id,
          product_id: returnItems[id].product_id,
          return_qty: returnItems[id].return_qty,
          refund_amount: returnItems[id].refund
        });
      }
    }

    let totalRefund = items.reduce((sum, item) => sum + item.refund_amount, 0);

    Swal.fire({
      title: 'Confirm Return?',
      icon: 'info',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#94a3b8',
      confirmButtonText: '<i class="la la-check-circle"></i> Confirm Return',
      cancelButtonText: 'Cancel',
      html: `<div style="text-align:left;margin-top:20px">
        <p><strong>Total Return Items:</strong> ${items.length}</p>
        <p><strong>Total Return Quantity:</strong> ${items.reduce((sum, item) => sum + item.return_qty, 0)}</p>
        <p style="margin-top:10px"><strong>Total Refund Amount:</strong> <span style="color:#dc2626">৳${totalRefund.toFixed(2)}</span></p>
        <p style="font-size:12px;color:#6c757d;margin-top:15px">✓ Stock will be added back<br>✓ Profit will be reduced<br>✓ Return record will be created</p>
      </div>`,
    }).then((result) => {
      if (result.isConfirmed) {
        submitReturn(items, totalRefund);
      }
    });
  });

  function submitReturn(items, totalRefund) {
    let $btn = $('#process-return');
    $btn.prop('disabled', true).html('<i class="la la-spinner fa-spin"></i> Processing...');

    $.ajax({
      url: '{{ route("admin.order.save.return") }}',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        _token: '{{ csrf_token() }}',
        order_id: selectedOrder.id,
        items: items,
        total_refund: totalRefund
      }),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          Swal.fire({
            title: 'Success!',
            html: '<p>' + res.message + '</p>' +
                  '<p style="color:#6c757d;font-size:14px;">Total Items Returned: <strong>' + res.data.total_items + '</strong></p>' +
                  '<p style="color:#6c757d;font-size:14px;">Total Quantity Returned: <strong>' + res.data.total_qty + '</strong></p>' +
                  '<p style="color:#dc2626;font-size:14px;">Total Refund: <strong>৳' + res.data.total_refund.toFixed(2) + '</strong></p>',
            icon: 'success',
            confirmButtonColor: '#10b981'
          }).then(() => {
            // Reset form
            $('#order-search').val('');
            $('#order-details-section').hide();
            $('#return-summary-section').hide();
            selectedOrder = null;
            returnItems = {};
            toastr.success('Return processed successfully!');
          });
        } else {
          toastr.error(res.message || 'Failed to process return');
          $btn.prop('disabled', false).html('<i class="la la-undo"></i> Process Return');
        }
      },
      error: function(xhr) {
        console.error('Error:', xhr);
        let errorMsg = 'Failed to process return';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        toastr.error(errorMsg);
        $btn.prop('disabled', false).html('<i class="la la-undo"></i> Process Return');
      }
    });
  }

  // ── CLOSE SEARCH ON OUTSIDE CLICK ──
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.search-input-wrapper').length) {
      $('#order-suggestions').removeClass('show');
    }
  });

  // ── CLOSE SEARCH ON ESCAPE ────────
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
      $('#order-suggestions').removeClass('show');
      $('#order-search').blur();
    }
  });

  // ── PREVENT CLOSING DROPDOWN WHEN CLICKING INSIDE ──
  $(document).on('click', '#order-suggestions', function(e) {
    e.stopPropagation();
  });

  console.log('✅ Add Return page initialized');
});
</script>
@endpush
