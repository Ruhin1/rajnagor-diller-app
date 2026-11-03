@extends('adminlte::page')

@section('title', 'পেমেন্ট রিপোর্ট')

@section('content_header')
    <h1>পেমেন্ট রিপোর্ট</h1>
@stop

@section('content')
<div class="container-fluid py-2">

    {{-- ফিল্টার ফরম সেকশন --}}
    <div class="card shadow-sm mb-2 border-0">
        <div class="card-body p-2">
            <form method="GET" action="{{ route('payments.report') }}" id="filterForm">
                <div class="row g-2 align-items-center">
                    
                    {{-- সময় ফিল্টার --}}
                    <div class="col-md-2 col-6">
                        <label class="form-label mb-1 fw-bold" style="font-size:12px;">📅 সময়</label>
                        <select name="date_filter" id="dateFilter" class="form-select form-select-sm" onchange="toggleCustomDate(this.value)">
                            <option value="today" {{ $dateFilter == 'today' ? 'selected' : '' }}>আজ (Today)</option>
                            <option value="yesterday" {{ $dateFilter == 'yesterday' ? 'selected' : '' }}>গতকাল</option>
                            <option value="last_7_days" {{ $dateFilter == 'last_7_days' ? 'selected' : '' }}>গত ৭ দিন</option>
                            <option value="last_15_days" {{ $dateFilter == 'last_15_days' ? 'selected' : '' }}>গত ১৫ দিন</option>
                            <option value="last_month" {{ $dateFilter == 'last_month' ? 'selected' : '' }}>গত ৩০ দিন</option>
                            <option value="custom" {{ $dateFilter == 'custom' ? 'selected' : '' }}>কাস্টম ডেট</option>
                        </select>
                    </div>

                    {{-- পেমেন্টের ধরন ফিল্টার --}}
                    <div class="col-md-2 col-6">
                        <label class="form-label mb-1 fw-bold" style="font-size:12px;">🏷️ পেমেন্টের ধরন</label>
                        <select name="payment_type" class="form-select form-select-sm">
                            <option value="">সকল পেমেন্ট</option>
                            <option value="customer" {{ request('payment_type') == 'customer' ? 'selected' : '' }}>👤 কাস্টমার পেমেন্ট</option>
                            <option value="dealer" {{ request('payment_type') == 'dealer' ? 'selected' : '' }}>🏢 ডিলার পেমেন্ট</option>
                            <option value="dipo" {{ request('payment_type') == 'dipo' ? 'selected' : '' }}>🏭 ডিপু পেমেন্ট</option>
                        </select>
                    </div>

                    {{-- SR / ইউজার ফিল্টার --}}
                    <div class="col-md-2 col-6">
                        <label class="form-label mb-1 fw-bold" style="font-size:12px;">👤 SR / ইউজার</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">সকল SR / ইউজার</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- কাস্টম তারিখ ইনপুট --}}
                    <div class="col-md-2 col-6 custom-date-range" style="display: {{ $dateFilter == 'custom' ? 'block' : 'none' }};">
                        <label class="form-label mb-1 fw-bold" style="font-size:12px;">শুরুর তারিখ</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-2 col-6 custom-date-range" style="display: {{ $dateFilter == 'custom' ? 'block' : 'none' }};">
                        <label class="form-label mb-1 fw-bold" style="font-size:12px;">শেষ তারিখ</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>

                    {{-- বাটন সেকশন (সার্চ, রিসেট ও প্রিন্ট) --}}
                    <div class="col-md-2 col-12 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary btn-sm mt-3" title="খুঁজুন">
                            🔍
                        </button>
                        <a href="{{ route('payments.report') }}" class="btn btn-secondary btn-sm mt-3" title="রিসেট">
                            🔄
                        </a>
                        {{-- পেজিনেশন ছাড়া প্রিন্ট করার বাটন --}}
                        <a href="{{ route('payments.report', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-success btn-sm mt-3" title="প্রিন্ট করুন">
                            🖨️ প্রিন্ট
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- সামারি বার --}}
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-3">
            <div class="bg-white p-2 rounded border text-center shadow-sm">
                <span class="text-muted d-block" style="font-size:11px;">মোট ট্রানজেকশন</span>
                <strong class="text-dark fs-6">{{ number_format($totalPaymentCount) }} টি</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white p-2 rounded border text-center shadow-sm">
                <span class="text-muted d-block" style="font-size:11px;">মোট কালেকশন</span>
                <strong class="text-success fs-6">৳ {{ number_format($totalAmount, 2) }}</strong>
            </div>
        </div>
    </div>

    {{-- পেমেন্ট টেবিল --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0 text-nowrap align-middle" style="font-size:13px;">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 40px;">#</th>
                            <th>তারিখ</th>
                            <th>কাস্টমার / ডিলার / ডিপু</th>
                            <th>মেথড</th>
                            <th>রেফারেন্স</th>
                            <th>SR / ইউজার</th>
                            <th class="text-end">পরিমাণ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td class="text-center">{{ $payments->firstItem() + $index }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d-M-Y h:i A') }}</td>
                                
                                <td>
                                    @if($payment->customer_id && $payment->is_dipo == 1)
                                        <span class="badge bg-warning text-dark me-1" style="font-size:10px;">🏭 ডিপু</span>
                                        <strong>{{ $payment->customer->name ?? 'N/A' }}</strong>
                                    @elseif($payment->dealer_id)
                                        <span class="badge bg-primary me-1" style="font-size:10px;">🏢 ডিলার</span>
                                        <strong>{{ $payment->dealer->name ?? 'N/A' }}</strong>
                                    @elseif($payment->customer_id)
                                        <span class="badge bg-info text-dark me-1" style="font-size:10px;">👤 কাস্টমার</span>
                                        <strong>{{ $payment->customer->name ?? 'N/A' }}</strong>
                                    @else
                                        <span class="text-muted">গেস্ট / অন্যান্য</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ ucfirst($payment->method ?? 'Cash') }}</span>
                                </td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td>{{ $payment->srname ?? $payment->user->name ?? '-' }}</td>
                                <td class="text-end fw-bold text-success">
                                    ৳ {{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">
                                    কোনো পেমেন্ট রেকর্ড পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-2 d-flex justify-content-end">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
{!! ToastMagic::styles() !!}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
@stop

@section('js')
{!! ToastMagic::scripts() !!}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toastMagic = new ToastMagic();

    function toggleCustomDate(val) {
        const customFields = document.querySelectorAll('.custom-date-range');
        customFields.forEach(el => {
            el.style.display = (val === 'custom') ? 'block' : 'none';
        });
    }
</script> 
@stop