@extends('adminlte::page')

@section('title', 'অন্যান্য বিক্রয়')

@section('content_header')
    <h1 style="color: green">অন্যান্য বিক্রয়</h1> 
@stop

@section('content') 

{{-- নতুন কোয়ান্টিটি ইনপুট মডাল --}}
<div id="quantityModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalProductName">পণ্যের নাম</h3>
      <button id="modalCloseBtn" class="close-btn">&times;</button>
    </div>
    <div class="modal-body">
      <img id="modalProductImage" src="" alt="Product Image" class="modal-img">
      <label for="quantityInput">পরিমাণ (Quantity)</label>
      <input type="number" id="quantityInput" value="1" min="1">
    </div>
    <div class="modal-actions">
      <button id="modalSubmitBtn" class="btn">কার্টে যোগ করুন</button>
    </div>
  </div>
</div>




<div class="grid">
  <!-- Left: Product list -->
  <div class="card">
    <div class="title">প্রোডাক্ট সার্চ</div>
    <input id="search" placeholder="পণ্যের নাম লিখে সার্চ করুন...">
    
    <div id="productGrid" class="product-grid">
      {{-- পণ্য এখানে লোড হবে --}}
    </div>
    
    <div id="pagination" class="mt-2 pagination-container"></div>
  </div>

  <!-- Right: Cart & Checkout -->
  <div class="card">
    <div class="title">কার্ট <span id="cartCounter" class="cart-counter"></span></div>
    <div class="cart-table-container">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>পণ্য</th>
            <th class="right">দাম</th>
            <th>Qty</th>
            <th class="right">টোটাল</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="cartBody"></tbody>
      </table>
    </div>
    
    {{-- কাস্টমার সেকশন --}}
    <div style="margin-top:12px">
        <!-- কাস্টমার টাইপ টগল সুইচ -->
        <div class="customer-toggle">
            <label class="switch-label">নিবন্ধিত কাস্টমার</label>
            <label class="switch">
                <input type="checkbox" id="saleTypeToggle">
                <span class="slider round"></span>
            </label>
            <label class="switch-label">গেস্ট কাস্টমার</label>
        </div>

        

        <!-- গেস্ট কাস্টমার সেকশন -->
        <div id="guestCustomerDiv" style="display: none;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <label>গেস্টের নাম <span class="text-danger">*</span></label>
                    <input type="text" id="guest_name" placeholder="নাম লিখুন">
                </div>
                <div>
                    <label>গেস্টের ফোন</label>
                    <input type="text" id="guest_phone" placeholder="ফোন নম্বর (ঐচ্ছিক)">
                </div>
            </div>
        </div>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px">
      <div>
        <label>কমিশন</label>
        <input id="discount" type="number" step="0.01" value="0">
      </div>
      <div>
        <label>অন্যান্য খরচ</label>
        <input id="shipping" type="number" step="0.01" value="0">
      </div>
      <div>
        <label>নগদ গ্রহণ</label>
        <input id="paid" type="number" step="0.01" value="0">
      </div>
      <div>
        <label>ডেলিভারি তারিখ:</label>
        <input id="ddate" type="date" value="{{ now()->toDateString() }}" required>
      </div>
    </div>
    <div style="margin:12px 0" id="summary"></div>
    <div style="display:flex;gap:8px" class="checkout-actions">
      <select id="payment_method" style="max-width:180px; color:green;">
        <option selected value="cash">ক্যাশ</option>
        <option value="bkash">বিকাশ</option>
        <option value="nagad">নগদ</option>
        <option value="card">কার্ড</option>
      </select>
      <button id="checkout" class="btn">চেকআউট</button>
    </div>
    <div class="muted" id="errorBox" style="margin-top:8px;"></div>
  </div>
</div>
@stop

@section('css')
<!-- ToastMagic -->
{!! ToastMagic::styles() !!}
 <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style> 
