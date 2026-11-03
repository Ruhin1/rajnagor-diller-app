@extends('adminlte::page')
@section('title', 'পণ্যের তালিকা')
@section('plugins.Sweetalert2', true)
@section('content')
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">পণ্যের তালিকা ও স্টক</h3>
        <a href="{{ route('morich.products.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> নতুন পণ্য যোগ করুন</a>
    </div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <div class="table-responsive"><table class="table table-bordered table-striped"><thead class="thead-dark"><tr><th>#</th><th>পণ্যের নাম</th><th class="text-right">বর্তমান স্টক</th><th class="text-center" style="width: 15%;">অ্যাকশন</th></tr></thead><tbody>@forelse($products as $product)<tr><td>{{ $loop->iteration }}</td><td>{{ $product->name }}</td><td class="text-right font-weight-bold">{{ number_format($product->stock_in_grams / 1000, 3) }} কেজি</td><td class="text-center"><a href="{{ route('morich.products.edit', $product->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> এডিট</a>
        {{-- <form action="{{ route('morich.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই পণ্যটি ডিলেট করতে চান?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> ডিলেট</button></form> --}}
        </td></tr>@empty<tr><td colspan="4" class="text-center text-muted">এখনো কোনো পণ্য যোগ করা হয়নি।</td></tr>@endforelse</tbody></table></div>
    </div>
    <div class="card-footer">{{ $products->links() }}</div>
</div>
@stop