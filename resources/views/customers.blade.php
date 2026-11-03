@extends('adminlte::page')

@section('title', 'কাস্টমার তালিকা')

@section('content_header')
    {{-- নতুন এবং উন্নত হেডার --}}
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">কাস্টমার তালিকা</h1>
        <button id="openModal" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> নতুন কাস্টমার যুক্ত করুন
        </button>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">সকল কাস্টমারের তালিকা</h3>
        <div class="card-tools">
            {{-- লাইভ সার্চ বক্স --}}
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" id="searchInput" class="form-control float-right" placeholder="সার্চ করুন...">
                <div class="input-group-append">
                    <button type="button" class="btn btn-default"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>আইডি</th> 
                    <th>কাস্টমারের নাম</th> 
                    <th>দোকানের নাম</th>
                    <th>মোবাইল নাম্বার</th>
                    <th>দোকানের ঠিকানা</th>
                    <th class="text-center">একশন</th>
                </tr>
            </thead>
            <tbody id="customerTableBody">
                {{-- AJAX দিয়ে ডেটা এখানে লোড হবে --}}
            </tbody>
        </table>
    </div>
</div>

<!-- Insert/Update Modal -->
<div id="customerModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">নতুন কাস্টমার ফর্ম</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <input type="hidden" id="update_id" name="id">
                    <div class="form-group">
                        <label for="name">কাস্টমারের নাম <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="number">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="number" name="number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                                <label for="shop_name">দোকানের নাম</label>
                                <input type="text" class="form-control" id="shop_name" name="shop_name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="shop_address">দোকানের ঠিকানা</label>
                        <textarea class="form-control" id="shop_address" name="shop_address" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">বাতিল করুন</button>
                        <button type="submit" id="submitBtn" class="btn btn-success">সেভ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
{{-- Font Awesome আইকন এর জন্য (AdminLTE তে সাধারণত থাকে) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* টেবিলের একশন বাটনগুলোকে সুন্দর করার জন্য */
    .btn-group-action .btn {
        margin: 0 5px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // সব কাস্টমার লোড করার ফাংশন
    function loadCustomers(){
      $.ajax({
          url: "/customers",
          type: "GET",
          dataType: "json",
          success: function(res){
              let rows = "";
              if(res.length > 0) {
                  $.each(res, function(i, p){
                      rows += `<tr data-id="${p.id}" data-name="${p.name}" data-number="${p.number}" data-shop-name="${p.shop_name || ''}" data-shop-address="${p.shop_address || ''}">
                        <td>${p.id}</td>
                        <td>${p.name}</td>
                        <td>${p.shop_name || 'N/A'}</td>
                        <td>${p.number}</td>
                        <td>${p.shop_address || 'N/A'}</td>
                        <td class="text-center btn-group-action">
                            <button class="btn btn-sm btn-info editBtn" title="আপডেট করুন"><i class="fas fa-edit"></i></button>
                            <button disabled class="btn btn-sm btn-danger deleteBtn" title="ডিলিট করুন"><i class="fas fa-trash-alt"></i></button>
                        </td>
                        </tr>`;
                  });
              } else {
                  rows = `<tr><td colspan="5" class="text-center">কোনো কাস্টমার পাওয়া যায়নি।</td></tr>`;
              }
              $("#customerTableBody").html(rows);
          },
          error: function(xhr, status, error){
              Swal.fire('ত্রুটি!', 'ডেটা লোড করতে সমস্যা হয়েছে।', 'error');
          }
      });
    }

    // পেইজ লোড হলে কাস্টমার লোড করা
    loadCustomers();

    // নতুন কাস্টমার যুক্ত করার মডাল খোলা
    $('#openModal').on('click', function() {
        $('#customerForm')[0].reset();
        $('#update_id').val('');
        $('#modalTitle').text('নতুন কাস্টমার ফর্ম');
        $('#submitBtn').text('সেভ করুন');
        $('#customerModal').modal('show');
    });

    // কাস্টমার এডিট করার মডাল খোলা
    $(document).on("click", ".editBtn", function(){
        let row = $(this).closest("tr");
        
        $('#update_id').val(row.data("id"));
        $('#name').val(row.data("name"));
        $('#number').val(row.data("number"));
        $('#shop_name').val(row.data("shop-name"));
        $('#shop_address').val(row.data("shop-address"));

        $('#modalTitle').text('কাস্টমার তথ্য আপডেট');
        $('#submitBtn').text('আপডেট করুন');
        $('#customerModal').modal('show');
    });

    // ফর্ম সাবমিট (নতুন এবং আপডেট)
    $("#customerForm").submit(function(e){
        e.preventDefault();
        let id = $("#update_id").val();
        let url = id ? `/customers/update/${id}` : "/customers/store";
        let formData = new FormData(this);

        // আপডেটের জন্য কিছু ফিল্ডের নাম পরিবর্তন করা হচ্ছে
        if (id) {
            formData.append('up_name', formData.get('name'));
            formData.append('up_number', formData.get('number'));
            formData.append('up_shop_name', formData.get('shop_name'));
            formData.append('up_shop_address', formData.get('shop_address'));
            formData.delete('name');
            formData.delete('number');
            formData.delete('shop_name');
            formData.delete('shop_address');
        }

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if(res.status === "success"){
                    $('#customerModal').modal('hide');
                    loadCustomers();
                    Swal.fire('সফল!', `কাস্টমার তথ্য সফলভাবে ${id ? 'আপডেট' : 'যুক্ত'} হয়েছে।`, 'success');
                } else {
                    Swal.fire('ব্যর্থ!', res.message || 'একটি সমস্যা হয়েছে।', 'error');
                }
            },
            error: function() {
                Swal.fire('ত্রুটি!', `একটি অপ্রত্যাশিত সমস্যা হয়েছে।`, 'error');
            }
        });
    });
    
    // কাস্টমার ডিলিট করা
    $(document).on("click", ".deleteBtn", function () {
        let row = $(this).closest("tr"); 
        let id = row.data("id");
        
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: "আপনি এই কাস্টমারকে ডিলিট করতে চান!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/customersdelate/${id}`,
                    type: "DELETE",
                    success: function (res) {
                        if (res.status === "success") {
                            loadCustomers();
                            Swal.fire('ডিলিট!', 'কাস্টমারকে সফলভাবে ডিলিট করা হয়েছে।', 'success');
                        }
                    },
                    error: function () {
                        Swal.fire('ত্রুটি!', 'ডিলেট করতে সমস্যা হয়েছে!', 'error');
                    }
                });
            }
        });
    });

    // লাইভ সার্চ ফাংশন
    $("#searchInput").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("#customerTableBody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@stop