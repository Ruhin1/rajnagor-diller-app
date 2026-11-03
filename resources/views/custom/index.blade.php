@extends('adminlte::page')
@section('title', 'কাস্টমার বকেয়া তালিকা')

@section('content')
<!-- Add Due Modal -->
<div class="modal fade" id="addDueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form id="dueForm" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">নতুন বিল যোগ করুন (<span id="dueCustomerName"></span>)</h5></div>
            <div class="modal-body">
                <input type="hidden" name="type" value="debit">
                <div class="form-group"><label>পরিমাণ (৳)</label><input type="number" name="amount" class="form-control" step="any" required></div>
                <div class="form-group"><label>তারিখ</label><input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="form-group"><label>বিবরণ</label><input type="text" name="description" class="form-control" placeholder="যেমন: নতুন পণ্য ক্রয়"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button></div>
        </form>
    </div></div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form id="paymentForm" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">বকেয়া জমা নিন (<span id="paymentCustomerName"></span>)</h5></div>
            <div class="modal-body">
                <input type="hidden" name="type" value="credit">
                <div class="form-group"><label>পরিমাণ (৳)</label><input type="number" name="amount" class="form-control" step="any" required></div>
                <div class="form-group"><label>তারিখ</label><input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="form-group"><label>বিবরণ</label><input type="text" name="description" class="form-control" placeholder="যেমন: নগদ/বিকাশ পেমেন্ট"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">জমা করুন</button></div>
        </form>
    </div></div>
</div>

<div class="card mt-3 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title font-weight-bold mb-2 mb-md-0">কাস্টমার বকেয়া তালিকা</h3>
        <div class="card-tools d-flex align-items-center">
            {{-- *** নতুন: লাইভ সার্চ ইনপুট *** --}}
            <input type="text" id="liveSearchInput" class="form-control form-control-sm mr-3" placeholder="কাস্টমারের নাম খুঁজুন...">
            <a href="{{ route('custom.customers.create') }}" class="btn btn-primary btn-sm mr-2"><i class="fas fa-plus"></i> নতুন কাস্টমার</a>
            {{-- *** নতুন: প্রিন্ট বাটন *** --}}
            <button class="btn btn-info btn-sm" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট</button>
        </div>
    </div>
    <div class="card-body">
         {{-- আপনার alert partial টি এখানে থাকবে --}}
         @include('custom.alert') 
        
        {{-- প্রিন্টের জন্য হেডার, যা শুধু প্রিন্টের সময় দেখা যাবে --}}
        <div class="d-none d-print-block text-center mb-4">
            <h2>কাস্টমার বকেয়ার তালিকা</h2>
            <p><strong>তারিখ:</strong> {{ now()->format('d F, Y') }}</p>
            <hr>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="customerTable">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>নাম</th>
                        <th>ঠিকানা</th>
                        <th class="text-right">বর্তমান বকেয়া (৳)</th>
                        <th class="text-center print-hide">অ্যাকশন</th> {{-- প্রিন্টে এটি হাইড থাকবে --}}
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalDue = 0;
                    @endphp
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->address ?? '-' }}</td>
                        <td class="text-right font-weight-bold @if($customer->due_amount > 0) text-danger @else text-success @endif">
                            {{ number_format($customer->due_amount, 2) }}
                        </td>
                        <td class="text-center print-hide">
                            <button class="btn btn-primary btn-sm add-due-btn" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="নতুন বিল"><i class="fas fa-plus"></i></button>
                            <button class="btn btn-success btn-sm add-payment-btn" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="জমা"><i class="fas fa-minus"></i></button>
                            <a href="{{ route('custom.customers.statement', $customer->id) }}" class="btn btn-info btn-sm" title="স্টেটমেন্ট"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @php
                        $totalDue += $customer->due_amount;
                    @endphp
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">কোনো কাস্টমার পাওয়া যায়নি।</td></tr>
                    @endforelse
                </tbody>
                {{-- মোট বকেয়ার জন্য নতুন ফুটার --}}
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="3" class="text-right">সর্বমোট বকেয়া:</td>
                        <td class="text-right text-danger">{{ number_format($totalDue, 2) }}</td>
                        <td class="print-hide"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer print-hide">{{ $customers->links() }}</div>
</div>
@stop

@section('css')
<style>
    /* প্রিন্টের জন্য মিডিয়া কোয়েরি */
    @media print {
        .print-hide, .main-header, .main-sidebar, .main-footer, .content-header, .card-title, .card-tools {
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
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function(){
    // লাইভ সার্চের জন্য জাভাস্ক্রিপ্ট
    $("#liveSearchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#customerTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // মডাল খোলার জন্য আগের কোড
    $('.add-due-btn').click(function(){
        const customerId = $(this).data('id');
        const customerName = $(this).data('name');
        $('#dueCustomerName').text(customerName);
        $('#dueForm').attr('action', `/custom/transactions/${customerId}`);
        $('#addDueModal').modal('show');
    });

    $('.add-payment-btn').click(function(){
        const customerId = $(this).data('id');
        const customerName = $(this).data('name');
        $('#paymentCustomerName').text(customerName);
        $('#paymentForm').attr('action', `/custom/transactions/${customerId}`);
        $('#addPaymentModal').modal('show');
    });
});
</script>
@stop