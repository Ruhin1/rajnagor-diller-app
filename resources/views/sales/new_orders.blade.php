@extends('adminlte::page')

@section('title', 'নতুন অর্ডার')

@section('content_header')
    <h1>নতুন অর্ডারসমূহ (Ordered)</h1>
@stop

@section('content')
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
                <form method="GET" action="{{ route('sales.new_orders') }}">
                    <div class="input-group">
                        <input type="text" name="search_term" class="form-control" placeholder="অর্ডার আইডি / কাস্টমার বা ডিলারের নাম..." value="{{ request('search_term') }}">
                        <div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> খুঁজুন</button></div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-md-right quick-filters">
                <a href="{{ route('sales.new_orders', ['filter' => 'today']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-day"></i> দৈনিক</a>
                <a href="{{ route('sales.new_orders', ['filter' => 'this_week']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-week"></i> সাপ্তাহিক</a>
                <a href="{{ route('sales.new_orders', ['filter' => 'this_month']) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-calendar-alt"></i> মাসিক</a>
            </div>
        </div>

        <form method="GET" action="{{ route('sales.new_orders') }}" class="form-row align-items-end">
            <div class="form-group col-lg-3 col-md-6">
                <label for="from_date">শুরুর তারিখ:</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-6">
                <label for="to_date">শেষ তারিখ:</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="form-group col-lg-3 col-md-6">
                <label for="sale_type">বিক্রয়ের ধরণ:</label>
                <select name="sale_type" class="form-control">
                    <option value="">সব ধরণ</option>
                    <option value="customer" {{ request('sale_type') == 'customer' ? 'selected' : '' }}>কাস্টমার অর্ডার</option>
                    <option value="dealer" {{ request('sale_type') == 'dealer' ? 'selected' : '' }}>ডিলার অর্ডার</option>
                </select>
            </div>

            @if(auth()->user()->role == 1)
            <div class="form-group col-lg-3 col-md-6">
                <label for="user_id">এসআর অনুযায়ী ফিল্টার:</label>
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
                    <a href="{{ route('sales.new_orders') }}" class="btn btn-secondary" style="width: 50%;"><i class="fas fa-sync-alt"></i> রিসেট</a>
                </div>
            </div>
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
                        <th>বিক্রয়ের ধরণ</th>
                        <th>এসআর</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr> 
                </thead>
                <tbody>
                    @php $user = auth()->user(); @endphp
                    @forelse($sales as $sale)
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
                            <td data-label="স্ট্যাটাস"><span class="badge badge-primary">অর্ডারড</span></td>
                            <td data-label="তারিখ">{{ $sale->created_at->format('d-m-Y') }}</td>
                            <td data-label="বিক্রয়ের ধরণ">
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
                                    {{-- ডেলিভারি বাটন --}}
                                    <form action="{{ route('sales.delivery', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি ডেলিভারি করতে চান? ডেলিভারি করলে স্টক থেকে পণ্য মাইনাস হবে।')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="ডেলিভারি"><i class="fas fa-truck"></i></button>
                                    </form>

                                    {{-- প্রিন্ট বাটন --}}
                                    <a href="javascript:void(0)" 
                                        onclick="openPrintModal(
                                            '{{ route('sales.show', $sale->id) }}', 
                                            '{{ route('sales.pos.print', $sale->id) }}',
                                            '{{ route('sales.bluetooth.print', $sale->id) }}'
                                        )" 
                                        class="btn btn-sm btn-secondary" 
                                        title="প্রিন্ট অপশন">
                                        <i class="fas fa-print"></i>
                                    </a> 

                                    <a href="{{ $sale->customer_id ? route('sales.edit', $sale->id) : route('delar.edit', $sale->id) }}" class="btn btn-sm btn-primary" title="এডিট"><i class="fas fa-edit"></i></a>
                                    
                                    @if ($sale->customer_id == 99)
                                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি ডিলিট করতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" disabled class="btn btn-sm btn-danger" title="ডিলিট"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @else
                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(event, 'আপনি কি এই অর্ডারটি ডিলিট করতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="ডিলিট"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="text-center">কোনো নতুন অর্ডার পাওয়া যায়নি</td></tr>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* ডিলার এবং কাস্টমার রো এর জন্য হালকা কালার */
    .dealer-row { background-color: #e8f5e9 !important; }
    .customer-row { background-color: #e3f2fd !important; }

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
    }
</style>
@stop
 
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // প্রিন্ট মোডাল ওপেন করার ফাংশন
    function openPrintModal(normalUrl, posUrl, bluetoothUrl) {
        Swal.fire({
            title: 'প্রিন্ট অপশন নির্বাচন করুন',
            html: `
                <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                    <a href="${normalUrl}" target="_blank" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-file-alt"></i> সাধারণ প্রিন্ট (A4)
                    </a>
                    <a href="${posUrl}" target="_blank" class="btn btn-info btn-lg text-white" style="width: 100%;">
                        <i class="fas fa-receipt"></i> পস (POS) প্রিন্ট
                    </a>
                    <a href="${bluetoothUrl}" target="_blank" class="btn btn-secondary btn-lg" style="width: 100%;">
                        <i class="fab fa-bluetooth"></i> ব্লুটুথ প্রিন্ট
                    </a>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: '<i class="fas fa-times"></i> বাতিল',
            cancelButtonColor: '#d33',
            width: '400px'
        });
    }

    // কনফার্মেশন অ্যালার্ট (ডেলিভারি এবং ডিলিট এর জন্য)
    function confirmAction(event, message) {
        event.preventDefault(); 
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745', 
            cancelButtonColor: '#d33',
            confirmButtonText: 'হ্যাঁ, নিশ্চিত!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit(); 
            }
        });
        return false;
    }
</script>
@stop