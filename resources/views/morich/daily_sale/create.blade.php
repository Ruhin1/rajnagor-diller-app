@extends('adminlte::page')
@section('title', 'দৈনিক বিক্রয় এন্ট্রি')
@section('plugins.Select2', true)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <form action="{{ route('morich.daily-sale.store') }}" method="POST">
                @csrf
                <div class="card card-success mt-3 shadow">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-cart-plus mr-2"></i> দৈনিক বিক্রয়ের হিসাব যুক্ত করুন</h3></div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 form-group"><label for="sale_date">বিক্রয়ের তারিখ</label><input type="date" id="sale_date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->toDateString()) }}" required></div>
                            <div class="col-md-6 form-group"><label for="notes">নোট (ঐচ্ছিক)</label><input type="text" id="notes" name="notes" class="form-control" placeholder="যেমন: আজকের বাজার ভালো ছিল" value="{{ old('notes') }}"></div>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3 font-weight-bold">বিক্রিত পণ্যের তালিকা</h5>

                        {{-- *** নতুন: রেসপন্সিভ টেবিল স্ট্রাকচার *** --}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="product-col">পণ্য</th>
                                        <th class="quantity-col">কেজি</th>
                                        <th class="quantity-col">গ্রাম</th>
                                        <th class="action-col"></th>
                                    </tr>
                                </thead>
                                <tbody id="sale-items-container">
                                    {{-- জাভাস্ক্রিপ্ট দিয়ে এখানে রো যুক্ত হবে --}}
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="add-item-btn" class="btn btn-primary mt-3">
                            <i class="fas fa-plus mr-1"></i> নতুন পণ্য যোগ করুন
                        </button>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-lg btn-success">
                            <i class="fas fa-save mr-2"></i> হিসাব সংরক্ষণ করুন
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* মোবাইল ডিভাইসের জন্য কাস্টম স্টাইল */
    @media (max-width: 767px) {
        /* টেবিলের কলামগুলোর প্রস্থ মোবাইলের জন্য ঠিক করা */
        .product-col {
            min-width: 200px; /* পণ্যের নাম দেখার জন্য যথেষ্ট জায়গা */
        }
        .quantity-col {
            min-width: 100px; /* কেজি/গ্রাম ইনপুটের জন্য */
        }
        .action-col {
            min-width: 60px;  /* বাটন রাখার জন্য */
        }
        .card-footer.text-right {
            text-align: center !important; /* মোবাইলে বাটন সেন্টারে থাকবে */
        }
        .card-footer .btn {
            width: 100%; /* মোবাইলে বাটন ফুল-উইডথ হবে */
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let rowIndex = 0;
    
    function addSaleItemRow() {
        const products = @json($products);
        let productOptions = '<option value="">পণ্য সিলেক্ট করুন</option>';
        products.forEach(p => {
            const stockInKg = (p.stock_in_grams / 1000).toFixed(3);
            productOptions += `<option value="${p.id}">${p.name} (স্টক: ${stockInKg} কেজি)</option>`;
        });

        // টেবিলের রো-এর গঠন পরিবর্তন করা হয়েছে
        const newRow = `
            <tr id="row-${rowIndex}">
                <td class="product-col">
                    <select name="items[${rowIndex}][product_id]" class="form-control select2" required>${productOptions}</select>
                </td>
                <td class="quantity-col">
                    <input type="number" name="items[${rowIndex}][quantity_kg]" class="form-control" step="1" min="0" placeholder="কেজি" value="0">
                </td>
                <td class="quantity-col">
                    <input type="number" name="items[${rowIndex}][quantity_gram]" class="form-control" step="1" min="0" max="999" placeholder="গ্রাম" value="0">
                </td>
                <td class="text-center action-col">
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn" data-row-id="${rowIndex}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        
        $('#sale-items-container').append(newRow);
        
        // নতুন যুক্ত হওয়া select এলিমেন্টকে Select2 হিসেবে চালু করা
        $(`#row-${rowIndex} .select2`).select2({
            placeholder: "পণ্য সিলেক্ট করুন",
            width: '100%' // রেসপন্সিভ поведения জন্য
        });
        
        rowIndex++;
    }
    
    addSaleItemRow(); // পেজ লোড হওয়ার সাথে সাথে প্রথম রো যুক্ত করা
    $('#add-item-btn').on('click', addSaleItemRow);
    
    // ডাইনামিকভাবে তৈরি হওয়া বাটনের জন্য ইভেন্ট ডেলিগেশন
    $(document).on('click', '.remove-item-btn', function() { 
        const rowId = $(this).data('row-id');
        $(`#row-${rowId}`).remove(); 
    });
});
</script>
@stop