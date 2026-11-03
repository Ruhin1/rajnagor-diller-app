@extends('adminlte::page')
@section('title', 'তারিখভিত্তিক স্টক লেজার')
@section('plugins.Select2', true)

@section('content')
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">তারিখভিত্তিক স্টক লেজার</h3>
        <button class="btn btn-info btn-sm print-hide" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> রিপোর্ট প্রিন্ট/PDF করুন
        </button>
    </div>
    <div class="card-body">
        <!-- ফিল্টার সেকশন -->
        <div class="mb-4 p-3 border rounded bg-light print-hide">
            <form action="{{ route('morich.products.date-wise-ledger') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="product_id">পণ্য (ঐচ্ছিক)</label>
                        <select name="product_id" id="product_id" class="form-control select2">
                            <option value="">সব পণ্য</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @if($productId == $product->id) selected @endif>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="start_date">শুরুর তারিখ</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="end_date">শেষ তারিখ</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> রিপোর্ট দেখুন</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- প্রিন্টের জন্য শিরোনাম -->
        <div class="d-none d-print-block text-center mb-4">
            <!-- ... প্রিন্টের হেডার ... -->
        </div>

        <!-- রিপোর্টের টেবিল -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark text-center">
                    <tr>
                        <th>তারিখ</th>
                        <th class="text-right">দিনের শুরু (স্টক)</th>
                        <th class="text-right">নতুন স্টক (যোগ)</th>
                        <th class="text-right">মোট বিক্রয় (বিয়োগ)</th>
                        <th class="text-right">দিনের শেষ (স্টক)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        function formatGrams($grams) {
                            if ($grams == 0) return '-';
                            if ($grams < 0) return '<span class="text-danger">' . number_format(abs($grams) / 1000, 3) . ' কেজি</span>';
                            return number_format($grams / 1000, 3) . ' কেজি';
                        }
                    @endphp
                    @forelse($ledgerData as $data)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($data['date'])->format('d M, Y') }}</td>
                        <td class="text-right">{!! formatGrams($data['opening_stock']) !!}</td>
                        <td class="text-right text-success font-weight-bold">{!! formatGrams($data['stock_in']) !!}</td>
                        <td class="text-right text-danger font-weight-bold">{!! formatGrams($data['sale_out']) !!}</td>
                        <td class="text-right font-weight-bold bg-light">{!! formatGrams($data['closing_stock']) !!}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">এই সময়সীমার মধ্যে কোনো লেনদেন পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-secondary font-weight-bold">
                    <tr>
                        <td class="text-right">শুরুর ব্যালেন্স:</td>
                        <td class="text-right">{!! formatGrams($openingBalance) !!}</td>
                        <td class="text-right text-success">মোট যোগ: {!! formatGrams(collect($ledgerData)->sum('stock_in')) !!}</td>
                        <td class="text-right text-danger">মোট বিক্রয়: {!! formatGrams(collect($ledgerData)->sum('sale_out')) !!}</td>
                        <td class="text-right">শেষ ব্যালেন্স: {!! formatGrams(end($ledgerData)['closing_stock'] ?? $openingBalance) !!}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* ... আপনার প্রিন্টের CSS অপরিবর্তিত থাকবে ... */
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@stop