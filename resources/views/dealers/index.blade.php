@extends('adminlte::page')

@section('title', 'ডিলার ম্যানেজমেন্ট')

@section('content_header')
    {{-- নতুন এবং উন্নত হেডার --}}
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">ডিলার ম্যানেজমেন্ট</h1>
        <button id="addDealerBtn" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> নতুন ডিলার যোগ করুন
        </button>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">সকল ডিলারের তালিকা</h3>
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
                    <th>নাম</th>
                    <th>ফোন</th>
                    <th>প্রতিষ্ঠানের নাম</th>
                    <th>ইমেইল</th>
                    <th>ঠিকানা</th>
                    <th class="text-center">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody id="dealerTableBody">
                {{-- AJAX দিয়ে ডেটা এখানে লোড হবে --}}
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Update Dealer Modal -->
<div class="modal fade" id="dealerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">নতুন ডিলার ফর্ম</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="dealerForm">
                    @csrf
                    <input type="hidden" id="dealer_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">ডিলারের নাম <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" placeholder="ডিলারের নাম" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">ফোন নাম্বার <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" placeholder="ফোন নাম্বার" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pname">প্রতিষ্ঠানের নাম</label>
                        <input type="text" class="form-control" id="pname" placeholder="প্রতিষ্ঠানের নাম">
                    </div>
                    <div class="form-group">
                        <label for="email">ইমেইল (ঐচ্ছিক)</label>
                        <input type="email" class="form-control" id="email" placeholder="ইমেইল">
                    </div>
                    <div class="form-group">
                        <label for="address">ঠিকানা (ঐচ্ছিক)</label>
                        <textarea class="form-control" id="address" rows="3" placeholder="ঠিকানা"></textarea>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Font Awesome আইকন এর জন্য (AdminLTE তে সাধারণত থাকে) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .btn-group-action .btn {
        margin: 0 5px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    // সব ডিলার লোড করার ফাংশন
    function loadDealers(){
        $.get("/dealers/list", function(res){
            let rows = "";
            if(res.length > 0) {
                res.forEach(d=>{
                    rows += `<tr data-id="${d.id}" data-name="${d.name}" data-phone="${d.phone}" data-pname="${d.pname || ''}" data-email="${d.email || ''}" data-address="${d.address || ''}">
                        <td>${d.id}</td>
                        <td>${d.name}</td>
                        <td>${d.phone}</td>
                        <td>${d.pname || 'N/A'}</td>
                        <td>${d.email || 'N/A'}</td>
                        <td>${d.address || 'N/A'}</td>
                        <td class="text-center btn-group-action">
                            <button class="btn btn-sm btn-info editBtn" title="এডিট করুন"><i class="fas fa-edit"></i></button>
                            <button disabled class="btn btn-sm btn-danger delBtn" title="ডিলিট করুন"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                rows = `<tr><td colspan="6" class="text-center">কোনো ডিলার পাওয়া যায়নি।</td></tr>`;
            }
            $("#dealerTableBody").html(rows);
        });
    }
    
    // পেইজ লোড হলে ডিলার লোড করা
    loadDealers();

    // নতুন ডিলার যোগ করার মডাল খোলা
    $("#addDealerBtn").on("click", function(){
        $("#dealerForm")[0].reset();
        $("#dealer_id").val('');
        $("#modalTitle").text("নতুন ডিলার ফর্ম");
        $("#submitBtn").text("সেভ করুন");
        $("#dealerModal").modal('show');
    });

    // ডিলার এডিট করার মডাল খোলা
    $(document).on("click", ".editBtn", function(){
        let row = $(this).closest('tr');
        $("#dealer_id").val(row.data("id"));
        $("#name").val(row.data("name"));
        $("#phone").val(row.data("phone"));
        $("#pname").val(row.data("pname"));
        $("#email").val(row.data("email"));
        $("#address").val(row.data("address"));
        
        $("#modalTitle").text("ডিলার তথ্য আপডেট");
        $("#submitBtn").text("আপডেট করুন");
        $("#dealerModal").modal('show');
    });

    // ফর্ম সাবমিট (নতুন এবং আপডেট)
    $("#dealerForm").submit(function(e){
        e.preventDefault();
        let id = $("#dealer_id").val();
        let url = id ? `/dealers/${id}/update` : "/dealers/store";

        $.post(url, {
            _token: "{{ csrf_token() }}",
            name: $("#name").val(),
            phone: $("#phone").val(),
            email: $("#email").val(),
            address: $("#address").val(),
            pname: $("#pname").val()
        }, function(res){
            $("#dealerModal").modal('hide');
            loadDealers();
            Swal.fire({
                icon: 'success',
                title: 'সফল!',
                text: `ডিলার তথ্য সফলভাবে ${id ? 'আপডেট' : 'সেভ'} হয়েছে।`,
                timer: 2000,
                showConfirmButton: false
            });
        }).fail(xhr=>{
            Swal.fire('ত্রুটি!', 'একটি সমস্যা হয়েছে: ' + xhr.responseText, 'error');
        });
    });

    // ডিলার ডিলিট করা
    $(document).on("click", ".delBtn", function(){
        let row = $(this).closest('tr');
        let id = row.data("id");
        
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: `আপনি "${row.data("name")}" নামের ডিলারকে ডিলিট করতে চান!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/dealers/${id}`,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(){
                        loadDealers();
                        Swal.fire('ডিলিট!', 'ডিলারকে সফলভাবে ডিলিট করা হয়েছে।', 'success');
                    },
                    error: function() {
                        Swal.fire('ত্রুটি!', 'ডিলেট করতে সমস্যা হয়েছে!', 'error');
                    }
                });
            }
        });
    });

    // লাইভ সার্চ ফাংশন
    $("#searchInput").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("#dealerTableBody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@stop