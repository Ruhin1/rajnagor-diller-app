@extends('adminlte::page')
@section('title', 'পণ্যভিত্তিক বিক্রয় বিশ্লেষণ')

@section('content')
<div class="card mt-3 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold mb-2 mb-md-0">পণ্যভিত্তিক বিক্রয় বিশ্লেষণ</h3>
        <div class="card-tools d-flex align-items-center">
            
            {{-- *** নতুন: মাস সিলেক্ট করার فرم *** --}}
            <form action="{{ route('reports.product-sales-analysis') }}" method="GET" class="form-inline mr-3 print-hide">
                <label for="month_select" class="mr-2">মাস সিলেক্ট করুন:</label>
                <input type="month" id="month_select" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                {{-- একটি অদৃশ্য বাটন, যা Enter চাপলে فرم সাবমিট করবে --}}
                <button type="submit" style="display: none;"></button>
            </form>
            
            <button class="btn btn-info btn-sm print-hide" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> প্রিন্ট করুন
            </button> 
        </div>
    </div>
    <div class="card-body">
        
        {{-- প্রিন্টের জন্য শিরোনাম --}}
        <div class="d-none d-print-block text-center mb-4">
            <h2>পণ্যভিত্তিক বিক্রয় বিশ্লেষণ</h2>
            <p><strong>মাস:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F, Y') }}</p>
            <hr>
        </div>

        {{-- বড় স্ক্রিনের জন্য টেবিল (ডেক্সটপ) --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark text-center">
                    <tr>
                        <th rowspan="2" class="align-middle">#</th>
                        <th rowspan="2" class="align-middle">পণ্যের নাম</th>
                        <th rowspan="2" class="align-middle">মোট বিক্রি (পিস)</th>
                        <th colspan="3">মূল্যভিত্তিক বিক্রি</th>
                        <th colspan="4">পেমেন্টভিত্তিক বিক্রি</th>
                    </tr>
                    <tr>
                        <th>ডিলার প্রাইস (DP)</th>
                        <th>ট্রেড প্রাইস (TP)</th>
                        <th class="align-middle">ডিপো বিক্রি</th>
                        <th>গেস্ট (TP)</th>
                        <th>গেস্ট (DP)</th>
                        <th>বাকিতে</th>
                        <th>নগদে</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productsReport as $report)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $report->product_name }}</td>
                        <td class="text-center font-weight-bold bg-light">{{ $report->total_sold }}</td>
                        <td class="text-center">{{ $report->sold_at_dp }}</td>
                        <td class="text-center">{{ $report->sold_at_tp }}</td>
                        <td class="text-center text-warning">{{ $report->sold_at_dipu }}</td>
                        <td class="text-center text-primary">{{ $report->sold_as_guest_tp }}</td> 
                        <td class="text-center text-primary">{{ $report->sold_as_guest_dp }}</td> 
                        <td class="text-center text-danger">{{ $report->sold_on_due }}</td>
                        <td class="text-center text-success">{{ $report->sold_on_cash }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">এই মাসের জন্য কোনো বিক্রয়ের তথ্য পাওয়া যায়নি।</td></tr>
                    @endforelse
                     {{-- <tr>
                        <td></td>
                        <td>1</td>
                        <td>1</td> 
                        <td>1</td>
                        <td>1</td>
                        <td>1</td>
                        <td>1</td>
                        <td>1</td>
                        <td>1</td>
                        <td>1</td>
                        
                    </tr> --}}
                </tbody>
            </table>
        </div>

        
    </div>
</div>
@stop

@section('css')
<style>
    /* মোবাইল কার্ডের জন্য কাস্টম স্টাইল */
    .product-report-card {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
    }
    .product-report-card .card-title {
        padding: .75rem 1.25rem;
        background-color: rgba(0,0,0,.03);
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-report-card h5 {
        margin: 0;
        font-weight: bold;
    }
    .total-sold-badge {
        background-color: #343a40;
        color: #fff;
        padding: .25em .6em;
        border-radius: .25rem;
        font-size: 90%;
    }
    .product-report-card .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .product-report-card .list-group-item span {
        color: #6c757d;
    }
    .product-report-card .list-group-item i {
        width: 20px; /* আইকনগুলোর অ্যালাইনমেন্ট ঠিক রাখার জন্য */
    }
</style>
@stop
@section('js')
<script>
    // মাস পরিবর্তন হলে স্বয়ংক্রিয়ভাবে فرم সাবমিট করার জন্য
    document.getElementById('month_select').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@stop 