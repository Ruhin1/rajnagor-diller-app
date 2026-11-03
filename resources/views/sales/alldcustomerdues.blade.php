@extends('adminlte::page')

@section('title', 'কাস্টমার বকেয়া')

@section('content_header')
    <h1>কাস্টমার বকেয়া তালিকা</h1>
@stop

@section('content')
<div class="container-fluid">
    @php
    $summaryBase = \App\Models\Customer::query()
        ->where('customers.is_dipu', 1)
        ->whereHas('sales', fn($q) => $q->where('due', '>', 0));

    $totalDueAmount = (clone $summaryBase)
        ->join('sales', 'customers.id', '=', 'sales.customer_id')
        ->where('sales.due', '>', 0)
        ->sum('sales.due');

    $dueCustomersCount = (clone $summaryBase)
        ->distinct('customers.id')
        ->count('customers.id');
    @endphp
    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="dueCustomersCount">{{ $dueCustomersCount }} জন</h3>
                    <p>মোট বকেয়া কাস্টমার</p>
                </div><div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="totalDueAmount">{{ number_format($totalDueAmount, 2) }}<sup style="font-size: 20px">৳</sup></h3>
                    <p>সর্বমোট বকেয়ার পরিমাণ</p>
                </div><div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-2"></i>সার্চ ও ফিল্টার</h3></div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8 mb-2">
                    <label for="search">কাস্টমার খুঁজুন</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                        <input type="text" id="search" class="form-control" placeholder="কাস্টমার আইডি, নাম বা মোবাইল নাম্বার...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label for="sort-due">বকেয়া অনুযায়ী সাজান</label>
                    <select id="sort-due" class="form-control">
                        <option value="desc">বেশি থেকে কম</option>
                        <option value="asc">কম থেকে বেশি</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- কাস্টমার কার্ডের তালিকা এখানে লোড হবে --}}
    <div id="customer-dues-container" class="row">
        {{-- প্রাথমিক লোডের জন্য ডেটা দেখানো হচ্ছে --}}
        @php $found = false; @endphp
      
        @forelse($customers as $customer)
            @php
                $totalDue = $customer->total_due; // কন্ট্রোলার থেকে আসা pre-calculated ডেটা
            @endphp
            @if($totalDue > 0)
                @php 
                    $found = true;
                    $totalSales = $customer->total_invoices;
                    $statusClass = 'status-low';
                    if ($totalDue > 20000) $statusClass = 'status-high';
                    else if ($totalDue > 5000) $statusClass = 'status-medium';
                @endphp
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card due-card {{ $statusClass }} shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title-name mb-2">{{ $customer->name }}</h5>
                            <div class="customer-info-item"><i class="fas fa-id-card"></i> <span>আইডি: {{ $customer->id }}</span></div>
                            <div class="customer-info-item"><i class="fas fa-phone"></i> <span>{{ $customer->number ?? 'N/A' }}</span></div>
                            <div class="customer-info-item"><i class="fas fa-file-invoice"></i> <span>বকেয়া ইনভয়েস: {{ $totalSales }}</span></div>
                            <div class="mt-auto text-center pt-3">
                                <small class="text-muted">মোট বকেয়া</small>
                                <p class="due-amount">{{ number_format($totalDue, 2) }} ৳</p>
                                <form action="{{ route('customers.dues', ['customer'=> $customer->id]) }}" method="GET" class="mt-2">
                                    <button class="btn btn-primary btn-block"><i class="fas fa-hand-holding-usd mr-2"></i> বকেয়া পরিশোধ করুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
             <div class="col-12">
                <div class="no-data-found"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>কোনো কাস্টমারের বকেয়া পাওয়া যায়নি।</p></div>
            </div>
        @endforelse
    </div>

    {{-- Pagination কন্টেইনার --}}
    <div id="pagination-container" class="d-flex justify-content-center mt-3">
        {!! $customers->links() !!}
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .due-card { border-left: 5px solid; transition: all 0.3s ease; }
    .due-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .status-dot { height: 12px; width: 12px; border-radius: 50%; display: inline-block; }
    .due-card.status-high { border-left-color: #dc3545; }
    .due-card.status-medium { border-left-color: #ffc107; }
    .due-card.status-low { border-left-color: #28a745; }
    .card-title-name { font-weight: 600; font-size: 1.2rem; }
    .due-amount { font-size: 1.5rem; font-weight: 700; color: #dc3545; }
    .customer-info-item { display: flex; align-items: center; margin-bottom: 8px; color: #6c757d; }
    .customer-info-item i { width: 20px; text-align: center; margin-right: 10px; color: #007bff; }
    .no-data-found { text-align: center; padding: 50px; font-size: 1.2rem; color: #6c757d; }
</style>
@stop
 


@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function renderCustomers(customers) {
        let html = '';
        if (customers && customers.length > 0) {
            customers.forEach(customer => {
                let totalDue = parseFloat(customer.total_due).toFixed(2);
                let statusClass = 'status-low';
                if (totalDue > 20000) statusClass = 'status-high';
                else if (totalDue > 5000) statusClass = 'status-medium'; 
                
                // **সংশোধন:** URL তৈরির জন্য সঠিক পদ্ধতি ব্যবহার করা হয়েছে
                let payUrl = "{{ route('customers.dues', ['customer' => ':id']) }}".replace(':id', customer.id);

                html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card due-card ${statusClass} shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title-name mb-2">${customer.name}</h5>
                            <div class="customer-info-item"><i class="fas fa-id-card"></i> <span>আইডি: ${customer.id}</span></div>
                            <div class="customer-info-item"><i class="fas fa-store-alt"></i> <span>দোকানের নাম: ${customer.shop_name ? customer.shop_name : 'নাম যুক্ত করা হয়নি'}</span></div>
                            <div class="customer-info-item"><i class="fas fa-phone"></i> <span>${customer.number || 'N/A'}</span></div>
                            <div class="customer-info-item"><i class="fas fa-file-invoice"></i> <span>বকেয়া ইনভয়েস: ${customer.total_invoices}</span></div>
                            <div class="mt-auto text-center pt-3">
                                <small class="text-muted">মোট বকেয়া</small>
                                <p class="due-amount">${totalDue} ৳</p>
                                <form action="${payUrl}" method="GET" class="mt-2">
                                    <button class="btn btn-primary btn-block"><i class="fas fa-hand-holding-usd mr-2"></i> বকেয়া পরিশোধ করুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html = `<div class="col-12"><div class="no-data-found"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>কোনো কাস্টমারের বকেয়া পাওয়া যায়নি।</p></div></div>`;
        }
        $('#customer-dues-container').html(html);
    }

    function renderPagination(links) {
        let html = '<nav><ul class="pagination">';
        links.forEach(link => {
            const label = new DOMParser().parseFromString(link.label, "text/html").documentElement.textContent;
            html += `<li class="page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}">
                        <a class="page-link" href="${link.url}">${label}</a>
                     </li>`;
        });
        html += '</ul></nav>';
        $('#pagination-container').html(html);
    }
    
    function updateSummary(summary) {
        if (summary) {
            $('#dueCustomersCount').text(`${summary.dueCustomersCount} জন`);
            $('#totalDueAmount').html(`${summary.totalDueAmount}<sup style="font-size: 20px">৳</sup>`);
        }
    }

    function fetchData(url) {
        if (!url) {
            url = "{{ route('alldcustomerdues') }}";
        }
        
        let query = $('#search').val();
        let sort = $('#sort-due').val();

        $('#customer-dues-container').html('<div class="col-12 text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

        $.ajax({
            url: url,
            type: "GET",
            data: { search: query, sort: sort },
            success: function(response){
                renderCustomers(response.customers.data);
                renderPagination(response.customers.links);
                updateSummary(response.summary);
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                $('#customer-dues-container').html('<div class="col-12"><div class="no-data-found">ডেটা লোড করতে সমস্যা হয়েছে।</div></div>');
            }
        }); 
    }

    $('#search').on('keyup', debounce(function() {
        fetchData();
    }, 500));

    $('#sort-due').on('change', function() {
        fetchData();
    });

    $(document).on('click', '#pagination-container .pagination a', function(e){
        e.preventDefault();
        let url = $(this).attr('href');
        if (url && !$(this).parent().hasClass('disabled')) {
            fetchData(url);
        }
    });

    // প্রাথমিক লোডের সময় ডেটা দেখানোর জন্য AJAX কল করা হচ্ছে
    fetchData();
});
</script>
@stop