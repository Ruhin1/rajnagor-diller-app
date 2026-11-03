@extends('adminlte::page')

@section('title', 'সেলস রিপোর্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>সেলস রিপোর্ট</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">

    <!-- Filter Section -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('sales.reportall') }}" method="GET" id="filterForm">
                <div class="row align-items-end">
                    
                    <!-- Date Filter Selection -->
                    <div class="col-md-3 mb-3">
                        <label for="date_filter">তারিখ ফিল্টার করুন</label>
                        <select name="date_filter" id="date_filter" class="form-control">
                            <option value="today" {{ request('date_filter', 'today') == 'today' ? 'selected' : '' }}>আজ</option>
                            <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>গতকাল</option>
                            <option value="last_7_days" {{ request('date_filter') == 'last_7_days' ? 'selected' : '' }}>একসপ্তাহ (গত ৭ দিন)</option>
                            <option value="last_15_days" {{ request('date_filter') == 'last_15_days' ? 'selected' : '' }}>গত ১৫ দিন</option>
                            <option value="last_month" {{ request('date_filter') == 'last_month' ? 'selected' : '' }}>গত একমাস</option>
                            <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>যেকোনো দুই ডেট</option>
                        </select>
                    </div>

                    <!-- Custom Dates (Hidden by default) -->
                    <div class="col-md-3 mb-3" id="custom_dates" style="{{ request('date_filter') == 'custom' ? 'display: block;' : 'display: none;' }}">
                        <div class="row">
                            <div class="col-6">
                                <label for="start_date">শুরুর তারিখ</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <label for="end_date">শেষের তারিখ</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>

                    <!-- User Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="user_id">ইউজার সিলেক্ট করুন</label>
                        <select name="user_id" id="user_id" class="form-control">
                            <option value="">সব ইউজার</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} (ID: {{ $user->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons (Search & Print) -->
                    <div class="col-md-3 mb-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill mr-1">🔍 সার্চ করুন</button>
                        <a href="{{ route('sales.reportall', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-success flex-fill">🖨️ প্রিন্ট / PDF</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Widgets Section -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট মেমো/বিক্রি</span>
                    <span class="info-box-number" style="font-size: 20px;">{{ number_format($totalSalesCount) }} টি</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট বিক্রয় মূল্য</span>
                    <span class="info-box-number" style="font-size: 20px;">{{ number_format($totalAmount, 2) }} ৳</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট জমা (Paid)</span>
                    <span class="info-box-number" style="font-size: 20px;">{{ number_format($totalPaid, 2) }} ৳</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট বকেয়া (Due)</span>
                    <span class="info-box-number" style="font-size: 20px;">{{ number_format($totalDue, 2) }} ৳</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>তারিখ</th>
                        <th>ইউজার (SR)</th>
                        <th>গ্রাহকের বিবরণ</th>
                        <th>সেলস টাইপ</th>
                        <th>মোট টাকা</th>
                        <th>জমা</th>
                        <th>বকেয়া</th>
                        <th>স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $sale)
                        <tr>
                            <td>{{ $sales->firstItem() + $index }}</td>
                            <td>{{ $sale->ddate ? $sale->ddate->format('d M, Y') : (\Carbon\Carbon::parse($sale->created_at)->format('d M, Y')) }}</td>
                            <td>{{ $sale->usersr ? $sale->usersr->name : ($sale->srname ?? 'N/A') }}</td>
                            <td>
                                @if($sale->customer)
                                    {{ $sale->customer->name }} (টিপি)
                                @elseif($sale->dealer)
                                    {{ $sale->dealer->name }} (ডিলার)
                                @elseif($sale->guest_name)
                                    {{ $sale->guest_name }} (গেস্ট)
                                @else
                                    সাধারণ ক্রেতা
                                @endif
                            </td>
                            <td>
                                @if($sale->guest_sell_type == 1)
                                    <span class="badge badge-warning">টিপি গেস্ট সেলস</span>
                                @elseif($sale->guest_sell_type == 2)
                                    <span class="badge badge-info">ডিপি গেস্ট সেলস</span>
                                @elseif($sale->is_dipu == 1)
                                    <span class="badge badge-dark">ডিপু সেলস</span>
                                @elseif(!empty($sale->customer_id))
                                    <span class="badge badge-primary">টিপি সেলস</span>
                                @elseif(!empty($sale->dealer_id))
                                    <span class="badge badge-secondary">ডিপি সেলস</span>
                                @else
                                    <span class="badge badge-light">সাধারণ সেলস</span>
                                @endif
                            </td>
                            <td class="font-weight-bold">{{ number_format($sale->total, 2) }} ৳</td>
                            <td class="text-success">{{ number_format($sale->paid, 2) }} ৳</td>
                            <td class="text-danger">{{ number_format($sale->due, 2) }} ৳</td>
                            <td>
                                @if($sale->due == 0)
                                    <span class="badge badge-success">বিক্রি সম্পূর্ণ</span>
                                @else
                                    <span class="badge badge-danger">অর্ডার সম্পূর্ণ হয়েছে, বিক্রি সম্পূর্ণ হয় নি</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center font-weight-bold text-danger py-4">
                                এই ফিল্টারে কোনো সেলস রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($sales->hasPages())
            <div class="card-footer clearfix no-print">
                {{ $sales->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div> 
@stop

@section('css')
<!-- ToastMagic -->
{!! ToastMagic::styles() !!}
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@stop

@section('js')
<!-- ToastMagic -->
{!! ToastMagic::scripts() !!}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Custom Date ফিল্ড Show/Hide করার লজিক
    document.addEventListener("DOMContentLoaded", function() {
        const dateFilter = document.getElementById('date_filter');
        const customDatesDiv = document.getElementById('custom_dates');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        dateFilter.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDatesDiv.style.display = 'block';
                startDateInput.required = true;
                endDateInput.required = true;
            } else {
                customDatesDiv.style.display = 'none';
                startDateInput.required = false;
                endDateInput.required = false;
            }
        });
    });
</script>
@stop