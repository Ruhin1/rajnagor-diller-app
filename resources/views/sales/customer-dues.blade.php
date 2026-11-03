@extends('adminlte::page')

@section('title', 'বকেয়া পরিশোধ')

@section('content_header')
    <h1>বকেয়া পরিশোধ করুন </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- বাম দিকের কলাম: কাস্টমার তথ্য ও পেমেন্ট ফর্ম --}}
        <div class="col-lg-4">
            {{-- কাস্টমার প্রোফাইল কার্ড --}}
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h3 class="profile-username text-center mt-3">{{ $customer->name }}</h3>
                    <p class="text-muted text-center">{{ $customer->shop_name ?? 'দোকানের নাম নেই' }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>মোবাইল</b> <a class="float-right">{{ $customer->number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>ঠিকানা</b> <a class="float-right">{{ $customer->shop_address ?? 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>বকেয়া ইনভয়েস</b> <a class="float-right">{{ $sales->count() }} টি</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- পেমেন্ট ফর্ম কার্ড --}}
            <div class="card shadow-sm">
                <div class="card-header bg-success">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>বকেয়া টাকা গ্রহণ</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">কাস্টমারের কাছ থেকে টাকা হাতে পেয়ে "পরিশোধ করুন" বাটনে ক্লিক করুন।</span>
                        </div>
                    </div>

                    <form id="payForm" class="mt-3">
                        @csrf
                        <div class="form-group">
                            <label for="payAmount">টাকার পরিমাণ (৳)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" step="0.01" min="0.01" name="amount" id="payAmount" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="method">লেনদেনের মাধ্যম</label>
                            <select class="form-control" name="method" id="method"> 
                                <option selected value="Cash">ক্যাশ</option>
                                <option value="Bkash">বিকাশ</option>
                                <option value="Nagod">নগদ</option>
                                <option value="bank">ব্যাংক</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reference">রেফারেন্স (ঐচ্ছিক)</label>
                            <input type="text" name="reference" id="reference" class="form-control" placeholder="ট্রানজেকশন আইডি বা অন্য কোনো তথ্য">
                        </div>
                        <button class="btn btn-success btn-block" id="applyPay"><i class="fas fa-check-circle mr-2"></i> পরিশোধ করুন</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ডান দিকের কলাম: বকেয়া ইনভয়েস তালিকা --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">বকেয়া ইনভয়েসসমূহের তালিকা</h3>
                    <div class="card-tools">
                        <span class="badge badge-danger" style="font-size: 1rem;">মোট বকেয়া: {{ number_format($totalDue, 2) }} ৳</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ইনভয়েস #</th>
                                <th>তারিখ</th>
                                <th>মোট বিল</th>
                                <th>জমা</th>
                                <th>বকেয়া</th>
                                <th class="text-center">প্রিন্ট</th>
                            </tr>
                        </thead>
                        <tbody id="duesTable">
                            @foreach($sales as $s)
                            <tr data-id="{{ $s->id }}">
                                <td>#{{ $s->id }}</td>
                                <td>{{ $s->created_at->format('d-m-Y') }}</td>
                                <td>৳{{ number_format($s->total, 2) }}</td>
                                <td>৳{{ number_format($s->paid, 2) }}</td>
                                <td class="due-cell font-weight-bold">৳{{ number_format($s->due, 2) }}</td>
                                <td class="text-center">
                                    {{-- <a href="{{ route('sales.show', $s->id) }}" target="_blank" class="btn btn-sm btn-secondary" title="ইনভয়েস প্রিন্ট করুন">
                                        <i class="fas fa-print"></i>
                                    </a>  --}}
                                    {{-- update --}}
                                    {{-- <a href="javascript:void(0)" 
                                    onclick="openPrintModal('{{ route('sales.show', $s->id) }}', '{{ route('sales.pos.print', $s->id) }}')" 
                                    class="btn btn-sm btn-secondary" 
                                    title="প্রিন্ট অপশন">
                                    <i class="fas fa-print"></i>
                                    </a> --}}
                                    <a href="javascript:void(0)" 
   onclick="openPrintModal(
       '{{ route('sales.show', $s->id) }}', 
       '{{ route('sales.pos.print', $s->id) }}',
       '{{ route('sales.bluetooth.print', $s->id) }}'
   )" 
   class="btn btn-sm btn-secondary" 
   title="প্রিন্ট অপশন">
   <i class="fas fa-print"></i>
</a>
                                    {{-- end update --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-center m-3">

    <a class="btn btn-success " href="javascript:void(0)" 
   onclick="openPrintModal(
       '{{ route('customers.statement', $customer->id) }}', 
       '{{ route('customers.statement.pos.print', $customer->id) }}',
       '{{ route('customers.statement.bluetooth', $customer->id) }}' 
   )" 
   class="btn btn-sm btn-secondary" 
   title="প্রিন্ট অপশন">
   কাস্টমারে লেনদেনের স্টেটমান্ট দেখুন
</a>
</div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    $('#payForm').on('submit', function(e){
        e.preventDefault();
        
        const amount = $('#payAmount').val();
        if (!amount || amount <= 0) {
            Swal.fire('ত্রুটি!', 'দয়া করে সঠিক পরিমাণ লিখুন।', 'error');
            return;
        }

        $('#applyPay').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেসিং...');

        const payload = {
            amount: Number(amount),
            method: $('#method').val(),
            reference: $('input[name="reference"]').val()
        };

        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: `আপনি কি ({{ $customer->name }}) এর কাছ থেকে ${payload.amount} টাকা বুঝে পেয়েছেন?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'হ্যাঁ, পেয়েছি!',
            cancelButtonText: 'না'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('customers.pay-dues', $customer->id) }}",
                    method: "POST",
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(res){
                        if(res.status === 'success'){
                            // console.log(res.data);
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: `৳${res.applied} টাকা সফলভাবে পরিশোধ হয়েছে। বর্তমান বকেয়া: ৳${res.remaining_due}`,
                            }).then(() => {
                                // যদি বকেয়া পরিশোধের পর কোনো ইনভয়েস প্রিন্টের প্রয়োজন হয়
                            
                                //  window.open(res.redirect, '_blank');
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
                                            window.location.href = res.normal_url;
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

                                
                                // location.reload(); 
                            });
                        } else {
                            Swal.fire('ব্যর্থ!', res.message || 'একটি সমস্যা হয়েছে।', 'error');
                        }
                    },
                    error: function(xhr){
                        let txt = 'একটি অপ্রত্যাশিত সমস্যা হয়েছে।';
                        try { txt = JSON.parse(xhr.responseText).message || xhr.responseText; } catch(e){}
                        Swal.fire('ত্রুটি!', txt, 'error');
                    },
                    complete: function(){
                        $('#applyPay').prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> পরিশোধ করুন');
                    }
                });
            } else {
                $('#applyPay').prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> পরিশোধ করুন');
            }
        });
    });
});

 //  =====================
        // একটি ফাংশন দিয়ে সব কনফার্মেশন হ্যান্ডেল করা
    /**
 * প্রিন্ট মডাল খোলা (3টি অপশন সহ)
 */
function openPrintModal(normalUrl, posUrl, bluetoothUrl) {
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
            window.location.href = normalUrl;
        } 
        // ২. POS Print (Rongta Network Printer)
        else if (result.isDenied) {
            printToRongta(posUrl);
        }
        // ৩. Bluetooth Print (Dotmax)
        else if (result.dismiss === Swal.DismissReason.cancel) {
            printToBluetooth(bluetoothUrl);
        }
    });
}

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
    // ======================== 

</script>
@stop