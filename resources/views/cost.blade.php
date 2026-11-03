@extends('adminlte::page')

@section('title', 'খরচ ম্যানেজমেন্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">খরচ ম্যানেজমেন্ট</h1>
        <button id="addExpenseBtn" class="btn btn-success">
            <i class="fas fa-plus-circle mr-1"></i> নতুন খরচ যুক্ত করুন
        </button>
    </div>
@stop

@section('content')
<div class="container-fluid">
    {{-- সারসংক্ষেপ কার্ড --}}
    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($todayExpense ?? 0, 2) }}<sup style="font-size: 20px">৳</sup></h3>
                    <p>আজকের মোট খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($weekExpense ?? 0, 2) }}<sup style="font-size: 20px">৳</sup></h3>
                    <p>এই সপ্তাহের মোট খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-week"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($monthExpense ?? 0, 2) }}<sup style="font-size: 20px">৳</sup></h3>
                    <p>এই মাসের মোট খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- খরচের তালিকা কার্ড --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">সকল খরচের তালিকা</h3>
            <div class="card-tools">
                <form id="filterForm" class="form-inline">
                    <input type="date" id="from_date" name="from" class="form-control form-control-sm mr-2">
                    <input type="date" id="to_date" name="to" class="form-control form-control-sm mr-2">
                    <button type="submit" class="btn btn-sm btn-primary mr-2"><i class="fas fa-filter"></i> ফিল্টার</button>
                    <button type="button" id="resetFilter" class="btn btn-sm btn-secondary"><i class="fas fa-sync-alt"></i> রিসেট</button>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>খরচের নাম</th>
                        <th>পরিমাণ</th>
                        <th>নোট</th>
                        <th>তারিখ</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody id="expenseTableBody">
                    {{-- AJAX দিয়ে ডেটা এখানে লোড হবে --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Insert/Update Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">নতুন খরচ ফর্ম</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <input type="hidden" id="expense_id" name="id">
                    <div class="form-group">
                        <label for="name">খরচের নাম <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">পরিমাণ <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="form-group" id="created_at_group">
                        <label for="created_at">তারিখ ও সময় <span class="text-danger">*</span></label>
                        <input type="datetime-local"  class="form-control" id="created_at" name="created_at" required>
                    </div>
                    <div class="form-group">
                        <label for="note">নোট (ঐচ্ছিক)</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .card-tools .form-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    
$(document).ready(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // সব খরচ লোড করার ফাংশন
    function loadExpenses(from = '', to = ''){
        $.ajax({ 
            url: "/cost",
            type: "GET",
            data: { from: from, to: to },
            success: function(res){
                let rows = "";
                if(res.length > 0) {
                    $.each(res, function(i, e){
                        let date = new Date(e.created_at);
                        let formattedDate = `${date.getDate().toString().padStart(2, '0')}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getFullYear()}`;
                        rows += `<tr data-id="${e.id}" data-name="${e.name}" data-amount="${e.amount}" data-note="${e.note || ''}">
                        <td>${e.name}</td>
                        <td>৳${parseFloat(e.amount).toFixed(2)}</td>
                        <td>${e.note || "N/A"}</td>
                        <td>${formattedDate}</td>
                        ${
                            e.type !== 1 ?
                            `<td class="text-center">
                                <button class="btn btn-sm btn-info editBtn" title="এডিট"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger deleteBtn" title="ডিলেট"><i class="fas fa-trash-alt"></i></button>
                            </td>`
                            :
                            '<td></td>' // শর্ত পূরণ না হলে একটি খালি সেল দেখানো হবে
                        }
                    </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="5" class="text-center">এই তারিখ পরিসরে কোনো খরচ পাওয়া যায়নি।</td></tr>`;
                }
                $("#expenseTableBody").html(rows);
            },
            error: function(){
                Swal.fire('ত্রুটি!', 'ডেটা লোড করতে সমস্যা হয়েছে।', 'error');
            }
        });
    }

    loadExpenses();

    // ফিল্টার ফর্ম সাবমিট
    $('#filterForm').on('submit', function(e){
        e.preventDefault();
        let from = $('#from_date').val();
        let to = $('#to_date').val();
        loadExpenses(from, to);
    });

    // ফিল্টার রিসেট
    $('#resetFilter').on('click', function(){
        $('#filterForm')[0].reset();
        loadExpenses();
    });

    // নতুন খরচ যুক্ত করার মডাল খোলা
    $("#addExpenseBtn").click(function(){
        $("#created_at_group").show();
        $('#expenseForm')[0].reset();
        $('#expense_id').val('');
        $('#modalTitle').text('নতুন খরচ ফর্ম');
        $('#submitBtn').text('সেভ করুন');
        $("#expenseModal").modal("show");
    });

    // এডিট বাটন ক্লিক
    $(document).on("click", ".editBtn", function(){
        let row = $(this).closest("tr");
        $("#expense_id").val(row.data("id"));
        $("#name").val(row.data("name"));
        $("#amount").val(row.data("amount"));       
        $("#note").val(row.data("note"));
        $("#created_at_group").hide();
        
        $('#modalTitle').text('খরচ আপডেট ফর্ম');
        $('#submitBtn').text('আপডেট করুন');
        $("#expenseModal").modal("show");
    });

    // ফর্ম সাবমিট (নতুন এবং আপডেট)
    $("#expenseForm").submit(function(e){
        e.preventDefault();
        let id = $("#expense_id").val();
        let url = id ? `/cost/${id}` : "/cost";
        let method = id ? "PUT" : "POST";
        let data = $(this).serialize();

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(res){
                $("#expenseModal").modal("hide");
                console.log(res.expense);
                Swal.fire('সফল!', `খরচ সফলভাবে ${id ? 'আপডেট' : 'সংরক্ষণ'} হয়েছে।`, 'success')
                    .then(() => location.reload()); // পেইজ রিফ্রেশ করে সারসংক্ষেপ আপডেট
            }, 
            error: function(xhr){
                Swal.fire('ব্যর্থ!', 'একটি সমস্যা হয়েছে: ' + xhr.responseText, 'error');
            }
        });
    });

    // ডিলেট বাটন ক্লিক
    $(document).on("click", ".deleteBtn", function(){
        let id = $(this).closest("tr").data("id");
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: "আপনি এই খরচটি মুছে ফেলতে চান!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, ডিলেট করুন!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/cost/${id}`,
                    type: "DELETE",
                    success: function(res){
                        Swal.fire('ডিলেট!', 'খরচ সফলভাবে ডিলেট করা হয়েছে।', 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr){
                        Swal.fire('ত্রুটি!', 'ডিলেট করতে সমস্যা হয়েছে!', 'error');
                    }
                });
            }
        }); 
    });
     
});
 
</script>
@stop