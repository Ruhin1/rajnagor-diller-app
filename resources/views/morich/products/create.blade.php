@extends('adminlte::page')
@section('title', 'নতুন পণ্য যোগ')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">নতুন পণ্য যোগ করুন</h3></div>
            <form action="{{ route('morich.products.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">পণ্যের নাম</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="যেমন: শুকনা মরিচ" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- *** স্টক ইনপুটের জন্য নতুন ফিল্ড *** --}}
                    <label>প্রাথমিক স্টক</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="stock_kg">কেজি</label>
                                <input type="number" id="stock_kg" name="stock_kg" class="form-control @error('stock_kg') is-invalid @enderror" step="1" min="0" placeholder="_ _" value="{{ old('stock_kg', 0) }}">
                                @error('stock_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-6">
                             <div class="form-group">
                                <label for="stock_gram">গ্রাম</label>
                                <input type="number" id="stock_gram" name="stock_gram" class="form-control @error('stock_gram') is-invalid @enderror" step="1" min="0" max="999" placeholder="_ _ _" value="{{ old('stock_gram', 0) }}">
                                @error('stock_gram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    @error('stock_total')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> সেভ করুন</button>
                    <a href="{{ route('morich.products.index') }}" class="btn btn-secondary">বাতিল</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop