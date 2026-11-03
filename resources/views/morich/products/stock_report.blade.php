@extends('adminlte::page')

@section('title', 'পণ্যের স্টক রিপোর্ট')

@section('content_header')
    <h1>পণ্যের বর্তমান স্টক রিপোর্ট</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow" id="report-card">
                <div class="card-header d-flex justify-content-between align-items-center print-hide">
                    <h3 class="card-title mb-0">স্টকের সারাংশ</h3>
                    <button class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> রিপোর্ট প্রিন্ট করুন
                    </button>
                </div>

                <div class="card-body">
                    <!-- প্রিন্টের জন্য হেডার, যা শুধু প্রিন্টের সময় দেখা যাবে -->
                    <div class="d-none d-print-block mb-4">
                        <div class="row">
                            <div class="col-8">
                                <h2 class="mb-1">GF Food & Beverage Ltd.</h2>
                                <p class="mb-0">পণ্যের বর্তমান স্টক রিপোর্ট</p>
                            </div>
                            <div class="col-4 text-right">
                                <p class="mb-0"><strong>রিপোর্টের তারিখ:</strong> {{ now()->format('d F, Y h:i A') }}</p>
                            </div>
                        </div>
                        <hr>
                    </div>

                    <!-- স্ক্রিনে দেখানোর জন্য তারিখ -->
                    <h4 class="d-print-none text-center mb-4"><strong>রিপোর্টের তারিখ:</strong> {{ now()->format('d F, Y h:i A') }}</h4>

                    <!-- স্টকের টেবিল -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 10%;">#</th>
                                    <th>পণ্যের নাম</th>
                                    <th class="text-right" style="width: 30%;">বর্তমান স্টক</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-right font-weight-bold">
                                        {{-- গ্রামকে কেজিতে রূপান্তর করে দেখানো হচ্ছে --}}
                                        {{ number_format($product->stock_in_grams / 1000, 3) }} কেজি
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">কোনো পণ্যের তথ্য পাওয়া যায়নি।</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- স্বাক্ষর সেকশন -->
                    <div class="signature-section mt-5 pt-5">
                        <div class="signature-box">
                            <p class="signature-title">অফিস</p>
                        </div>
                        <div class="signature-box">
                            <p class="signature-title">মুমিন আহমেদ</p>
                        </div>
                        <div class="signature-box">
                            <p class="signature-title">রাহেল আহমেদ</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .signature-section {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }
    .signature-box {
        width: 30%;
        text-align: center;
    }
    .signature-title {
        border-top: 2px dotted #000;
        padding-top: 10px;
        font-weight: bold;
    }

    /* প্রিন্টের জন্য মিডিয়া কোয়েরি */
    @media print {
        /* অপ্রয়োজনীয় অংশ লুকিয়ে ফেলা */
        .print-hide, .main-header, .main-sidebar, .main-footer, .content-header {
            display: none !important;
        }
        /* প্রিন্টের জন্য কন্টেন্টকে পুরো পেজ জুড়ে দেওয়া */
        .content-wrapper, .content, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@stop