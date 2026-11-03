@extends('adminlte::page')
@section('title', 'দৈনিক বিক্রয়ের সারসংক্ষেপ')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8" id="invoice-area">
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">বিক্রয়ের সারসংক্ষেপ</h3>
                <div class="print-hide">
                     <a href="{{ route('morich.daily-sale.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> নতুন এন্ট্রি</a>
                    <button onclick="window.print()" class="btn btn-sm btn-info"><i class="fas fa-print mr-1"></i> প্রিন্ট করুন</button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success print-hide">{{ session('success') }}</div>@endif
                <div class="row mb-4">
                    <div class="col-md-6"><h5><strong>বিক্রয়ের তারিখ:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d F, Y') }}</h5><p class="mb-0"><strong>এন্ট্রি দিয়েছেন:</strong> {{ $sale->user->name ?? 'N/A' }}</p><p class="mb-0"><strong>এন্ট্রি সময়:</strong> {{ $sale->created_at->format('h:i A') }}</p></div>
                    @if($sale->notes)<div class="col-md-6"><p class="mb-1"><strong>নোট:</strong></p><p class="border p-2 rounded bg-light">{{ $sale->notes }}</p></div>@endif
                </div>
                <div class="mb-4"><h5 class="bg-success p-2 rounded text-white">এই দিনের বিক্রীত পণ্যের তালিকা</h5><table class="table table-bordered table-striped"><thead class="thead-dark"><tr><th>#</th><th>পণ্যের নাম</th><th class="text-right">বিক্রিত পরিমাণ</th></tr></thead><tbody>@foreach($sale->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->product->name }}</td><td class="text-right font-weight-bold">{{ $item->quantity_sold_in_grams >= 1000 ? ($item->quantity_sold_in_grams / 1000) . ' কেজি' : $item->quantity_sold_in_grams . ' গ্রাম' }}</td></tr>@endforeach</tbody></table></div>
                <div><h5 class="bg-info p-2 rounded text-white">বর্তমান স্টক স্ট্যাটাস</h5><table class="table table-bordered"><thead class="thead-light"><tr><th>#</th><th>পণ্যের নাম</th><th class="text-right">অবশিষ্ট স্টক</th></tr></thead><tbody>@foreach($allProducts as $product)<tr><td>{{ $loop->iteration }}</td><td>{{ $product->name }}</td><td class="text-right font-weight-bold">{{ number_format($product->stock_in_grams / 1000, 3) }} কেজি</td></tr>@endforeach</tbody></table></div>
            </div>
        </div>
    </div>
</div>
@stop
@section('css')
<style> @media print { .main-footer, .print-hide, .main-header, .content-header { display: none !important; } .content-wrapper, .content { padding: 0 !important; margin: 0 !important; } .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; } } </style>
@stop