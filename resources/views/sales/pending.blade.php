@extends('adminlte::page')

@section('title', 'পেন্ডিং অর্ডার')

@section('content_header')
    <h1>পেন্ডিং অর্ডারসমূহ</h1>
@stop

@section('content')
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h3 class="card-title mt-1"><i class="fas fa-search mr-2"></i>সার্চ ও ফিল্টার</h3>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('sales.pending') }}">
                    <div class="input-group">
                        <input type="text" name="search_term" class="form-control" placeholder="অর্ডার আইডি / কাস্টমার বা ডিলারের নাম..." value="{{ request('search_term') }}">
                        <div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> খুঁজুন</button></div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-md-right quick-filters">
                <a href="{{ route('sales.pending', ['filter' => 'today']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-day"></i> দৈনিক</a>
                <a href="{{ route('sales.pending', ['filter' => 'this_week']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-week"></i> সাপ্তাহিক</a>
                <a href="{{ route('sales.pending', ['filter' => 'this_month']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-alt"></i> মাসিক</a>
            </div>
        </div>

        <form method="GET" action="{{ route('sales.pending') }}" class="form-row align-items-end">
            <div class="form-group col-lg-3 col-md-6">
                <label for="from_date">শুরুর তারিখ:</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-6">
                <label for="to_date">শেষ তারিখ:</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-6">
                <label for="sale_type">বিক্রয়ের ধরণ:</label>
                <select name="sale_type" class="form-control">
                    <option value="">সব ধরণ</option>
                    <option value="customer" {{ request('sale_type') == 'customer' ? 'selected' : '' }}>কাস্টমার অর্ডার</option>
                    <option value="dealer" {{ request('sale_type') == 'dealer' ? 'selected' : '' }}>ডিলার অর্ডার</option>
                </select>
            </div>

            {{-- শুধুমাত্র এডমিনের জন্য ইউজার ফিল্টার --}}
            @if(auth()->user()->role == 1)
            <div class="form-group col-lg-3 col-md-6">
                <label for="user_id">এসআর অনুযায়ী ফিল্টার:</label>
                <select name="user_id" class="form-control">
                    <option value="">সকল এসআর</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group col-lg-12">
                <div class="btn-group" style="width: 100%;">
                    <button type="submit" class="btn btn-info" style="width: 50%;"><i class="fas fa-filter"></i> ফিল্টার করুন</button>
                    <a href="{{ route('sales.pending') }}" class="btn btn-secondary" style="width: 50%;"><i class="fas fa-sync-alt"></i> রিসেট</a>
                </div>
                <button type="submit" form="downloadForm" class="btn btn-danger btn-block mt-2"><i class="fas fa-file-pdf"></i> PDF ডাউনলোড করুন</button>
            </div>
        </form>

        {{-- PDF ডাউনলোডের জন্য অদৃশ্য ফর্ম (এখন user_id সহ) --}}
        <form id="downloadForm" method="GET" action="{{ route('sales.pending.pdf') }}" target="_blank" class="d-none">
            <input type="hidden" name="search_term" value="{{ request('search_term') }}">
            <input type="hidden" name="filter" value="{{ request('filter') }}">
            <input type="hidden" name="from" value="{{ request('from') }}">
            <input type="hidden" name="to" value="{{ request('to') }}">
            <input type="hidden" name="sale_type" value="{{ request('sale_type') }}">
            @if(auth()->user()->role == 1)
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            @endif
        </form>
    </div>
