@extends('adminlte::page')

@section('title', 'ডিলার বিক্রয়')

@section('content_header')
    <h1 style="color: green">ডিলার বিক্রয় (Dealer POS)</h1>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- কোয়ান্টিটি ইনপুটের জন্য মডাল --}}
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

{{-- নতুন ডিলার যোগ করার মডাল --}}
<div id="newDealerModal" class="modal-overlay" style="display: none;">
  <div class="modal-content" style="max-width: 450px;">
    <div class="modal-header">
      <h3>নতুন ডিলার যুক্ত করুন</h3>
      <button id="dealerModalCloseBtn" class="close-btn">&times;</button>
    </div>
    <div class="modal-body" style="text-align:left;">
      <div style="margin-bottom:12px;">
        <label>নাম <span style="color:var(--danger);">*</span></label>
        <input type="text" id="new_dealer_name" placeholder="ডিলারের নাম">
      </div>
      <div style="margin-bottom:12px;">
        <label>মোবাইল নম্বর <span style="color:var(--danger);">*</span></label>
        <input type="text" id="new_dealer_phone" placeholder="০১XXXXXXXXX">
      </div>
      <div style="margin-bottom:12px;">
        <label>প্রতিষ্ঠানের নাম</label>
        <input type="text" id="new_dealer_pname" placeholder="প্রতিষ্ঠানের নাম (ঐচ্ছিক)">
      </div>
      <div style="margin-bottom:12px;">
        <label>ঠিকানা</label>
        <textarea id="new_dealer_address" rows="2" placeholder="ঠিকানা লিখুন (ঐচ্ছিক)"></textarea>
      </div>
    </div>
    <div class="modal-actions">
      <button id="saveNewDealerBtn" class="btn">সংরক্ষণ করুন</button>
    </div>
  </div>
</div>

<div class="grid">
  <!-- Left: Product list -->
  <div class="card">
    <div class="title">প্রোডাক্ট সার্চ</div>
    <input id="search" placeholder="পণ্যের নাম লিখে সার্চ করুন...">
    <div id="productGrid" class="product-grid"></div>
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
              <th class="right">DP</th>
              <th>Qty</th>
              <th class="right">টোটাল</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="cartBody"></tbody>
        </table>
    </div>
    
    <div style="margin-top:12px">
        <div class="customer-toggle">
            <label class="switch-label">নিবন্ধিত ডিলার</label>
            <label class="switch">
                <input type="checkbox" id="saleTypeToggle">
                <span class="slider round"></span>
            </label>
            <label class="switch-label">গেস্ট/ওয়াকিং</label>
        </div>

        <div id="registeredDealerDiv">
            <label>ডিলার</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <select id="dealer" style="flex:1;">
                    <option></option>
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->id }}">{{ $dealer->name }} {{ $dealer->phone ? '('.$dealer->phone.')' : '' }} / {{$dealer->pname}} / {{$dealer->address}}</option>
                    @endforeach
                </select>
                <button type="button" id="addNewDealerBtn" class="btn" style="width:auto;padding:10px 16px;background:var(--success);" title="নতুন ডিলার যুক্ত করুন">
                    ➕ নতুন
                </button>
            </div>
            <div id="dealerHelp" class="muted" style="margin-top:6px"></div>
        </div>

        <div id="guestDealerDiv" style="display: none;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <label>নাম <span class="text-danger">*</span></label>
                    <input type="text" id="guest_name" placeholder="নাম লিখুন">
                </div>
                <div>
                    <label>ফোন</label>
                    <input type="text" id="guest_phone" placeholder="ফোন নম্বর (ঐচ্ছিক)">
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px">
      <div><label>কমিশন</label><input id="discount" type="number" step="0.01" value="0"></div>
      <div><label>অন্যান্য খরচ</label><input id="shipping" type="number" step="0.01" value="0"></div>
      <div><label>নগদ গ্রহণ</label><input id="paid" type="number" step="0.01" value="0"></div>
      <div><label>ডেলিভারি তারিখ</label><input id="ddate" type="date" value="{{ now()->toDateString() }}"></div>
    </div>

    <div style="margin:12px 0" id="summary"></div>
    <div style="display:flex;gap:8px" class="checkout-actions">
        <button id="checkout" class="btn">বিক্রয় সম্পন্ন করুন</button>
    </div>
    <div class="muted" id="errorBox" style="margin-top:8px;"></div>
  </div>
