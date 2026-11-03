@extends('adminlte::page')

@section('title', 'ক্লিম করা কার্ড')

@section('content_header')
    <h1>ক্লিম করা কার্ডের রিপোর্ট</h1>
@stop

@section('content')
<div class="container-fluid">

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('claimed.cards') }}" method="GET" id="filterForm">
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
                        <label for="used_by">ইউজার সিলেক্ট করুন</label>
                        <select name="used_by" id="used_by" class="form-control">
                            <option value="">সব ইউজার</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('used_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} (ID: {{ $user->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons (Search & Print) -->
                    <div class="col-md-3 mb-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill mr-1">🔍 সার্চ করুন</button>
                        <a href="{{ route('claimed.cards', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-success flex-fill">🖨️ প্রিন্ট</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Widgets Section -->
    <div class="row">
        <div class="col-md-6 col-sm-6 col-12">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট ক্লিম হওয়া কার্ড</span>
                    <span class="info-box-number" style="font-size: 22px;">{{ number_format($totalCards) }} টি</span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-6 col-12">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">মোট টাকা</span>
                    <span class="info-box-number" style="font-size: 22px;">{{ number_format($totalAwards, 2) }} ৳</span>
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
                        <th>কার্ড কোড</th>
                        <th>প্রোডাক্টের নাম</th>
                        <th>টাকা</th>
                        <th>ইউজার (Used By)</th>
                        <th>ক্লিম করার তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $index => $card)
                        <tr>
                            <td>{{ $cards->firstItem() + $index }}</td>
                            <td><span class="badge badge-secondary">{{ $card->code }}</span></td>
                            <td>{{ $card->product_name }}</td>
                            <td class="font-weight-bold text-success">{{ number_format($card->awards, 2) }} ৳</td>
                            <td>{{ $card->user ? $card->user->name : $card->used_by }}</td>
                            <td>{{ \Carbon\Carbon::parse($card->used_at)->format('d M, Y - h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center font-weight-bold text-danger py-4">
                                এই ফিল্টারে কোনো ক্লিম করা কার্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($cards->hasPages())
            <div class="card-footer clearfix">
                {{ $cards->appends(request()->query())->links('pagination::bootstrap-4') }}
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