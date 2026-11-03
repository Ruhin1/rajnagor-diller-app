@extends('adminlte::page')
@section('title', 'পণ্য এডিট')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">পণ্যের তথ্য এডিট করুন</h3></div>
            <form action="{{ route('morich.products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">পণ্যের নাম</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-info">
                        <strong>বর্তমান স্টক:</strong> {{ number_format($product->stock_in_grams / 1000, 3) }} কেজি
                    </div>

                    {{-- *** স্টক যোগ করার জন্য নতুন ফিল্ড *** --}}
                    <label>নতুন স্টক যোগ করুন</label>
                    <small class="form-text text-muted mb-2">বিদ্যমান স্টকের সাথে নতুন পরিমাণ যোগ হবে।</small>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="add_stock_kg">কেজি</label>
                                <input type="number" id="add_stock_kg" name="add_stock_kg" class="form-control @error('add_stock_kg') is-invalid @enderror" step="1" min="0" placeholder="_ _" value="{{ old('add_stock_kg') }}">
                                @error('add_stock_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                             <div class="form-group">
                                <label for="add_stock_gram">গ্রাম</label>
                                <input type="number" id="add_stock_gram" name="add_stock_gram" class="form-control @error('add_stock_gram') is-invalid @enderror" step="1" min="0" max="999" placeholder="_ _ _" value="{{ old('add_stock_gram') }}">
                                @error('add_stock_gram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-sync-alt mr-1"></i> আপডেট করুন</button>
                    <a href="{{ route('morich.products.index') }}" class="btn btn-secondary">বাতিল</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop