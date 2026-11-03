@extends('adminlte::page')
@section('title', 'স্টক মুভমেন্ট রিপোর্ট')

@section('content')
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">স্টক মুভমেন্ট রিপোর্ট (স্টক লেজার)</h3>
        <button class="btn btn-info btn-sm print-hide" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> রিপোর্ট প্রিন্ট/PDF করুন
        </button>
    </div>
    <div class="card-body">
        <!-- ফিল্টার সেকশন -->
        <div class="mb-4 p-3 border rounded bg-light print-hide">
            <form action="{{ route('morich.products.stock-movement') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">শুরুর তারিখ:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" required>
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">শেষ তারিখ (ঐচ্ছিক):</label>
                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> রিপোর্ট দেখুন</button>
            </form>
        </div>

        <!-- প্রিন্টের জন্য শিরোনাম -->
        <div class="d-none d-print-block text-center mb-4">
            <h2>GF Food & Beverage Ltd.</h2>
            <h4>স্টক মুভমেন্ট রিপোর্ট</h4>
            <p><strong>সময়কাল:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
            <p><strong>প্রিন্টের তারিখ:</strong> {{ now()->format('d F, Y') }}</p>
            <hr>
        </div>

        <!-- রিপোর্টের টেবিল -->
        <div class="table-responsive">
   <table class="table table-bordered table-striped">
        <thead class="thead-dark text-center">
            <tr>
                <th rowspan="2" style="vertical-align: middle;">#</th>
                <th rowspan="2" style="vertical-align: middle;">পণ্যের নাম</th>
                <th colspan="5">স্টকের হিসাব (কেজি/গ্রাম)</th>
            </tr>
            <tr>
                <th class="text-right">পূর্বের স্টক</th>
                <th class="text-right">নতুন স্টক (যোগ)</th>
                <th class="text-right bg-info">মোট স্টক</th>
                <th class="text-right">মোট বিক্রয় (বিয়োগ)</th>
                <th class="text-right">অবশিষ্ট স্টক</th>
            </tr>
        </thead>
        <tbody>
            @php
                function formatGrams($grams) {
                    if ($grams < 0) return '<span class="text-danger">Error</span>';
                    if ($grams == 0) return '-';
                    $kg = floor($grams / 1000);
                    $gram = $grams % 1000;
                    $result = '';
                    if ($kg > 0) $result .= $kg . ' কেজি ';
                    if ($gram > 0) $result .= $gram . ' গ্রাম';
                    return trim($result);
                }
                $grandTotalStockIn = 0;
                $grandTotalSold = 0;
            @endphp
            @forelse($reportData as $data)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $data['name'] }}</td>
                <td class="text-right">{!! formatGrams($data['previous_stock']) !!}</td>
                <td class="text-right text-success font-weight-bold">{!! formatGrams($data['stock_in']) !!}</td>
                <td class="text-right font-weight-bold bg-light">{!! formatGrams($data['total_stock']) !!}</td>
                <td class="text-right text-danger font-weight-bold">{!! formatGrams($data['current_sale']) !!}</td>
                <td class="text-right font-weight-bold">{!! formatGrams($data['closing_stock']) !!}</td>
            </tr>
            @php
                $grandTotalStockIn += $data['stock_in'];
                $grandTotalSold += $data['current_sale'];
            @endphp
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">কোনো পণ্যের তথ্য পাওয়া যায়নি।</td>
            </tr>
            @endforelse
        </tbody>
         <tfoot class="bg-light font-weight-bold">
            <tr>
                <td colspan="3" class="text-right">নির্বাচিত সময়ে সর্বমোট:</td>
                <td class="text-right text-success">{!! formatGrams($grandTotalStockIn) !!}</td>
                <td></td> {{-- মোট স্টকের ফুটার খালি --}}
                <td class="text-right text-danger">{!! formatGrams($grandTotalSold) !!}</td>
                <td></td> {{-- অবশিষ্ট স্টকের ফুটার খালি --}}
            </tr>
        </tfoot>
    </table>
</div>
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .print-hide, .main-header, .main-sidebar, .main-footer, .content-header {
            display: none !important;
        }
        .content-wrapper, .content, .card {
            margin: 0 !important; padding: 0 !important; box-shadow: none !important;
        }
    }
</style>
@stop