</div>
    
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#আইডি</th>
                        <th>কাস্টমার/ডিলার</th>
                        <th>মোবাইল</th>
                        <th>ঠিকানা</th>
                        <th>দোকান/প্রতিষ্ঠান</th>
                        <th>কমিশন</th>
                        <th>জমা</th>
                        <th>বাকি</th>
                        <th>মোট</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ</th>
                        <th>বিক্রয়ের ধরণ</th>
                        <th>এসআর</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr> 
                </thead>
                <tbody>
                    @php $user = auth()->user(); @endphp
                    @forelse($sales as $sale)
                    
                        {{-- @if($user->role != 1 && $user->id != $sale->user_id) 
                        @continue 
                        @endif --}}

                        <tr class="{{ $sale->dealer_id ? 'dealer-row' : 'customer-row' }}">
                            <td data-label="#আইডি">{{ $sale->id }}</td>
                            <td data-label="কাস্টমার/ডিলার">{{ $sale->customer->name ?? $sale->dealer->name ?? 'N/A' }}</td>
                            <td data-label="মোবাইল">{{ $sale->customer->number ?? $sale->dealer->phone ?? 'N/A' }}</td>
                            <td data-label="ঠিকানা">{{ $sale->customer->shop_address ?? $sale->dealer->address ?? 'N/A' }}</td>
                            <td data-label="দোকান/প্রতিষ্ঠান">{{ $sale->customer->shop_name ?? $sale->dealer->pname ?? 'N/A' }}</td>
                            <td data-label="কমিশন">{{ $sale->discount }}</td>
                            <td data-label="জমা">{{ $sale->paid }}</td>
                            <td data-label="বাকি">{{ $sale->due }}</td>
                            <td data-label="মোট">৳{{ number_format($sale->total, 2) }}</td>
                            <td data-label="স্ট্যাটাস"><span class="badge badge-warning">পেন্ডিং</span></td>
                            <td data-label="তারিখ">{{ $sale->created_at->format('d-m-Y') }}</td>
                            <td data-label="বিক্রয়ের ধরণ">
                                @if ($sale->customer_id && $sale->is_dipu == 0) 
                                <span class="badge badge-info">কাস্টমার</span>
                                @elseif ($sale->dealer_id && $sale->is_dipu == 0) 
                                <span class="badge badge-success">ডিলার</span>
                                @else
                                <span class="badge badge-secondary">ডিপু</span>
                                 @endif
                            </td>
                            <td data-label="এসআর">{{ $sale->srname }}</td>
                            <td data-label="অ্যাকশন" class="text-center">
                                <div class="btn-group">
                                    <a href="javascript:void(0)"
                                        onclick="openPrintModal(
                                            '{{ route('sales.show', $sale->id) }}', 
                                            '{{ route('sales.pos.print', $sale->id) }}',
                                            '{{ route('sales.bluetooth.print', $sale->id) }}'
                                        )"
                                        class="btn btn-sm btn-secondary" title="প্রিন্ট অপশন">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    
                                    @if (auth()->user()->role == 1)
                                        {{-- নতুন অ্যাড করা বাটন: ডেলিভারি পেজে ফেরত পাঠানোর জন্য --}}
                                        <form action="{{ route('sales.revert_to_new', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি পুনরায় ডেলিভারি পেজে (New Orders) পাঠাতে চান? এর ফলে পূর্বে কমানো স্টক প্রোডাক্টের সাথে পুনরায় যোগ হবে।')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="ডেলিভারি পেজে ফেরত পাঠান">
                                                <i class="fas fa-undo text-dark"></i>
                                            </button>
                                        </form>

                                        <a href="{{ $sale->customer_id ? route('sales.edit', $sale->id) : route('delar.edit', $sale->id) }}" class="btn btn-sm btn-primary" title="এডিট">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if ($sale->customer_id == 99)
                                            <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি ডিলিট করতে চান?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" disabled class="btn btn-sm btn-danger" title="ডিলিট">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি ডিলিট করতে চান?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="ডিলিট">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr><td colspan="14" class="text-center">কোনো পেন্ডিং অর্ডার পাওয়া যায়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $sales->appends(request()->query())->links() }}</div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Font Awesome আইকন এর জন্য (AdminLTE তে সাধারণত থাকে) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* ডিলার এবং কাস্টমার রো এর জন্য হালকা কালার */
    .dealer-row { background-color: #e8f5e9 !important; } /* হালকা সবুজ */
    .customer-row { background-color: #e3f2fd !important; } /* হালকা নীল */

    /* মোবাইল ডিভাইসের জন্য উন্নত স্টাইল */
    @media (max-width: 768px) {
        .form-row { flex-direction: column; }
        .form-row .form-group { width: 100%; }
        .form-inline .input-group { flex-wrap: wrap; }
        .form-inline .input-group .form-control { flex: 1 1 auto; width: 1%; }
        .form-inline .input-group-append { width: 100%; }
        .form-inline .input-group-append .btn { width: 100%; border-radius: 0 0 .25rem .25rem; }
        .quick-filters .btn { margin-bottom: 5px; }
        .table thead { display: none; }
        .table, .table tbody, .table tr, .table td { display: block; width: 100%; }
        .table tr { margin-bottom: 1rem; border: 1px solid #dee2e6; }
        .table td { text-align: right; padding-left: 50%; position: relative; }
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: .75rem;
            width: 45%;
            padding-right: .75rem;
            font-weight: bold;
            text-align: left;
        }
        /* মোবাইল ভিউ এর জন্য লেবেল সেট করা */
        .table td:nth-of-type(1)::before { content: "#আইডি"; }
        .table td:nth-of-type(2)::before { content: "কাস্টমার/ডিলার"; }
        .table td:nth-of-type(3)::before { content: "মোবাইল নাম্বার"; }
        .table td:nth-of-type(4)::before { content: "ঠিকানা"; }
        .table td:nth-of-type(5)::before { content: "দোকান/প্রতিষ্টান"; } 
        .table td:nth-of-type(6)::before { content: "কমিশন"; }
        .table td:nth-of-type(7)::before { content: "জমা"; }
        .table td:nth-of-type(8)::before { content: "বাকি"; }
        .table td:nth-of-type(9)::before { content: "মোট"; }
        .table td:nth-of-type(10)::before { content: "স্ট্যাটাস"; }
        .table td:nth-of-type(11)::before { content: "তারিখ"; }
        .table td:nth-of-type(12)::before { content: "বিক্রয়ের ধরণ"; }
        .table td:nth-of-type(13)::before { content: "এসআর"; }
        .table td:nth-of-type(14)::before { content: "একশন"; }
    }
</style>
@stop
 
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   
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

    function confirmAction(event, message) {
        event.preventDefault(); // ফর্ম সাবমিট বন্ধ করা

        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'হ্যাঁ, নিশ্চিত!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit(); // কনফার্ম করলে ফর্ম সাবমিট হবে
            }
        });
        return false;
    }
</script>
@stop