</div>
@stop

@section('css')
{!! ToastMagic::styles() !!}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
:root{--bg:#0b1020;--card:#121939;--text:#e9eef7;--accent:#4f8cff;--muted:#8ea0b8;--danger:#ff4f4f;--success:#28a745}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;padding:20px}
.grid{display:grid;grid-template-columns:3fr 2fr;gap:16px}
.card{background:var(--card);border-radius:14px;padding:16px;display:flex;flex-direction:column}
.title{font-weight:700;font-size:1.2rem;margin-bottom:10px}
input,select,textarea{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:var(--text);box-sizing:border-box}
.btn{padding:10px 14px;border:none;border-radius:10px;background:linear-gradient(180deg,var(--accent),#7aa8ff);color:#fff;cursor:pointer;width:100%;font-weight:600}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;font-size:14px}
th{background:rgba(255,255,255,.05)}
.right{text-align:right}
.muted{color:var(--muted);font-size:12px}
.mt-2{margin-top:1rem}
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;flex-grow:1;overflow-y:auto;max-height:65vh;padding:10px;background:rgba(0,0,0,0.2);border-radius:10px}
.product-item{background:rgba(255,255,255,0.05);border:1px solid transparent;border-radius:10px;padding:8px;text-align:center;cursor:pointer;transition:all .2s ease}
.product-item:hover{transform:translateY(-5px);box-shadow:0 4px 15px rgba(0,0,0,0.2);border-color:var(--accent)}
.product-item.in-cart{border-color:var(--success);background:rgba(40,167,69,.1)}
.cart-counter{display:inline-block;background-color:var(--accent);color:#fff;font-size:12px;padding:3px 10px;border-radius:12px;font-weight:700;margin-left:10px;vertical-align:middle}
.product-item img{width:100%;height:110px;object-fit:cover;border-radius:8px;background-color:#ffffff10}
.product-item-name{margin-top:8px;font-size:14px;font-weight:600;height:40px;overflow:hidden;color:var(--text)}
.product-item-price{font-size:13px;color:var(--accent);font-weight:bold}
.product-stock{font-size:11px;color:var(--muted)}
.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:flex;justify-content:center;align-items:center;z-index:1050;backdrop-filter:blur(5px)}
.modal-content{background:var(--card);padding:20px;border-radius:14px;width:90%;max-width:350px}
.modal-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;margin-bottom:15px}
.close-btn{background:none;border:none;color:var(--text);font-size:2rem;cursor:pointer}
.modal-body{text-align:center}
.modal-img{width:150px;height:150px;object-fit:cover;border-radius:10px;margin-bottom:15px;background-color:#ffffff10}
.modal-body label{display:block;margin-bottom:8px;font-size:1rem}
.modal-actions{margin-top:20px}
.pagination-container{text-align:center}
.page-btn{padding:8px 12px;border:1px solid rgba(255,255,255,.08);border-radius:8px;background:rgba(255,255,255,.05);color:var(--text);cursor:pointer;margin:0 4px}
.page-btn.active{background:var(--accent);color:#fff}
.cart-table-container{flex-grow:1;overflow-y:auto;max-height:250px}
.select2-container--default .select2-selection--single{background-color:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;height:40px}
.select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--text);line-height:40px}
.select2-container--default .select2-selection--single .select2-selection__arrow{height:40px}
.select2-dropdown{background-color:var(--card);border:1px solid rgba(255,255,255,.08)}
.select2-container--default .select2-search--dropdown .select2-search__field{background-color:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:var(--text)}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{background-color:var(--accent)}
.select2-results__option{color:var(--text)}
.customer-toggle{display:flex;align-items:center;justify-content:center;margin-bottom:12px}
.switch-label{margin:0 10px;font-weight:normal;color:var(--muted);cursor:pointer}
.switch{position:relative;display:inline-block;width:60px;height:34px}
.switch input{opacity:0;width:0;height:0}
.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;transition:.4s}
.slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background-color:white;transition:.4s}
input:checked+.slider{background-color:var(--accent)}
input:checked+.slider:before{transform:translateX(26px)}
.slider.round{border-radius:34px}
.slider.round:before{border-radius:50%}
#saleTypeToggle:checked~.switch-label:last-of-type,#saleTypeToggle:not(:checked)~.switch-label:first-of-type{color:var(--text);font-weight:bold}
#addNewDealerBtn:hover{background:linear-gradient(180deg,var(--success),#20c997)!important;transform:scale(1.02)}

