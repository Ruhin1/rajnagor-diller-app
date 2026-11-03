@extends('adminlte::page')
@section('title', 'নতুন কাস্টমার')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-3"><div class="card-header"><h3 class="card-title">নতুন কাস্টমার যোগ করুন</h3></div>
            <form action="{{ route('custom.customers.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group"><label>নাম <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>ঠিকানা (ঐচ্ছিক)</label><input type="text" name="address" class="form-control"></div>
                    <div class="form-group"><label>এমাউন্ট <span class="text-danger">*</span></label><input type="number" name="due_amount" class="form-control" required></div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-success">সেভ করুন</button></div>
            </form>
        </div>
    </div>
</div>
@stop