@extends('adminlte::page')
@section('title', 'পণ্যভিত্তিক বিক্রয় রিপোর্ট')

@section('content')
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">পণ্যভিত্তিক বিক্রয় রিপোর্ট</h3>
        {{-- *** প্রিন্ট বাটনটি এখানে যুক্ত করা হয়েছে *** --}}
        <button class="btn btn-info btn-sm print-hide" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> রিপোর্ট প্রিন্ট/PDF করুন
        </button>
    </div>
    <div class="card-body">
        <!-- ফিল্টার সেকশন (প্রিন্টের সময় এটি লুকিয়ে থাকবে) -->
        <div class="mb-4 p-3 border rounded bg-light print-hide">
            <div class="row align-items-center">
                <!-- দ্রুত ফিল্টার বাটন -->
                <div class="col-md-7 mb-2 mb-md-0">
                    <a href="{{ route('morich.sreport', ['filter' => 'today']) }}" class="btn btn-sm {{ $filter === 'today' ? 'btn-success' : 'btn-secondary' }}">আজকের বিক্রয়</a>
                    <a href="{{ route('morich.sreport', ['filter' => 'week']) }}" class="btn btn-sm {{ $filter === 'week' ? 'btn-success' : 'btn-secondary' }}">এই সপ্তাহের বিক্রয়</a>
                    <a href="{{ route('morich.sreport', ['filter' => 'month']) }}" class="btn btn-sm {{ $filter === 'month' ? 'btn-success' : 'btn-secondary' }}">এই মাসের বিক্রয়</a>
                    <a href="{{ route('morich.sreport', ['filter' => 'all']) }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-success' : 'btn-secondary' }}">সব বিক্রয়</a>
                </div>
                
                <!-- তারিখ রেঞ্জ ফিল্টার فرم -->
                <div class="col-md-5">
                    <form action="{{ route('morich.sreport') }}" method="GET" class="form-inline justify-content-end">
                        <input type="date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $startDate }}">
                        <input type="date" name="end_date" class="form-control form-control-sm mr-2" value="{{ $endDate }}">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- প্রিন্টের জন্য একটি অতিরিক্ত শিরোনাম, যা শুধু প্রিন্টের সময় দেখা যাবে -->
        <div class="d-none d-print-block text-center mb-4">
            <h2>GF Food & Beverage Ltd.</h2>
            <h4>পণ্যভিত্তিক বিক্রয় রিপোর্ট</h4>
            @if($startDate && $endDate)
                <p><strong>সময়কাল:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
            @endif
            <p><strong>প্রিন্টের তারিখ:</strong> {{ now()->format('d F, Y h:i A') }}</p>
            <hr>
        </div>

        <!-- রিপোর্টের টেবিল -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th>পণ্যের নাম</th>
                        <th class="text-right" style="width: 30%;">মোট বিক্রির পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $grandTotalGrams = 0; 
                    @endphp
                    @forelse($allProducts as $product)
                        @php
                            $soldGrams = $salesData[$product->id] ?? 0;
                            $grandTotalGrams += $soldGrams;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-right font-weight-bold">
                                @if ($soldGrams > 0)
                                    @php
                                        $kg = floor($soldGrams / 1000);
                                        $gram = $soldGrams % 1000;
                                    @endphp
                                    
                                    @if ($kg > 0) {{ $kg }} কেজি @endif
                                    @if ($gram > 0) {{ $gram }} গ্রাম @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">কোনো পণ্যের তথ্য পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- মোট বিক্রির জন্য ফুটার --}}
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="2" class="text-right">সর্বমোট বিক্রি:</td>
                        <td class="text-right">
                            @if ($grandTotalGrams > 0)
                                @php
                                    $totalKg = floor($grandTotalGrams / 1000);
                                    $totalGram = $grandTotalGrams % 1000;
                                @endphp
                                @if ($totalKg > 0) {{ $totalKg }} কেজি @endif
                                @if ($totalGram > 0) {{ $totalGram }} গ্রাম @endif
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* এই স্টাইলটি প্রিন্ট করার সময় অপ্রয়োজনীয় অংশগুলো লুকিয়ে ফেলবে */
    @media print {
        .print-hide, .main-header, .main-sidebar, .main-footer, .content-header {
            display: none !important;
        }
        .content-wrapper, .content, .card {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .table {
            width: 100%;
        }
        .table thead {
            /* প্রিন্টে টেবিল হেডার রঙিন দেখানোর জন্য */
            -webkit-print-color-adjust: exact; 
            color-adjust: exact;
        }
    }
</style>
@stop

@section('js')
{{-- প্রিন্ট বাটনের জন্য কোনো আলাদা JS এর প্রয়োজন নেই, কারণ আমরা onclick অ্যাট্রিবিউট ব্যবহার করেছি --}}
@stop