:root{--bg:#0b1020;--card:#121939;--text:#e9eef7;--accent:#4f8cff;--muted:#8ea0b8;--danger:#ff4f4f;--success:#28a745}body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;padding:20px}.grid{display:grid;grid-template-columns:3fr 2fr;gap:16px}.card{background:var(--card);border-radius:14px;padding:16px;display:flex;flex-direction:column}.title{font-weight:700;font-size:1.2rem;margin-bottom:10px}input,select,textarea{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:var(--text);box-sizing:border-box}.btn{padding:10px 14px;border:none;border-radius:10px;background:linear-gradient(180deg,var(--accent),#7aa8ff);color:#fff;cursor:pointer;width:100%;font-weight:600}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;font-size:14px}th{background:rgba(255,255,255,.05)}.right{text-align:right}.muted{color:var(--muted);font-size:12px}.mt-2{margin-top:1rem}.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;flex-grow:1;overflow-y:auto;max-height:65vh;padding:10px;background:rgba(0,0,0,0.2);border-radius:10px}.product-item{background:rgba(255,255,255,0.05);border:1px solid transparent;border-radius:10px;padding:8px;text-align:center;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease,background-color .2s ease}.product-item:hover{transform:translateY(-5px);box-shadow:0 4px 15px rgba(0,0,0,0.2);border-color:var(--accent)}.product-item.in-cart{border-color:var(--success);background:rgba(40,167,69,.1)}.product-item.in-cart .product-item-name{color:var(--success)}.product-item.in-cart:hover{box-shadow:0 4px 15px rgba(40,167,69,.3)}.cart-counter{display:inline-block;background-color:var(--accent);color:#fff;font-size:12px;padding:3px 10px;border-radius:12px;font-weight:700;margin-left:10px;vertical-align:middle}.product-item img{width:100%;height:110px;object-fit:cover;border-radius:8px;background-color:#ffffff10}.product-item-name{margin-top:8px;font-size:14px;font-weight:600;height:40px;overflow:hidden;color:var(--text)}.product-item-price{font-size:13px;color:var(--accent);font-weight:bold}.product-stock{font-size:11px;color:var(--muted)}.product-stock.low-stock{color:var(--danger);font-weight:bold}.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:flex;justify-content:center;align-items:center;z-index:1050;backdrop-filter:blur(5px)}.modal-content{background:var(--card);padding:20px;border-radius:14px;width:90%;max-width:350px;box-shadow:0 5px 25px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.1)}.modal-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;margin-bottom:15px}.modal-header h3{margin:0;font-size:1.2rem}.close-btn{background:none;border:none;color:var(--text);font-size:2rem;cursor:pointer}.modal-body{text-align:center}.modal-img{max-width:150px;height:auto;border-radius:10px;margin-bottom:15px}.modal-body label{display:block;margin-bottom:8px;font-size:1rem}.modal-actions{margin-top:20px}.pagination-container{text-align:center}.page-btn{padding:8px 12px;border:1px solid rgba(255,255,255,.08);border-radius:8px;background:rgba(255,255,255,.05);color:var(--text);cursor:pointer;margin:0 4px}.page-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}.cart-table-container{flex-grow:1;overflow-y:auto;max-height:250px}
/* Select2 ডার্ক থিম */
.select2-container--default .select2-selection--single{background-color:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;height:40px}.select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--text);line-height:40px}.select2-container--default .select2-selection--single .select2-selection__arrow{height:40px}.select2-dropdown{background-color:var(--card);border:1px solid rgba(255,255,255,.08)}.select2-container--default .select2-search--dropdown .select2-search__field{background-color:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:var(--text)}.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{background-color:var(--accent)}.select2-results__option{color:var(--text)}
/* কাস্টমার টগল সুইচ */
.customer-toggle { display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.switch-label { margin: 0 10px; font-weight: normal; color: var(--muted); cursor: pointer; }
.switch { position: relative; display: inline-block; width: 60px; height: 34px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: var(--accent); }
input:checked + .slider:before { transform: translateX(26px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }
#saleTypeToggle:checked ~ .switch-label:last-of-type,
#saleTypeToggle:not(:checked) ~ .switch-label:first-of-type { color: var(--text); font-weight: bold; }
/* নতুন কাস্টমার বাটন হোভার */
#addNewCustomerBtn:hover {
    background: linear-gradient(180deg, var(--success), #20c997) !important;
    transform: scale(1.02);
}
@media (max-width:992px){.grid{grid-template-columns:1fr}.product-grid{max-height:50vh}}@media (max-width:768px){.card{padding:12px}table th,table td{font-size:12px;padding:6px}input,select,textarea,.btn{font-size:14px;padding:10px}.title{font-size:16px}.checkout-actions{flex-direction:column}#payment_method{max-width:100%}}
</style>
@stop
 
@section('js')
<!-- ToastMagic -->
{!! ToastMagic::scripts() !!}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    let cart = []; 
    let selectedProduct = null;
    let saleType = 'customer';

    const productGrid = $("#productGrid");
    const paginationContainer = $("#pagination");
    const cartBody = $("#cartBody");
    const summaryDiv = $("#summary");
    const quantityModal = $('#quantityModal');
    const cartCounter = $('#cartCounter');

    function money(n) { return Number(n).toFixed(2); }
    function showAlert(type, text, icon) { Swal.fire({ title: type, text: text, icon: icon, confirmButtonText: 'OK', background: 'var(--card)', color: 'var(--text)' }); }

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function updateProductGridHighlighting() {
        const cartProductIds = new Set(cart.map(item => item.id));
        $('.product-item').each(function() {
            const productId = Number($(this).data('id'));
            if (cartProductIds.has(productId)) {
                $(this).addClass('in-cart');
            } else {
                $(this).removeClass('in-cart');
            }
        });
    }

    function loadProducts(q = "", page = 1) {
        $.getJSON('/pos/products', { q, page }, function(res) {
            let gridItems = "";
            if (res.data && res.data.length > 0) {
                res.data.forEach(p => {
                    const imageUrl = p.image ? `{{ asset('/') }}${p.image}` : `https://via.placeholder.com/150?text=No+Image`;
                    const stockClass = p.quantity < 10 ? 'low-stock' : '';
                    
                    gridItems += `
                    <div class="product-item" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}" data-image="${imageUrl}" title="${p.name}">
                        <img src="${imageUrl}" alt="${p.name}">
                        <div class="product-item-name">${p.name}</div>
                        <div class="product-item-price">৳${money(p.price)}</div>
                        <div class="product-stock ${stockClass}">স্টক: ${p.quantity}</div>
                    </div>`;
                });
            } else {
                gridItems = `<p class="muted" style="text-align:center; grid-column: 1 / -1;">কোনো পণ্য পাওয়া যায়নি।</p>`;
            }
            productGrid.html(gridItems);
            updateProductGridHighlighting();

            let pagination = "";
            if (res.links && res.data.length > 0) {
                res.links.forEach(link => {
                    if (link.url) {
                        const pageNum = new URL(link.url).searchParams.get('page');
                        if (pageNum) {
                            const label = $('<div/>').html(link.label).text();
                            pagination += `<button class="page-btn ${link.active ? 'active' : ''}" data-page="${pageNum}">${label}</button>`;
                        }
                    }
                });
            }
            paginationContainer.html(pagination);
        }).fail(xhr => {
            console.error(xhr.responseText);
            productGrid.html(`<p class="muted">পণ্য লোড করতে সমস্যা হয়েছে।</p>`);
        });
    }

    $("#customer").select2({ placeholder: "কাস্টমার সিলেক্ট করুন বা সার্চ করুন..." });

    $('#customer').on('change', function() {
        let id = $(this).val();
        if (id) {
            $.ajax({
                url: '/customerDuesserch/' + id, type: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.invoise > 0) {
                        Swal.fire({
                            title: `এই কাস্টমারের কাছে ${response.invoise} ধাপে ${response.totalDue} টাকা বকেয়া রয়েছে।`,
                            icon: 'info', showCancelButton: true, confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33', confirmButtonText: 'বকেয়া পরিশোধ',
                            cancelButtonText: 'এখন না', background: 'var(--card)', color: 'var(--text)'
                        }).then((result) => { if (result.isConfirmed) window.location = response.redirect; });
                    }
                }
            });
        }
    });

    function loadCustomers(q = "") {
        $("#customerHelp").text('');
        $.getJSON('/pos/customers', { q }, function(res) {
            let opts = `<option value="">কাস্টমার সিলেক্ট করুন</option>`;
            if (res.length > 0) {
                res.forEach(c => {
                    opts += `<option value="${c.id}">${c.name} (${c.number || 'N/A'}) / ${c.shop_name || 'N/A'} / ${c.shop_address || 'N/A'}</option>`;
                });
                $("#customerHelp").text(`${res.length} জন কাস্টমার পাওয়া গেছে।`);
            } else {
                 $("#customerHelp").text('কোনো কাস্টমার পাওয়া যায়নি।');
            }
            $("#customer").html(opts).trigger('change');
        }).fail(xhr => {
            console.error(xhr.responseText);
            $("#customerHelp").text('কাস্টমার লোডে সমস্যা হয়েছে।');
        });
    }

    function renderCart() {
        let rows = "", subtotal = 0;
        cart.forEach((it, idx) => {
            const line = it.price * it.qty;
            subtotal += line;
            rows += `<tr>
                <td>${idx + 1}</td>
                <td>${it.name}</td>
                <td class="right">৳${money(it.price)}</td>
                <td><input type="number" min="1" value="${it.qty}" data-idx="${idx}" class="qtyInp" style="width:70px"></td>
                <td class="right">৳${money(line)}</td>
                <td><button class="rmv" data-idx="${idx}" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:1.2rem;">&times;</button></td>
            </tr>`;
        });
        cartBody.html(rows || `<tr><td colspan="6" class="muted" style="text-align:center;">কার্ট খালি</td></tr>`);
        
        cart.length > 0 ? cartCounter.text(`${cart.length} টি আইটেম`).show() : cartCounter.hide();
        updateProductGridHighlighting();

        const discount = Number($("#discount").val() || 0);
        const shipping = Number($("#shipping").val() || 0);
        const total = subtotal - discount + shipping;

        if (saleType === 'guest' && total > 0) {
            $('#paid').val(total.toFixed(2)).prop('disabled', true);
        } else {
            $('#paid').prop('disabled', false);
        }

        const paid = Number($("#paid").val() || 0);
        const due = Math.max(0, total - paid);

        summaryDiv.html(`
            <div style="display:flex; justify-content:space-between;"><span>সাবটোটাল:</span> <span>৳${money(subtotal)}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>কমিশন:</span> <span>৳${money(discount)}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>অন্যান্য খরচ:</span> <span>৳${money(shipping)}</span></div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size: 1.1rem;"><span>সর্বমোট:</span> <span>৳${money(total)}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>নগদ গ্রহণ:</span> <span>৳${money(paid)}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size: 1.1rem;"><b>বাকি:</b> <b>৳${money(due)}</b></div>
        `);
    }

    function openModal() {
        if (!selectedProduct) return;
        $('#modalProductName').text(selectedProduct.name);
        $('#modalProductImage').attr('src', selectedProduct.image);
        $('#quantityInput').val(1);
        quantityModal.fadeIn(200);
        $('#quantityInput').focus().select();
    }

    function closeModal() {
        quantityModal.fadeOut(200);
        selectedProduct = null;
    }

    // ===== নতুন কাস্টমার মডাল =====
    $('#addNewCustomerBtn').on('click', function() {
        $('#newCustomerModal').fadeIn(200);
        $('#new_customer_name').focus();
    });

    $('#customerModalCloseBtn, #newCustomerModal').on('click', function(e) {
        if (e.target === this || $(this).is('#customerModalCloseBtn')) {
            $('#newCustomerModal').fadeOut(200);
            $('#new_customer_name, #new_customer_number, #new_customer_shop_name, #new_customer_address').val('');
        }
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            if ($('#newCustomerModal').is(':visible')) {
                $('#newCustomerModal').fadeOut(200);
            } else {
                closeModal();
            }
        }
    });

    // নতুন কাস্টমার সংরক্ষণ
    $('#saveNewCustomerBtn').on('click', function() {
        const name = $('#new_customer_name').val().trim();
        const number = $('#new_customer_number').val().trim();
        const shop_name = $('#new_customer_shop_name').val().trim();
        const shop_address = $('#new_customer_address').val().trim();

        if (!name) {
            toastMagic.error("ত্রুটি!", "কাস্টমারের নাম লিখুন।");
            return;
        }
        if (!number) {
            toastMagic.error("ত্রুটি!", "মোবাইল নম্বর লিখুন।");
            return;
        }

        $.ajax({
            url: '/customers/quick-add',
            type: 'POST',
            data: {
                name: name,
                number: number,
                shop_name: shop_name,
                shop_address: shop_address
            },
            success: function(res) {
                toastMagic.success("সফল!", "নতুন কাস্টমার যুক্ত হয়েছে।");
                
                const newOption = new Option(
                    `${res.customer.name} (${res.customer.number}) / ${res.customer.shop_name || 'N/A'} / ${res.customer.shop_address || 'N/A'}`,
                    res.customer.id,
                    true,
                    true
                );
                $('#customer').append(newOption).trigger('change');
                
                $('#newCustomerModal').fadeOut(200);
                $('#new_customer_name, #new_customer_number, #new_customer_shop_name, #new_customer_address').val('');
            },
            error: function(xhr) {
                let errorMsg = 'কাস্টমার যুক্ত করতে সমস্যা হয়েছে।';
                try {
                    const j = JSON.parse(xhr.responseText);
                    if (j.message) errorMsg = j.message;
                    if (j.errors) errorMsg = Object.values(j.errors).map(e => e.join(', ')).join('\n');
                } catch (e) {}
                toastMagic.error("ত্রুটি!", errorMsg);
            }
        });
    }); 

    // --- Event Listeners ---

    $(document).on('click', '.product-item', function() {
        const productId = Number($(this).data('id'));
        const existingItem = cart.find(it => it.id === productId);
        if (existingItem) {
            toastMagic.info("পণ্যটি ইতিমধ্যে কার্টে আছে।");
            return;
        }
        
        selectedProduct = {
            id: productId,
            name: $(this).data('name'),
            price: Number($(this).data('price')),
            image: $(this).data('image')
        };
        openModal();
    });

    $('#modalSubmitBtn').on('click', function() {
        const qty = Number($('#quantityInput').val());
        if (qty > 0 && selectedProduct) {
            cart.push({ ...selectedProduct, qty });
            renderCart();
            toastMagic.success("সফল!", `${qty} টি ${selectedProduct.name} কার্টে যোগ হয়েছে।`);
            closeModal();
        } else {
            toastMagic.error("ত্রুটি!", "সঠিক পরিমাণ লিখুন।");
        }
    });

    $('#quantityInput').on('keyup', function(e) { if (e.key === "Enter") $('#modalSubmitBtn').click(); });
    $('#modalCloseBtn, .modal-overlay').on('click', function(e) { if (e.target === this || $(this).is('#modalCloseBtn')) closeModal(); });

    $(document).on('input', '.qtyInp', function() {
        const idx = Number($(this).data('idx'));
        if (cart[idx]) {
            cart[idx].qty = Math.max(1, Number($(this).val() || 1));
            renderCart();
        }
    });

    $(document).on('click', '.rmv', function() {
        const idx = Number($(this).data('idx'));
        cart.splice(idx, 1);
        renderCart();
        toastMagic.info("সফল!", "পণ্যটি বাতিল করা হয়েছে।");
    });

    let productDebounce;
    $('#search').on('input', function() {
        clearTimeout(productDebounce);
        productDebounce = setTimeout(() => loadProducts($(this).val(), 1), 350);
    });
    
    $(document).on("click", ".page-btn", function() {
        if(!$(this).hasClass('active')) {
            loadProducts($("#search").val(), $(this).data("page"));
        }
    });

    $('#saleTypeToggle').on('change', function() {
        if ($(this).is(':checked')) {
            saleType = 'guest';
            $('#registeredCustomerDiv').hide();
            $('#guestCustomerDiv').show();
        } else {
            saleType = 'customer';
            $('#registeredCustomerDiv').show();
            $('#guestCustomerDiv').hide();
        }
        renderCart();
    });

    $('#discount, #shipping, #paid').on('input', renderCart);

    $('#checkout').click(function() {
        if (cart.length === 0) { showAlert('ত্রুটি', "আপনি কোনো পণ্য সিলেক্ট করেননি।", "error"); return; }
        
        if (saleType === 'customer' && !$('#customer').val()) {
            showAlert('ত্রুটি', "নিবন্ধিত কাস্টমার সিলেক্ট করুন।", "error"); return;
        }
        if (saleType === 'guest' && !$('#guest_name').val()) {
            showAlert('ত্রুটি', "গেস্ট কাস্টমারের নাম লিখুন।", "error"); return;
        }

        Swal.fire({
            title: "আপনি কি বিক্রয় সম্পন্ন করতে চান?",
            icon: 'info', showCancelButton: true, confirmButtonText: 'হ্যাঁ, করুন', cancelButtonText: 'না',
            background: 'var(--card)', color: 'var(--text)'
        }).then((result) => {
            if (result.isConfirmed) {
                const payload = {
                    sale_type: saleType,
                    customer_id: saleType === 'customer' ? $('#customer').val() : null,
                    guest_name: saleType === 'guest' ? $('#guest_name').val() : null,
                    guest_phone: saleType === 'guest' ? $('#guest_phone').val() : null,
                    discount: $('#discount').val() || 0,
                    shipping: $('#shipping').val() || 0,
                    paid: $('#paid').val() || 0,
                    payment_method: $('#payment_method').val(),
                    ddate : $('#ddate').val(),
                    items: cart.map(it => ({ product_id: it.id, price: it.price, quantity: it.qty }))
                };

                $.ajax({ 
    url: '/sales', 
    type: 'POST', 
    data: JSON.stringify(payload), 
    contentType: 'application/json',
    success: function(res) {
        // ১. কার্ট এবং ইনপুট খালি করা (আগের মতোই)
        cart = [];
        $('#customer, #discount, #shipping, #paid, #guest_name, #guest_phone').val('');
        $('#customer').val(null).trigger('change');
        renderCart();

        // ২. মডাল ওপেন করা (অটোমেটিক রিডাইরেক্ট বাদ দিয়ে)
        Swal.fire({
            title: 'বিক্রয় সফল হয়েছে!',
            text: "আপনি কি ইনভয়েস প্রিন্ট করতে চান?",
            icon: 'success',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '<i class="fas fa-file-alt"></i> Normal Print',
            denyButtonText: '<i class="fas fa-print"></i> Pos Print',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6', // নীল কালার
            denyButtonColor: '#17a2b8',    // টিল কালার
            cancelButtonColor: '#d33',     // লাল কালার
            allowOutsideClick: false       // বাইরে ক্লিক করলে বন্ধ হবে না
        }).then((result) => {
            
            // ৩. যদি Normal Print বাটনে ক্লিক করে
            if (result.isConfirmed) {
                if (res.redirect) {
                    window.location.href = res.redirect;
                }
            } 
            
            // ৪. যদি Pos Print বাটনে ক্লিক করে
            else if (result.isDenied) {
                // লোডিং এলার্ট দেখানো
                Swal.fire({
                    title: 'প্রিন্ট করা হচ্ছে...',
                    html: 'অনুগ্রহ করে অপেক্ষা করুন',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // ব্যাকএন্ডে POS প্রিন্ট রিকোয়েস্ট পাঠানো
                // নোট: আপনার কন্ট্রোলারের response এ 'pos_url' থাকতে হবে
                $.ajax({
                    url: res.pos_url, 
                    type: 'GET',
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: 'প্রিন্ট সফলভাবে সম্পন্ন হয়েছে।',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            // প্রিন্টারে সমস্যা হলে
                            Swal.fire('ত্রুটি!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        // নেটওয়ার্ক বা সার্ভার এরর
                        Swal.fire('ত্রুটি!', 'প্রিন্টারের সাথে সংযোগ স্থাপন করা যায়নি।', 'error');
                    }
                });
            }
            
            // ৫. Cancel বাটনে ক্লিক করলে মডাল বন্ধ হবে এবং পেজ নতুন অর্ডারের জন্য রেডি থাকবে।
        });
    },
    error: function(xhr) {
        // এরর হ্যান্ডলিং (আপনার আগের কোড)
        let errorMsg = 'সমস্যা হয়েছে, আবার চেষ্টা করুন।';
        try {
            const j = JSON.parse(xhr.responseText);
            if (j.message) errorMsg = j.message;
            if (j.errors) errorMsg = Object.values(j.errors).map(e => e.join('\n')).join('\n');
        } catch (e) {}
        showAlert('ত্রুটি', errorMsg, 'error');
    }
});
            }
        });
    });

    // --- Initial Load ---
    loadProducts();
    loadCustomers();
    renderCart();
});
</script> 
@stop