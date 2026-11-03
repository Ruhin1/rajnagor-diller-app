@extends('adminlte::page')
@section('title', 'বিক্রয় রিপোর্ট')

@section('content')
<div class="card mt-3" id="report-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"> বিক্রয়ের রিপোর্ট</h3>
        <button id="print-btn" class="btn btn-sm btn-info print-hide">
            <i class="fas fa-print mr-1"></i> রিপোর্ট প্রিন্ট/PDF করুন
        </button>
    </div>
    <div class="card-body">
        <!-- ফিল্টার সেকশন -->
        <div class="mb-4 p-3 border rounded bg-light print-hide">
            <div class="row align-items-center">
                <!-- দ্রুত ফিল্টার বাটন -->
                <div class="col-md-6 mb-2 mb-md-0">
                    <a href="{{ route('morich.daily-sale.reports', ['filter' => 'today']) }}" class="btn btn-sm {{ $filter === 'today' ? 'btn-success' : 'btn-secondary' }}">আজকের বিক্রয়</a>
                    <a href="{{ route('morich.daily-sale.reports', ['filter' => 'week']) }}" class="btn btn-sm {{ $filter === 'week' ? 'btn-success' : 'btn-secondary' }}">এই সপ্তাহের বিক্রয়</a>
                    <a href="{{ route('morich.daily-sale.reports', ['filter' => 'month']) }}" class="btn btn-sm {{ $filter === 'month' ? 'btn-success' : 'btn-secondary' }}">এই মাসের বিক্রয়</a>
                    <a href="{{ route('morich.daily-sale.reports', ['filter' => 'all']) }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-success' : 'btn-secondary' }}">সব বিক্রয়</a>
                </div>
                
                <!-- তারিখ রেঞ্জ ফিল্টার فرم -->
                <div class="col-md-6">
                    <form action="{{ route('morich.daily-sale.reports') }}" method="GET" class="form-inline justify-content-end">
                        <div class="form-group mr-2">
                            <label for="start_date" class="mr-2">শুরুর তারিখ:</label>
                            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="end_date" class="mr-2">শেষ তারিখ:</label>
                            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> সার্চ</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- প্রিন্টের জন্য শিরোনাম -->
        <div class="d-none d-print-block text-center mb-4">
            <h2>দৈনিক বিক্রয়ের রিপোর্ট</h2>
            @if(request('start_date') && request('end_date'))
                <p><strong>রিপোর্টের সময়কাল:</strong> {{ \Carbon\Carbon::parse(request('start_date'))->format('d M, Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d M, Y') }}</p>
            @endif
            <p><strong>প্রিন্টের তারিখ:</strong> {{ now()->format('d F, Y h:i A') }}</p>
            <hr>
        </div>

        <!-- বিক্রয়ের তালিকা টেবিল -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>তারিখ</th>
                        <th class="text-right">মোট বিক্রীত পরিমাণ</th>
                        <th>নোট</th>
                        <th class="text-center print-hide">বিস্তারিত</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sales->firstItem() + $loop->index }}</td>
                        {{-- *** এখানে 'sale_adele' কে 'sale_date' করা হয়েছে *** --}}
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d F, Y') }}</td>
                        
                        {{-- *** এখানে কোডটি সংশোধন করা হয়েছে *** --}}
                        <td class="text-right font-weight-bold">
                            @php
                                $totalGrams = $sale->items->sum('quantity_sold_in_grams');
                            @endphp
                            @if ($totalGrams >= 1000)
                                {{ number_format($totalGrams / 1000, 3) }} কেজি
                            @else
                                {{ $totalGrams }} গ্রাম
                            @endif
                        </td>
                        
                        <td>{{ Str::limit($sale->notes, 30, '...') }}</td>
                        <td class="text-center print-hide">
                            <a href="{{ route('morich.daily-sale.summary', $sale->id) }}" class="btn btn-sm btn-info">
                               <i class="fas fa-eye"></i> দেখুন
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            এই ফিল্টারে কোনো বিক্রয়ের তথ্য পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer print-hide">
        {{ $sales->appends(request()->query())->links() }}
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .print-hide, .main-header, .main-footer, .content-header { display: none !important; }
        .content-wrapper, .content, .card { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
        .table { width: 100%; }
    }
</style>
@stop

@section('js')
<script>
    document.getElementById('print-btn').addEventListener('click', function() {
        window.print();
    });
</script>
@stop