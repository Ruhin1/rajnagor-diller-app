@extends('adminlte::page')

@section('title', 'বিক্রয় আপডেট')

@section('content_header')
    <h1>বিক্রয় আপডেট করুন</h1>
@stop

@section('content')

{{-- নতুন কোয়ান্টিটি ইনপুট মডাল --}}
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

  <!-- Right: Cart & Update -->
  <div class="card">
    {{-- এখানে কার্ট কাউন্টার যুক্ত করা হয়েছে --}}
    <div class="title">কার্ট আপডেট <span id="cartCounter" class="cart-counter"></span></div>
    <div class="cart-table-container">
      <table>
        <thead>
          <tr>
            {{-- সিরিয়াল নম্বরের জন্য নতুন হেডার --}}
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
    <div style="margin-top:12px">
      <label>কাস্টমার (অপরিবর্তনযোগ্য)</label>
      <select disabled id="customer" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:green"></select>
      <div id="customerHelp" class="muted" style="margin-top:6px"></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px">
      <div>
        <label>কমিশন</label>
        <input id="discount" type="number" step="0.01" value="{{ $sale->discount }}">
      </div>
      <div>
        <label>অন্যান্য খরচ</label>
        <input id="shipping" type="number" step="0.01" value="{{ $sale->shipping }}">
      </div>
      <div>
        <label>নগদ গ্রহণ</label>
        <input id="paid" type="number" step="0.01" value="{{ $sale->paid }}">
      </div>
      <div>
        <label>ডেলিভারি তারিখ:</label>
        <input id="ddate" type="date" value="{{ optional($sale->ddate)->format('Y-m-d') ?? \Illuminate\Support\Str::of($sale->ddate)->substr(0,10) }}">
      </div>
    </div>
    <div style="margin:12px 0" id="summary"></div>
    <div style="display:flex;gap:8px" class="checkout-actions">
      <select id="payment_method" style="max-width:180px; color:green;">
        <option value="cash"  {{ $sale->payment_method==='cash'?'selected':'' }}>ক্যাশ</option>
        <option value="bkash" {{ $sale->payment_method==='bkash'?'selected':'' }}>বিকাশ</option>
        <option value="nagad" {{ $sale->payment_method==='nagad'?'selected':'' }}>নগদ</option>
        <option value="card"  {{ $sale->payment_method==='card'?'selected':'' }}>কার্ড</option>
      </select>
      <button id="updateSale" class="btn">বিক্রয় আপডেট করুন</button>
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
<style> 
:root{--bg:#0b1020;--card:#121939;--text:#e9eef7;--accent:#4f8cff;--muted:#8ea0b8;--danger:#ff4f4f;--success:#28a745}body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;padding:20px}.grid{display:grid;grid-template-columns:3fr 2fr;gap:16px}.card{background:var(--card);border-radius:14px;padding:16px;display:flex;flex-direction:column}.title{font-weight:700;font-size:1.2rem;margin-bottom:10px}input,select,textarea{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:var(--text);box-sizing:border-box}select:disabled{background:rgba(0,0,0,0.2);cursor:not-allowed}.btn{padding:10px 14px;border:none;border-radius:10px;background:linear-gradient(180deg,var(--accent),#7aa8ff);color:#fff;cursor:pointer;width:100%;font-weight:600}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;font-size:14px}th{background:rgba(255,255,255,.05)}.right{text-align:right}.muted{color:var(--muted);font-size:12px}.mt-2{margin-top:1rem}.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;flex-grow:1;overflow-y:auto;max-height:65vh;padding:10px;background:rgba(0,0,0,0.2);border-radius:10px}.product-item{background:rgba(255,255,255,0.05);border:1px solid transparent;border-radius:10px;padding:8px;text-align:center;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease,background-color .2s ease}.product-item:hover{transform:translateY(-5px);box-shadow:0 4px 15px rgba(0,0,0,0.2);border-color:var(--accent)}.product-item.in-cart{border-color:var(--success);background:rgba(40,167,69,.1)}.product-item.in-cart .product-item-name{color:var(--success)}.product-item.in-cart:hover{box-shadow:0 4px 15px rgba(40,167,69,.3)}.cart-counter{display:inline-block;background-color:var(--accent);color:#fff;font-size:12px;padding:3px 10px;border-radius:12px;font-weight:700;margin-left:10px;vertical-align:middle}.product-item img{width:100%;height:110px;object-fit:cover;border-radius:8px;background-color:#ffffff10}.product-item-name{margin-top:8px;font-size:14px;font-weight:600;height:40px;overflow:hidden;color:var(--text)}.product-item-price{font-size:13px;color:var(--accent);font-weight:bold}.product-stock{font-size:11px;color:var(--muted)}.product-stock.low-stock{color:var(--danger);font-weight:bold}.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:flex;justify-content:center;align-items:center;z-index:1050;backdrop-filter:blur(5px)}.modal-content{background:var(--card);padding:20px;border-radius:14px;width:90%;max-width:350px;box-shadow:0 5px 25px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.1)}.modal-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;margin-bottom:15px}.modal-header h3{margin:0;font-size:1.2rem}.close-btn{background:none;border:none;color:var(--text);font-size:2rem;cursor:pointer}.modal-body{text-align:center}.modal-img{max-width:150px;height:auto;border-radius:10px;margin-bottom:15px}.modal-body label{display:block;margin-bottom:8px;font-size:1rem}.modal-actions{margin-top:20px}.pagination-container{text-align:center}.page-btn{padding:8px 12px;border:1px solid rgba(255,255,255,.08);border-radius:8px;background:rgba(255,255,255,.05);color:var(--text);cursor:pointer;margin:0 4px}.page-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}.cart-table-container{flex-grow:1;overflow-y:auto;max-height:250px}@media (max-width:992px){.grid{grid-template-columns:1fr}.product-grid{max-height:50vh}}@media (max-width:768px){.card{padding:12px}table th,table td{font-size:12px;padding:6px}input,select,textarea,.btn{font-size:14px;padding:10px}.title{font-size:16px}.checkout-actions{flex-direction:column}#payment_method{max-width:100%}}
</style>
@stop
 
@section('js')
<!-- ToastMagic -->
{!! ToastMagic::scripts() !!}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    
    window.initialCart = @json($initialCart);
    let cart = [];
    let selectedProduct = null;

    const productGrid = $("#productGrid");
    const paginationContainer = $("#pagination");
    const cartBody = $("#cartBody");
    const summaryDiv = $("#summary");
    const quantityModal = $('#quantityModal');
    const cartCounter = $('#cartCounter'); // কার্ট কাউন্টার এলিমেন্ট

    function money(n) { return Number(n).toFixed(2); }
    function showAlert(type, text, icon) { Swal.fire({ title: type, text: text, icon: icon, confirmButtonText: 'OK', background: 'var(--card)', color: 'var(--text)' }); }

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // নতুন ফাংশন: কার্টে থাকা প্রোডাক্ট গ্রিডে হাইলাইট করবে
    function updateProductGridHighlighting() {
        const cartProductIds = new Set(cart.map(item => item.id));
        $('.product-item').each(function() {
            const productId = $(this).data('id');
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
            if (res.data.length > 0) {
                res.data.forEach(p => {
                    const imageUrl = p.image ? `{{asset('${p.image}')}}` : `https://via.placeholder.com/150?text=No+Image`;
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
                gridItems = `<p class="muted" style="text-align:center; grid-column: 1 / -1;">কোনো পণ্য পাওয়া যায়নি।</p>`;
            }
            productGrid.html(gridItems);
            updateProductGridHighlighting(); // প্রোডাক্ট লোড হওয়ার পর হাইলাইট আপডেট

            let pagination = "";
            if (res.links && res.data.length > 0) {
                res.links.forEach(link => {
                    if (link.url) {
                        const label = $('<div/>').html(link.label).text();
                        pagination += `<button class="page-btn ${link.active ? 'active' : ''}" data-page="${new URL(link.url).searchParams.get('page')}">
                            ${label}
                        </button>`;
                    }
                });
            }
            paginationContainer.html(pagination);
        }).fail(xhr => {
            console.error(xhr.responseText);
            productGrid.html(`<p class="muted">পণ্য লোড করতে সমস্যা হয়েছে।</p>`);
        });
    }

    function loadCustomers() {
        // আপডেট পেইজে কাস্টমার সার্চের প্রয়োজন নেই, শুধু নির্দিষ্ট কাস্টমার লোড হবে
        $("#customer").html('<option value="">লোড হচ্ছে...</option>');
        $.getJSON('/pos/customers', { id: "{{ $sale->customer_id }}" }, function(res) {
            let opts = '';
            if (res.length > 0) {
                const c = res[0];
                opts += `<option value="${c.id}">${c.name} (${c.number || 'N/A'}) / ${c.shop_name || 'N/A'} / ${c.shop_address || 'N/A'}</option>`;

                $("#customerHelp").text('সিলেক্টেড: ' + `${c.name} (${c.number || 'N/A'}) / ${c.shop_name || 'N/A'} / ${c.shop_address || 'N/A'}`);
            } else {
                opts = `<option value="">কাস্টমার পাওয়া যায়নি</option>`;
            }
            $("#customer").html(opts).val("{{ $sale->customer_id }}");
        });
    }

    function renderCart() {
        let rows = "", subtotal = 0;
        cart.forEach((it, idx) => {
            const line = it.price * it.qty;
            subtotal += line;
            rows += `<tr>
                <td>${idx + 1}</td> {{-- সিরিয়াল নম্বর --}}
                <td>${it.name}</td>
                <td class="right">৳${money(it.price)}</td>
                <td><input type="number" min="1" value="${it.qty}" data-idx="${idx}" class="qtyInp" style="width:70px"></td>
                <td class="right">৳${money(line)}</td>
                <td><button class="rmv" data-idx="${idx}" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:1.2rem;">&times;</button></td>
            </tr>`;
        });
        // colspan="6" করা হয়েছে
        cartBody.html(rows || `<tr><td colspan="6" class="muted" style="text-align:center;">কার্ট খালি</td></tr>`);
        
        // কার্ট কাউন্টার আপডেট
        if(cart.length > 0) {
            cartCounter.text(`${cart.length} টি আইটেম`).show();
        } else {
            cartCounter.hide();
        }
        
        // গ্রিড হাইলাইট আপডেট
        updateProductGridHighlighting();

        const discount = Number($("#discount").val()||0);
        const shipping = Number($("#shipping").val()||0);
        const paid = Number($("#paid").val()||0);
        const total = subtotal - discount + shipping;
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

    $(document).on('click', '.product-item', function() {
        const existingItem = cart.find(it => it.id === $(this).data('id'));
        if (existingItem) {
            toastMagic.info("পণ্যটি ইতিমধ্যে কার্টে আছে", "আপনি কার্ট থেকে পরিমাণ পরিবর্তন করতে পারেন।");
            return;
        }
        selectedProduct = { id: Number($(this).data('id')), name: $(this).data('name'), price: Number($(this).data('price')), image: $(this).data('image') };
        openModal();
    });

    $('#modalSubmitBtn').on('click', function() {
        const qty = Number($('#quantityInput').val());
        if (qty > 0 && selectedProduct) {
            const existingItem = cart.find(it => it.id === selectedProduct.id);
            if (existingItem) {
                existingItem.qty += qty;
            } else {
                cart.push({ ...selectedProduct, qty });
            }
            renderCart();
            toastMagic.success("সফল!", `${qty} টি ${selectedProduct.name} কার্টে যোগ হয়েছে।`);
            closeModal();
        } else {
            toastMagic.error("ত্রুটি!", "সঠিক পরিমাণ লিখুন।");
        }
    });

    $('#quantityInput').on('keyup', function(e) { if (e.key === "Enter") $('#modalSubmitBtn').click(); });
    $('#modalCloseBtn, .modal-overlay').on('click', function(e) { if (e.target === this || $(this).is('#modalCloseBtn')) closeModal(); });
    $(document).on('keyup', function(e) { if (e.key === "Escape") closeModal(); });

    $(document).on('input', '.qtyInp', function() {
        cart[Number($(this).data('idx'))].qty = Math.max(1, Number($(this).val() || 1));
        renderCart();
    });
    $(document).on('click', '.rmv', function() {
        cart.splice(Number($(this).data('idx')), 1);
        renderCart();
        toastMagic.success("সফল!", "পণ্যটি বাতিল করা হয়েছে।");
    });
    
    let productDebounce = null;
    $('#search').on('input', function() {
        clearTimeout(productDebounce);
        productDebounce = setTimeout(() => loadProducts($(this).val()), 350);
    });
    $(document).on("click", ".page-btn", function() {
        if(!$(this).hasClass('active')) loadProducts($("#search").val(), $(this).data("page"));
    });

    $('#discount, #shipping, #paid').on('input', renderCart);

    $('#updateSale').click(function() {
        if (cart.length === 0) { showAlert('ত্রুটি', "কার্ট খালি। আপডেট করার জন্য অন্তত একটি পণ্য লাগবে।", "error"); return; }

        Swal.fire({
            title: "আপনি কি বিক্রয়টি আপডেট করতে চান?",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33', confirmButtonText: 'হ্যাঁ, আপডেট করুন', cancelButtonText: 'না',
            background: 'var(--card)', color: 'var(--text)'
        }).then((result) => {
            if (result.isConfirmed) {
                const payload = {
                    customer_id: $('#customer').val(),
                    discount: $('#discount').val() || 0,
                    shipping: $('#shipping').val() || 0,
                    paid: $('#paid').val() || 0,
                    payment_method: $('#payment_method').val(),
                    ddate: $('#ddate').val() || null,
                    items: cart.map(it => ({ product_id: it.id, price: it.price, quantity: it.qty }))
                };

                $.ajax({
                    url: "{{ route('sales.update', $sale->id) }}", type: 'PUT', data: JSON.stringify(payload), contentType: 'application/json',
                    success: function(res) {
                        showAlert('সফল', 'বিক্রয় সফলভাবে আপডেট করা হয়েছে', 'success');
                        if (res.redirect) setTimeout(() => { window.location = res.redirect; }, 1500);
                    },
                    error: function(xhr) {
                        let errorMsg = 'সমস্যা হয়েছে, আবার চেষ্টা করুন।';
                        try {
                            const j = JSON.parse(xhr.responseText);
                            if (j.message) errorMsg = j.message;
                            if (j.errors) errorMsg = Object.values(j.errors)[0][0];
                        } catch (e) { console.error(e); }
                        showAlert('ত্রুটি', errorMsg, 'error');
                    }
                });
            }
        });
    });

    // --- পেইজ লোডের সময় প্রাথমিক ডেটা লোড ---
    loadProducts();
    loadCustomers();
    cart = (window.initialCart || []).map(it => ({
        id: it.id, name: it.name, price: Number(it.price), qty: Number(it.qty)
    }));
    renderCart();
});
</script> 
@stop