/* মোবাইল রেসপন্সিভ */
@media (max-width:992px){
    .grid{grid-template-columns:1fr}
    .product-grid{max-height:50vh}
}
@media (max-width:768px){
    body{padding:10px}
    .card{padding:12px}
    .title{font-size:1rem}
    table th,table td{font-size:12px;padding:6px}
    input,select,textarea,.btn{font-size:14px;padding:8px}
    .product-grid{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;max-height:40vh}
    .product-item img{height:90px}
    .product-item-name{font-size:12px;height:35px}
    .product-item-price{font-size:11px}
    .modal-content{max-width:95%;padding:15px}
    .modal-img{width:120px;height:120px}
    .checkout-actions{flex-direction:column}
    .page-btn{padding:6px 10px;font-size:12px}
    .qtyInp{width:50px!important}
}
@media (max-width:480px){
    .product-grid{grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:6px}
    .product-item{padding:6px}
    .product-item img{height:70px}
    .product-item-name{font-size:11px;height:30px}
    table th,table td{font-size:11px;padding:4px}
    .cart-table-container{max-height:200px}
}
</style>
@stop

@section('js')
{!! ToastMagic::scripts() !!}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let cart = [];
    let selectedProduct = null;
    let saleType = 'dealer';
    const ASSET_URL = "{{ asset('/') }}";
    
    const productGrid = $("#productGrid"), paginationContainer = $("#pagination"), cartBody = $("#cartBody"),
          summaryDiv = $("#summary"), quantityModal = $('#quantityModal'), cartCounter = $('#cartCounter');

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function money(n) { return Number(n || 0).toFixed(2); }
    function showAlert(type, text, icon) { Swal.fire({ title: type, text: text, icon: icon, confirmButtonText: 'OK', background: 'var(--card)', color: 'var(--text)' }); }
    
    function updateProductGridHighlighting() {
        const cartProductIds = new Set(cart.map(item => item.id));
        $('.product-item').each(function() {
            const productId = Number($(this).data('id'));
            if (cartProductIds.has(productId)) { $(this).addClass('in-cart'); } 
            else { $(this).removeClass('in-cart'); }
        });
    }

    function loadProducts(q = "", page = 1) {
        $.getJSON('/pos/products', { q, page }, function(res) {
            let gridItems = "";
            if (res.data && res.data.length > 0) {
                res.data.forEach(p => {
                    const imageUrl = p.image ? `${ASSET_URL}${p.image}` : `https://via.placeholder.com/150?text=No+Image`;
                    const stockClass = p.quantity < 10 ? 'low-stock' : '';
                    gridItems += `<div class="product-item" data-id="${p.id}" data-name="${p.name}" data-price="${p.dp}" data-image="${imageUrl}" title="${p.name}"><img src="${imageUrl}" alt="${p.name}"><div class="product-item-name">${p.name}</div><div class="product-item-price">DP: ৳${money(p.dp)}</div><div class="product-stock ${stockClass}">স্টক: ${p.quantity}</div></div>`;
                });
            } else { gridItems = `<p class="muted" style="text-align:center; grid-column: 1 / -1;">কোনো পণ্য পাওয়া যায়নি।</p>`; }
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
        }).fail(xhr => console.error("Product loading failed:", xhr.responseText));
    }

    function renderCart() {
        let rows = "", subtotal = 0;
        cart.forEach((it, idx) => {
            const line = Number(it.price) * Number(it.qty);
            subtotal += line;
            rows += `<tr><td>${idx + 1}</td><td>${it.name}</td><td class="right">৳${money(it.price)}</td><td><input type="number" min="1" value="${it.qty}" data-idx="${idx}" class="qtyInp" style="width:70px"></td><td class="right">৳${money(line)}</td><td><button class="rmv" data-idx="${idx}" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:1.2rem;">&times;</button></td></tr>`;
        });
        cartBody.html(rows || `<tr><td colspan="6" class="text-center muted">কার্ট খালি</td></tr>`);
        cart.length ? cartCounter.text(`${cart.length} টি আইটেম`).show() : cartCounter.hide();
        updateProductGridHighlighting();
        
        const discount = Number($("#discount").val()||0), shipping = Number($("#shipping").val()||0), total = subtotal - discount + shipping;
        if (saleType === 'guest') { $('#paid').val(total.toFixed(2)).prop('disabled', true); }
        else { $('#paid').prop('disabled', false); }
        const paid = Number($("#paid").val()||0), due = Math.max(0, total - paid);
        summaryDiv.html(`<div style="display:flex;justify-content:space-between"><span>সাবটোটাল:</span><span>৳${money(subtotal)}</span></div><div style="display:flex;justify-content:space-between"><span>কমিশন:</span><span>৳${money(discount)}</span></div><div style="display:flex;justify-content:space-between"><span>অন্যান্য খরচ:</span><span>৳${money(shipping)}</span></div><hr style="border-color:rgba(255,255,255,0.1)"><div style="display:flex;justify-content:space-between;font-weight:bold;font-size:1.1rem"><span>সর্বমোট:</span><span>৳${money(total)}</span></div><div style="display:flex;justify-content:space-between"><span>নগদ গ্রহণ:</span><span>৳${money(paid)}</span></div><div style="display:flex;justify-content:space-between;font-weight:bold;font-size:1.1rem"><b>বাকি:</b><b>৳${money(due)}</b></div>`);
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

    $("#dealer").select2({ placeholder: "ডিলার সিলেক্ট করুন...", allowClear: true });
    
    // নতুন ডিলার মডাল
    $('#addNewDealerBtn').on('click', function() {
        $('#newDealerModal').fadeIn(200);
        $('#new_dealer_name').focus();
    });

    $('#dealerModalCloseBtn, #newDealerModal').on('click', function(e) {
        if (e.target === this || $(this).is('#dealerModalCloseBtn')) {
            $('#newDealerModal').fadeOut(200);
            $('#new_dealer_name, #new_dealer_phone, #new_dealer_pname, #new_dealer_address').val('');
        }
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            if ($('#newDealerModal').is(':visible')) {
                $('#newDealerModal').fadeOut(200);
            } else {
                closeModal();
            }
        }
    });

    // নতুন ডিলার সংরক্ষণ
    $('#saveNewDealerBtn').on('click', function() {
        const name = $('#new_dealer_name').val().trim();
        const phone = $('#new_dealer_phone').val().trim();
        const pname = $('#new_dealer_pname').val().trim();
        const address = $('#new_dealer_address').val().trim();

        if (!name) {
            toastMagic.error("ত্রুটি!", "ডিলারের নাম লিখুন।");
            return;
        }
        if (!phone) {
            toastMagic.error("ত্রুটি!", "মোবাইল নম্বর লিখুন।");
            return;
        }

        $.ajax({
            url: '/dealers/quick-add',
            type: 'POST',
            data: {
                name: name,
                phone: phone,
                pname: pname,
                address: address
            },
            success: function(res) {
                toastMagic.success("সফল!", "নতুন ডিলার যুক্ত হয়েছে।");
                
                const newOption = new Option(
                    `${res.dealer.name} (${res.dealer.phone}) / ${res.dealer.pname} / ${res.dealer.address}`,
                    res.dealer.id,
                    true,
                    true
                );
                $('#dealer').append(newOption).trigger('change');
                
                $('#newDealerModal').fadeOut(200);
                $('#new_dealer_name, #new_dealer_phone, #new_dealer_pname, #new_dealer_address').val('');
            },
            error: function(xhr) {
                let errorMsg = 'ডিলার যুক্ত করতে সমস্যা হয়েছে।';
                try {
                    const j = JSON.parse(xhr.responseText);
                    if (j.message) errorMsg = j.message;
                    if (j.errors) errorMsg = Object.values(j.errors).map(e => e.join(', ')).join('\n');
                } catch (e) {}
                toastMagic.error("ত্রুটি!", errorMsg);
            }
        });
    });

    $('#saleTypeToggle').on('change', function() {
        if ($(this).is(':checked')) {
            saleType = 'guest';
            $('#registeredDealerDiv').hide();
            $('#guestDealerDiv').show();
        } else {
            saleType = 'dealer';
            $('#registeredDealerDiv').show();
            $('#guestDealerDiv').hide();
        }
        renderCart();
    });

    $(document).on('click', '.product-item', function() {
        const productId = Number($(this).data('id'));
        if (cart.find(it => it.id === productId)) {
            toastMagic.info("পণ্যটি ইতিমধ্যে কার্টে আছে।");
            return;
        }
        selectedProduct = { id: productId, name: $(this).data('name'), price: Number($(this).data('price')), image: $(this).data('image') };
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
        if(cart[idx]) {
             cart[idx].qty = Math.max(1, Number($(this).val() || 1));
             renderCart();
        }
    });
    $(document).on('click', '.rmv', function() { cart.splice(Number($(this).data('idx')), 1); renderCart(); toastMagic.info("সফল!", "পণ্যটি বাতিল করা হয়েছে।"); });

    let prodDebounce;
    $('#search').on('input', function() { clearTimeout(prodDebounce); prodDebounce = setTimeout(() => loadProducts($(this).val(), 1), 350); });
    $(document).on("click", ".page-btn", function() { if(!$(this).hasClass('active')) loadProducts($("#search").val(), $(this).data("page")); });

    $('#dealer').on('change', function() {
        let id = $(this).val();
        if (id) {
            $.ajax({
                url: '/dealerDuesserch/' + id, type: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.invoise > 0) {
                        Swal.fire({
                            title: `এই ডিলারের কাছে ${response.invoise} ধাপে ${response.totalDue} টাকা বকেয়া রয়েছে।`,
                            icon: 'info', showCancelButton: true, confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33', confirmButtonText: 'বকেয়া পরিশোধ',
                            cancelButtonText: 'এখন না', background: 'var(--card)', color: 'var(--text)'
                        }).then((result) => { if (result.isConfirmed) window.location = response.redirect; });
                    }
                }
            });
        }
    });
    $('#discount, #shipping, #paid').on('input', renderCart);

    $('#checkout').on('click', function() {
        if (cart.length === 0) { showAlert('ত্রুটি', 'কার্ট খালি।', 'error'); return; }
        if (saleType === 'dealer' && !$('#dealer').val()) { showAlert('ত্রুটি', 'ডিলার সিলেক্ট করুন।', 'error'); return; }
        if (saleType === 'guest' && !$('#guest_name').val()) { showAlert('ত্রুটি', 'গেস্টের নাম লিখুন।', 'error'); return; }
        if (!$('#ddate').val()) { showAlert('ত্রুটি', "ডেলিভারি তারিখ দিন।", "error"); return; }
        
        Swal.fire({
            title: "আপনি কি বিক্রয় সম্পন্ন করতে চান?", icon: 'question', showCancelButton: true,
            confirmButtonText: 'হ্যাঁ, করুন', cancelButtonText: 'না', background: 'var(--card)', color: 'var(--text)'
        }).then((result) => {
            if (!result.isConfirmed) return;
            const payload = {
                sale_type: saleType,
                dealer_id: saleType === 'dealer' ? $('#dealer').val() : null,
                guest_name: saleType === 'guest' ? $('#guest_name').val() : null,
                guest_phone: saleType === 'guest' ? $('#guest_phone').val() : null,
                discount: $('#discount').val() || 0,
                shipping: $('#shipping').val() || 0,
                paid: $('#paid').val() || 0,
                ddate: $('#ddate').val(),
                items: cart.map(it => ({ product_id: it.id, quantity: it.qty }))
            };
            $.ajax({
    url: "{{ route('dealer.sales.store') }}",
    type: "POST", 
    data: JSON.stringify(payload), 
    contentType: "application/json",
    success: function(res) {
        // ১. ফর্ম এবং কার্ট রিসেট করা (আপনার আগের কোড)
        cart = [];
        $('#dealer, #guest_name, #guest_phone').val('');
        $('#dealer').val(null).trigger('change');
        $('#discount, #shipping, #paid').val(0);
        $('#ddate').val('{{ now()->toDateString() }}');
        renderCart();

        // ২. প্রিন্ট অপশন মডাল ওপেন করা
        // Swal.fire({
        //     title: 'বিক্রয় সম্পন্ন হয়েছে!',
        //     text: "আপনি কি ইনভয়েস প্রিন্ট করতে চান?",
        //     icon: 'success',
        //     showCancelButton: true,
        //     showDenyButton: true,
        //     confirmButtonText: '<i class="fas fa-file-alt"></i> Normal Print',
        //     denyButtonText: '<i class="fas fa-print"></i> Pos Print',
        //     cancelButtonText: 'Cancel',
        //     confirmButtonColor: '#3085d6', // নীল
        //     denyButtonColor: '#17a2b8',    // টিল
        //     cancelButtonColor: '#d33',     // লাল
        //     allowOutsideClick: false
        // }).then((result) => {
            
        //     // ৩. Normal Print বাটন চাপলে
        //     if (result.isConfirmed) {
        //         if (res.redirect) {
        //             window.location.href = res.redirect;
        //         }
        //     } 
            
        //     // ৪. Pos Print বাটন চাপলে
        //     else if (result.isDenied) {
        //         // লোডিং দেখানো
        //         Swal.fire({
        //             title: 'প্রিন্ট করা হচ্ছে...',
        //             html: 'অনুগ্রহ করে অপেক্ষা করুন',
        //             allowOutsideClick: false,
        //             didOpen: () => {
        //                 Swal.showLoading();
        //             }
        //         });

        //         // ব্যাকএন্ডে POS প্রিন্ট রিকোয়েস্ট পাঠানো
        //         $.ajax({
        //             url: res.pos_url, // নিশ্চিত করুন কন্ট্রোলার থেকে pos_url আসছে
        //             type: 'GET',
        //             success: function(response) {
        //                 if(response.success) {
        //                     Swal.fire({
        //                         icon: 'success',
        //                         title: 'সফল!',
        //                         text: 'প্রিন্ট সফলভাবে সম্পন্ন হয়েছে।',
        //                         timer: 2000,
        //                         showConfirmButton: false
        //                     });
        //                 } else {
        //                     Swal.fire('ত্রুটি!', response.message, 'error');
        //                 }
        //             },
        //             error: function(xhr) {
        //                 Swal.fire('ত্রুটি!', 'প্রিন্টারের সাথে সংযোগ স্থাপন করা যায়নি।', 'error');
        //             }
        //         });
        //     }
            
        //     // ৫. Cancel করলে মডাল বন্ধ হবে এবং নতুন সেলের জন্য পেজ রেডি থাকবে
        // });
                Swal.fire({
                                        title: 'প্রিন্ট অপশন সিলেক্ট করুন',
                                        text: "আপনি কোন ফরমেটে প্রিন্ট করতে চান?",
                                        icon: 'question',
                                        showCancelButton: true,
                                        showDenyButton: true,
                                        showCloseButton: true,
                                        confirmButtonText: '<i class="fas fa-file-alt"></i> Normal Print (A4)',
                                        denyButtonText: '<i class="fas fa-receipt"></i> POS Print (Rongta)',
                                        cancelButtonText: '<i class="fab fa-bluetooth"></i> Bluetooth Print (Dotmax)',
                                        confirmButtonColor: '#3085d6',
                                        denyButtonColor: '#28a745',
                                        cancelButtonColor: '#17a2b8', // Info color for Bluetooth
                                        reverseButtons: true
                                    }).then((result) => {
                                        // ১. Normal Print (A4)
                                        if (result.isConfirmed) {
                                            window.location.href = res.redirect;
                                        } 
                                        // ২. POS Print (Rongta Network Printer)
                                        else if (result.isDenied) {
                                            printToRongta(res.pos_url);
                                        }
                                        // ৩. Bluetooth Print (Dotmax)
                                        else if (result.dismiss === Swal.DismissReason.cancel) {
                                            printToBluetooth(res.bluetooth_url);
                                        }
                                    });

                                                                /**
                                     * Rongta POS Printer (Network)
                                     */
                                    function printToRongta(posUrl) {
                                        Swal.fire({
                                            title: 'প্রিন্ট করা হচ্ছে...',
                                            text: 'Rongta Printer এ পাঠানো হচ্ছে',
                                            allowOutsideClick: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        $.ajax({
                                            url: posUrl,
                                            type: 'GET',
                                            success: function(response) {
                                                if(response.success) {
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'সফল!',
                                                        text: 'POS প্রিন্ট সফলভাবে সম্পন্ন হয়েছে।',
                                                        timer: 2000,
                                                        showConfirmButton: false
                                                    });
                                                } else {
                                                    showPrinterError(response.message);
                                                }
                                            },
                                            error: function(xhr) {
                                                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'অজানা ত্রুটি';
                                                showPrinterError(errorMsg);
                                            }
                                        });
                                    }

                                    /**
                                     * Dotmax Bluetooth Printer (RawBT)
                                     */
                                    function printToBluetooth(bluetoothUrl) {
                                        Swal.fire({
                                            title: 'Bluetooth প্রিন্ট...',
                                            text: 'RawBT App খুলছে',
                                            allowOutsideClick: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        $.ajax({
                                            url: bluetoothUrl,
                                            type: 'GET',
                                            success: function(response) {
                                                if(response.success) {
                                                    // RawBT Intent URL খুলুন
                                                    window.location.href = response.intent_url;
                                                    
                                                    // Success message
                                                    setTimeout(() => {
                                                        Swal.fire({
                                                            icon: 'success',
                                                            title: 'RawBT App খুলছে',
                                                            text: 'Dotmax Printer সিলেক্ট করুন এবং Print চাপুন',
                                                            timer: 3000,
                                                            showConfirmButton: false
                                                        });
                                                    }, 500);
                                                } else {
                                                    showPrinterError(response.message || 'Bluetooth প্রিন্টিং ব্যর্থ');
                                                }
                                            },
                                            error: function(xhr) {
                                                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Bluetooth প্রিন্টিং এরর';
                                                showPrinterError(errorMsg);
                                            }
                                        });
                                    }

                                    /**
                                     * Error Message
                                     */
                                    function showPrinterError(msg) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'প্রিন্ট ত্রুটি!',
                                            html: 'প্রিন্টার অন করুন, অথবা প্রিন্টারের সাথে কানেক্ট করা যায় নি।<br><small style="color:red">Error: ' + msg + '</small>',
                                            confirmButtonText: 'ঠিক আছে'
                                        });
                                    }
    },
    error: function(xhr) {
        // এরর হ্যান্ডলিং (আপনার আগের কোড)
        let msg = "একটি সমস্যা হয়েছে।";
        try { 
            const j = JSON.parse(xhr.responseText); 
            msg = j.message || Object.values(j.errors).flat()[0]; 
        } catch (e) {}
        showAlert('ত্রুটি', msg, 'error');
    }
});
        });
    });

    loadProducts();
    renderCart();
});
</script>
@stop