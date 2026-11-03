@extends('adminlte::page')
@section('title', 'স্টেটমেন্ট - ' . $customer->name)
@section('content')
<div class="statement-wrapper">
    <button class="print-button" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট</button>
    <header class="header">
        <div><h3>{{ $customer->name }}</h3><p>{{ $customer->address }}</p></div>
        <div class="text-right"><h4>বকেয়ার বিবরণী</h4><p>তারিখ: {{ date('d-m-Y') }}</p></div>
    </header>
    <table class="transaction-table">
        <thead><tr><th>তারিখ</th><th>বিবরণ</th><th class="text-right">বিল/দেনা (৳)</th><th class="text-right">জমা (৳)</th><th class="text-right">অবশিষ্ট (৳)</th></tr></thead>
        <tbody>
            @php $balance = 0; @endphp
            @forelse($transactions as $tx)
                @if($tx->type == 'debit')
                    @php $balance += $tx->amount; @endphp
                    <tr><td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d-m-Y') }}</td><td>{{ $tx->description ?? 'নতুন বিল' }}</td><td class="text-right">{{ number_format($tx->amount, 2) }}</td><td class="text-right">-</td><td class="text-right">{{ number_format($balance, 2) }}</td></tr>
                @else
                    @php $balance -= $tx->amount; @endphp
                    <tr style="background-color: #eaf7e9;"><td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d-m-Y') }}</td><td>{{ $tx->description ?? 'বকেয়া জমা' }}</td><td class="text-right">-</td><td class="text-right">{{ number_format($tx->amount, 2) }}</td><td class="text-right">{{ number_format($balance, 2) }}</td></tr>
                @endif
            @empty
                <tr><td colspan="5" class="text-center">কোনো লেনদেন নেই।</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="4" class="text-right font-weight-bold">বর্তমান বকেয়া:</td><td class="text-right font-weight-bold">{{ number_format($customer->due_amount, 2) }} ৳</td></tr></tfoot>
    </table>
</div>
@stop
@section('css')
<style>
/* ... আগের স্টেটমেন্টের CSS কোড এখানে পেস্ট করুন, অথবা এটি ব্যবহার করুন ... */
.statement-wrapper { background: #fff; padding: 2rem; margin: 1rem auto; max-width: 800px; box-shadow: 0 0 15px rgba(0,0,0,.1); position: relative; }
.transaction-table { width: 100%; border-collapse: collapse; }
.transaction-table th, .transaction-table td { border: 1px solid #dee2e6; padding: .75rem; }
.text-right { text-align: right; }
.print-button { position: absolute; top: 1rem; right: 1rem; }
@media print { body { background: #fff; } .main-footer, .print-button, .main-header, .content-header, .main-sidebar { display: none !important; } .content-wrapper { padding: 0 !important; } .statement-wrapper { box-shadow: none; margin: 0; max-width: 100%; } }
</style>
@stop