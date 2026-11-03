@extends('adminlte::page')

@section('title', 'প্রোডাক্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0" style="color: #007bff;">প্রোডাক্ট লিস্ট</h1>
        <button id="openModal" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>পোডাক্ট যুক্ত করুন
        </button>
    </div>
@stop

@section('content')
<div class="card shadow-lg">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>আইডি</th>
                    <th>পোডাক্টের নাম</th>
                    <th>বর্তমান স্টক</th>
                    <th>পোডাক্টের ছবি</th>
                    <th>কোম্পানি প্রদত্ত দাম</th>
                    <th>ডিপু প্রদত্ত দাম</th>
                    <th>ডিলার প্রদত্ত দাম</th>
                    <th>ট্রেড প্রদত্ত দাম</th>
                    <th>এম আর পি প্রদত্ত দাম</th>
                    <th>স্ট্যাটাস</th>
                    <th>একশন</th>
                  </tr>
                </thead>
                <tbody id="productTable">
                    {{-- ডেটা এখানে লোড হবে --}}
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Insert Modal -->
<div id="productModal" class="modal"> 
    <div class="modal-content">
        <div class="modal-header">
            <h2>নতুন প্রোডাক্ট যুক্ত করুন</h2>
            <span class="close" id="closeModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="productForm" enctype="multipart/form-data">
                <label for="name">পোডাক্ট এর নাম দিন:</label>
                <input class="input" type="text" id="name" name="name" required />

                <label for="quantity">পোডাক্ট স্টক:</label>
                <input class="input" type="number" id="quantity" name="quantity" required />

                <label for="image">পোডাক্টের ছবি:</label>
                <input class="input" type="file" id="image" name="image" accept="image/*" required />

                <label for="cp">কোম্পানি প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="cp" name="cp" step="0.01" required /></div>

                <label for="dipo">ডিপু প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="dipo" name="dipo" step="0.01" required /></div>

                <label for="dp">ডিলার প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="dp" name="dp" step="0.01" required /></div>

                <label for="tp">ট্রেড প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="tp" name="tp" step="0.01" required /></div>

                <label for="mrp">MRP:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="mrp" name="mrp" step="0.01" required /></div>

                <div class="actions"><button type="submit" class="btn btn-success">প্রোডাক্ট সেভ করুন</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>প্রোডাক্ট আপডেট করুন</h2>
            <span class="close" id="closeUpdate">&times;</span>
        </div>
        <div class="modal-body">
            <form id="updateForm" enctype="multipart/form-data">
                <input type="hidden" id="update_id" name="id">

                <label for="update_name">পোডাক্ট এর নাম:</label>
                <input class="input" type="text" id="update_name" name="name" required />

                <label for="update_image">নতুন ছবি (ঐচ্ছিক):</label>
                <input class="input" type="file" id="update_image" name="image" accept="image/*" />

                <label for="update_cp">কোম্পানি প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="update_cp" name="cp" step="0.01" required /></div>

                <label for="update_dipo">ডিপু প্রাইজ:</label>
                <div class="input-group"> 
                    <span class="prefix">৳</span>
                    <input class="input" type="number" id="update_dipo" name="dipo" step="0.01" required />
                </div>

                <label for="update_dp">ডিলার প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="update_dp" name="dp" step="0.01" required /></div>

                <label for="update_tp">ট্রেড প্রাইজ:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="update_tp" name="tp" step="0.01" required /></div>

                <label for="update_mrp">MRP:</label>
                <div class="input-group"><span class="prefix">৳</span><input class="input" type="number" id="update_mrp" name="mrp" step="0.01" required /></div>

                <div class="actions"><button type="submit" class="btn btn-primary">আপডেট করুন</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="fas fa-history me-2"></i>স্টক মুভমেন্ট লগ — <span id="logProductName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
 
            <div class="modal-body">
                <div id="stockLogTableWrap" class="table-responsive">
                    <table id="stockLogTable" class="table table-bordered table-striped table-sm align-middle mb-0">
                        <thead class="table-secondary sticky-top">
                            <tr>
                                <th width="4%">#</th>
                                <th style="min-width:130px">তারিখ ও সময়</th>
                                <th style="min-width:100px">ধরন</th>
                                <th class="text-center" style="min-width:70px">পরিমাণ</th>
                                <th class="text-center" style="min-width:80px">আগের স্টক</th>
                                <th class="text-center" style="min-width:80px">নতুন স্টক</th>
                                <th style="min-width:100px">করেছেন</th>
                                <th>নোট</th>
                            </tr>
                        </thead>
                        <tbody id="stockLogTableBody">
                            {{-- Dynamic Content --}}
                        </tbody>
                    </table>
                </div>
            </div>
 
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- এখানে কোনো পরিবর্তন নেই --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS কোড আগের মতোই থাকবে */
        :root {
            --primary-color: #007bff; --success-color: #28a745; --danger-color: #dc3545;
            --light-gray: #f8f9fa; --dark-gray: #343a40; --text-color: #495057;
            --card-bg: #ffffff; --border-color: #dee2e6; --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { background-color: var(--light-gray); color: var(--text-color); }
        .table-responsive { width: 100%; overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; vertical-align: middle; border-top: 1px solid var(--border-color); }
        .table thead th { border-bottom: 2px solid var(--border-color); }
        .table-hover tbody tr:hover { background-color: rgba(0, 123, 255, 0.05); }
        .product-image { width: 70px; height: 70px; border-radius: 8px; object-fit: cover; box-shadow: var(--shadow); }
        .inactive-row { background-color: #fbebeb !important; opacity: 0.8; }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; }
        .modal-content { background-color: var(--card-bg); margin: auto; border: 1px solid var(--border-color); width: 90%; max-width: 500px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); animation: slide-down 0.3s ease-out; }
        @keyframes slide-down { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
        .modal-header h2 { font-size: 1.25rem; margin: 0; }
        .close { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; transition: color 0.2s; }
        .close:hover { color: var(--danger-color); }
        .modal-body { padding: 1.5rem; }
        label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
        .input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 5px; transition: border-color 0.2s, box-shadow 0.2s; }
        .input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); }
        .input-group { display: flex; }
        .input-group .prefix { padding: 0.75rem; background-color: var(--light-gray); border: 1px solid var(--border-color); border-right: none; border-radius: 5px 0 0 5px; }
        .input-group .input { border-radius: 0 5px 5px 0; }
        form > *:not(:last-child) { margin-bottom: 1rem; }
        .actions { margin-top: 1.5rem; text-align: right; }
        .stock-control { text-align: center; }
        .current-stock { font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem; }
        .new-stock-input { width: 80px; text-align: center; margin-right: 5px; }
        .drecris-stock-input { width: 80px; text-align: center; margin-right: 5px; }
        @media (max-width: 992px) {
            .table thead { display: none; }
            .table, .table tbody, .table tr, .table td { display: block; width: 100%; }
            .table tr { margin-bottom: 1.5rem; border: 1px solid var(--border-color); border-radius: 8px; box-shadow: var(--shadow); }
            .table td { text-align: right; padding-left: 50%; position: relative; border: none; border-bottom: 1px solid var(--border-color); }
            .table tr td:last-child { border-bottom: none; }
            .table td::before { content: attr(data-label); position: absolute; left: 1rem; width: calc(50% - 2rem); text-align: left; font-weight: bold; white-space: nowrap; }
        }

         #stockLogModal .modal-dialog {
        max-width: 720px; /* মোবাইল/ট্যাবের ডিফল্ট প্রস্থ */
    }
    @media (min-width: 992px) {
        #stockLogModal .modal-dialog {
            max-width: 1100px; /* ল্যাপটপ/ডেস্কটপে অনেক বেশি জায়গা */
        }
    }
    @media (min-width: 1400px) {
        #stockLogModal .modal-dialog {
            max-width: 1280px; /* বড় মনিটরে আরও প্রশস্ত */
        }
    }
 
    #stockLogModal .modal-header {
        padding: 10px 16px;
    }
    #stockLogModal .modal-title {
        font-size: 15px;
    }
 
    #stockLogModal .modal-body {
        padding: 10px 12px;
    }
 
    #stockLogModal .modal-footer {
        padding: 8px 12px;
    }
 
    /* টেবিল আরও ঘনসন্নিবিষ্ট — কম প্যাডিং, ছোট ফন্ট, তবু পড়তে সহজ */
    #stockLogTable {
        font-size: 12.5px;
    }
    #stockLogTable thead th {
        padding: 6px 8px;
        font-size: 11.5px;
        white-space: nowrap;
    }
    #stockLogTable tbody td {
        padding: 5px 8px;
        vertical-align: middle;
    }
    #stockLogTable .badge {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
    }
 
    /* বড় স্ক্রিনে টেবিলের স্ক্রল-এরিয়া আরও লম্বা রাখা যায়, যেহেতু মডালটাই বড় */
    #stockLogTableWrap {
        max-height: 60vh;
        overflow-y: auto;
    }
    @media (min-width: 992px) {
        #stockLogTableWrap {
            max-height: 65vh;
        }
    }
    </style>
@stop
{{-- //াইাইাআিই --}}
@section('js')
    {{-- SweetAlert CDN যুক্ত করা হয়েছে --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function(){
        const insertModal = document.getElementById("productModal");
        const updateModal = document.getElementById("updateModal");
        
        const openModalBtn = document.getElementById("openModal");
        const closeModalBtns = document.querySelectorAll(".close");

        if(openModalBtn) openModalBtn.onclick = () => $(insertModal).fadeIn(300).css("display", "flex");
        closeModalBtns.forEach(btn => btn.onclick = () => {
            $(insertModal).fadeOut(300);
            $(updateModal).fadeOut(300);
        });

        window.onclick = (e) => {
            if (e.target == insertModal) $(insertModal).fadeOut(300);
            if (e.target == updateModal) $(updateModal).fadeOut(300);
        };
        
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function loadProducts(){
            $.ajax({
                url: "/products4", type: "GET",
                success: function(res){
                    let rows = "";
                    $.each(res, function(i, p){
                        let rowClass = p.status == 0 ? "inactive-row" : "";
                        let toggleBtn = p.status == 1 
                            ? `<button class="btn btn-sm btn-success toggleStatus" data-id="${p.id}" data-status="1">Active</button>` 
                            : `<button class="btn btn-sm btn-danger toggleStatus" data-id="${p.id}" data-status="0">Inactive</button>`;
                        
                        rows += `<tr class="${rowClass}" data-id="${p.id}">
                            <td data-label="আইডি">${p.id}</td>
                            <td data-label="নাম">${p.name}</td>
                            <td data-label="স্টক" class="stock-control">
                                <div class="current-stock">${p.quantity}</div>
                                <input type="number" class="form-control form-control-sm d-inline-block new-stock-input border-info" placeholder="++" />
                                <button class="btn btn-sm btn-info updateStockBtn"><i class="fas fa-check"></i></button>
                                <br/>
                                <input type="number" class="form-control form-control-sm d-inline-block drecris-stock-input border-danger" placeholder="--" />
                                <button class="btn btn-sm btn-danger drecrisStockBtn"><i class="fas fa-times"></i></button>
                            </td>
                            <td data-label="ছবি"><img src="/${p.image}" class="product-image"></td>
                            <td data-label="কোম্পানি দাম">৳${p.cp}</td>
                            <td data-label="ডিপু দাম">৳${p.dipo}</td>
                            <td data-label="ডিলার দাম">৳${p.dp}</td>
                            <td data-label="ট্রেড দাম">৳${p.tp}</td> 
                            <td data-label="MRP">৳${p.mrp}</td>
                            <td data-label="স্ট্যাটাস">${toggleBtn}</td>
                            <td data-label="একশন">
                                <button class="btn btn-sm btn-warning editBtn"><i class="fas fa-edit"></i> আপডেট</button>
                                <button class="btn btn-sm btn-secondary viewLogBtn mt-1" data-id="${p.id}" data-name="${p.name}" title="স্টক মুভমেন্ট লগ দেখুন">
                                    <i class="fas fa-history"></i> লগ
                                </button>
                            </td>
                        </tr>`;
                    });
                    $("#productTable").html(rows);
                },
                error: function(xhr, status, error){
                    Swal.fire({
                        icon: 'error',
                        title: 'ত্রুটি!',
                        text: 'ডাটা লোড করতে সমস্যা হচ্ছে!',
                    });
                }
            });
        }
    loadProducts();

        $("#productForm").submit(function(e){
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({ 
                url: "/products/store", type: "POST", data: formData, processData: false, contentType: false,
                success: function(res){
                    if(res.status=="success"){ 
                        $(insertModal).fadeOut(300);
                        $("#productForm")[0].reset(); 
                        loadProducts(); 
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: 'প্রোডাক্ট সফলভাবে যুক্ত হয়েছে!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });

        $(document).on("click", ".editBtn", function(){
            let row = $(this).closest("tr");
            $("#update_id").val(row.data("id"));
            $("#update_name").val(row.find("td[data-label='নাম']").text());
            $("#update_cp").val(row.find("td[data-label='কোম্পানি দাম']").text().replace("৳",""));
            $("#update_dipo").val(row.find("td[data-label='ডিপু দাম']").text().replace("৳",""));
            $("#update_dp").val(row.find("td[data-label='ডিলার দাম']").text().replace("৳",""));
            $("#update_tp").val(row.find("td[data-label='ট্রেড দাম']").text().replace("৳",""));
            $("#update_mrp").val(row.find("td[data-label='MRP']").text().replace("৳",""));
            $(updateModal).fadeIn(300).css("display", "flex");
        });

        $("#updateForm").submit(function(e){
            e.preventDefault();
            let id = $("#update_id").val();
            let formData = new FormData(this);
            console.log(formData);
            $.ajax({ 
                url: "/products/update/"+id, type: "POST", data: formData, processData:false, contentType:false,
                success: function(res){
                    if(res.status=="success"){ 
                        $(updateModal).fadeOut(300);
                        $("#updateForm")[0].reset(); 
                        loadProducts();
                        Swal.fire({
                            icon: 'success',
                            title: 'সফল!',
                            text: 'প্রোডাক্ট সফলভাবে আপডেট হয়েছে!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });

        // confirm() এর পরিবর্তে SweetAlert ব্যবহার করা হয়েছে
        $(document).on("click", ".updateStockBtn", function(){
            let row = $(this).closest("tr");
            let id = row.data("id");
            let oldStock = parseInt(row.find(".current-stock").text().trim());
            let stockToAdd = parseInt(row.find(".new-stock-input").val());

            if(isNaN(stockToAdd) || stockToAdd <= 0){
                Swal.fire({
                    icon: 'error',
                    title: 'ভুল ইনপুট',
                    text: 'দয়া করে ০ এর বেশি সঠিক স্টক সংখ্যা লিখুন।'
                });
                return;
            }

            let totalStock = oldStock + stockToAdd;
            let productName = row.find("td[data-label='নাম']").text();

            Swal.fire({
                title: 'আপনি কি নিশ্চিত?',
                html: `আপনি কি <b>${productName}</b> প্রোডাক্টের স্টক <b>${stockToAdd}</b> পরিমাণ যোগ করতে চান?<br><br>বর্তমান স্টক: ${oldStock}<br>নতুন মোট স্টক হবে: <b>${totalStock}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'হ্যাঁ, আপডেট করুন!',
                cancelButtonText: 'না, বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/products/update-stock/" + id,
                        type: "POST",
                        data: { quantity: totalStock, _token: "{{ csrf_token() }}" },
                        success: function(res){
                            Swal.fire(
                                'আপডেট হয়েছে!',
                                'স্টক সফলভাবে আপডেট হয়েছে।',
                                'success'
                            );
                            loadProducts();
                        },
                        error: function(err){
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: 'স্টক আপডেট করতে সমস্যা হয়েছে!',
                            });
                        }
                    });
                }
            });
        });

        $(document).on("click", ".drecrisStockBtn", function(){
            let row = $(this).closest("tr");
            let id = row.data("id");
            let oldStock = parseInt(row.find(".current-stock").text().trim());
            let stockToAdd = parseInt(row.find(".drecris-stock-input").val());

            if(isNaN(stockToAdd) || stockToAdd <= 0){
                Swal.fire({
                    icon: 'error',
                    title: 'ভুল ইনপুট',
                    text: 'দয়া করে ০ এর বেশি সঠিক স্টক সংখ্যা লিখুন।'
                });
                return;
            }

            let totalStock = oldStock - stockToAdd;
            let productName = row.find("td[data-label='নাম']").text();

            Swal.fire({
                title: 'আপনি কি নিশ্চিত?',
                html: `আপনি কি <b>${productName}</b> প্রোডাক্টের স্টক <b>${stockToAdd}</b> পরিমাণ বিয়োগ করতে চান?<br><br>বর্তমান স্টক: ${oldStock}<br>নতুন মোট স্টক হবে: <b>${totalStock}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'হ্যাঁ, আপডেট করুন!',
                cancelButtonText: 'না, বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/products/update-stock/" + id,
                        type: "POST",
                        data: { quantity: totalStock, _token: "{{ csrf_token() }}" },
                        success: function(res){
                            Swal.fire(
                                'আপডেট হয়েছে!',
                                'স্টক সফলভাবে আপডেট হয়েছে।',
                                'success'
                            );
                            loadProducts();
                        },
                        error: function(err){
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: 'স্টক আপডেট করতে সমস্যা হয়েছে!',
                            });
                        }
                    });
                }
            });
        });

        $(document).on("click", ".toggleStatus", function(){
            let id = $(this).data("id");
            let currentStatus = $(this).data("status");
            let newStatus = currentStatus == 1 ? 0 : 1;
            let statusText = newStatus == 1 ? 'Active' : 'Inactive';

            Swal.fire({
                title: 'আপনি কি স্ট্যাটাস পরিবর্তন করতে চান?',
                text: `প্রোডাক্টটি "${statusText}" হিসাবে সেট করা হবে।`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'হ্যাঁ, পরিবর্তন করুন!',
                cancelButtonText: 'না'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/products/status/" + id,
                        type: "POST",
                        data: { status: newStatus, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function(res){
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: 'স্ট্যাটাস সফলভাবে পরিবর্তন হয়েছে!',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadProducts();
                        },
                        error: function(err){
                           Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: 'স্ট্যাটাস পরিবর্তন করতে সমস্যা হয়েছে!',
                            });
                        }
                    });
                }
            });
        });

        $(document).on("click", ".viewLogBtn", function () {
            let id   = $(this).data("id");
            let name = $(this).data("name");
    
            $("#logProductName").text(name);
            $("#stockLogTableBody").html(
                '<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-1"></i> লোড হচ্ছে...</td></tr>'
            );
            $("#stockLogModal").modal("show");
    
            $.ajax({
                url: "/products/stock-movements/" + id,
                type: "GET",
                success: function (res) {
                    let rows = "";
    
                    if (res.movements && res.movements.length > 0) {
                        $.each(res.movements, function (i, m) {
                            let badge = m.type === "in"
                                ? '<span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i> স্টক ইন</span>'
                                : '<span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i> স্টক আউট</span>';
    
                            rows += `<tr>
                                <td>${i + 1}</td>
                                <td>${m.created_at}</td>
                                <td>${badge}</td>
                                <td class="text-center fw-bold">${m.quantity}</td>
                                <td class="text-center text-muted">${m.old_stock}</td>
                                <td class="text-center fw-bold">${m.new_stock}</td>
                                <td>${m.created_by ?? 'N/A'}</td>
                                <td>${m.note ?? '-'}</td>
                            </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="8" class="text-center text-muted py-3">কোনো স্টক মুভমেন্ট রেকর্ড পাওয়া যায়নি!</td></tr>';
                    }
    
                    $("#stockLogTableBody").html(rows);
                },
                error: function () {
                    $("#stockLogTableBody").html(
                        '<tr><td colspan="8" class="text-center text-danger py-3">লগ লোড করতে সমস্যা হয়েছে!</td></tr>'
                    );
                }
            });
        });
        
    });
    </script>
@stop