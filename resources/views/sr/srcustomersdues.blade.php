@extends('adminlte::page')

@section('title', 'SR Due Confirmation')

@section('content_header')
    <h1>SR Due Confirmation</h1>
@stop

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6 mx-auto mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user-check mr-2"></i> Select SR</h5>
                </div>
                <div class="card-body">
                    <form id="userSelectForm" action="{{ route('srcustomersdues') }}" method="get">
                        <div class="input-group">
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">-- SR সিলেক্ট করুন --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @if($user && $user->id == $u->id) selected @endif>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($user)
            <div class="col-12">
                <div class="card mb-4 shadow-sm border-left-primary">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                        <div class="info-item">
                            <h5 class="mb-1"><strong>SR Name:</strong> <span class="text-primary">{{ $user->name }}</span></h5>
                        </div>
                        <div class="info-item">
                            <h5 class="mb-0"><strong>Total Pending:</strong> <span class="text-danger font-weight-bold">৳{{ number_format($amount, 2) }}</span></h5>
                        </div>
                    </div>
                </div>

                {{-- Payments Table --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-ul mr-2"></i> Pending Payments List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Name</th>
                                        <th>Shop/Institution</th>
                                        <th>Address</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Paid At</th>
                                        <th>Sale Type</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr> 
                                </thead>
                                <tbody>
                                    
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->sale_id }}</td>
                                            
                                            <td>
                                                @if ($payment->customer)
                                                    <a href="{{ url('/customers/' . $payment->customer_id . '/dues') }}">
                                                        {{ $payment->customer->name }}
                                                    </a>
                                                @elseif ($payment->dealer)
                                                    <a href="{{ url('/dealer/' . $payment->dealer_id . '/dues') }}">
                                                        {{ $payment->dealer->name }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if ($payment->customer)
                                                    {{ $payment->customer->shop_name ?? 'N/A' }}
                                                @elseif ($payment->dealer)
                                                    {{ $payment->dealer->pname ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if ($payment->customer)
                                                    {{ $payment->customer->shop_address ?? 'N/A' }}
                                                @elseif ($payment->dealer)
                                                    {{ $payment->dealer->address ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->method }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M, Y') }}</td>
                                            <td>{!! $payment->customer_id ? '<span class="badge badge-success">Customer</span>' : '<span class="badge badge-info">Dealer</span>' !!}</td>
                                            <td><span class="badge badge-warning">Pending</span></td>
                                            <td class="text-center">
                                                <form action="{{ route('payments.confirm', $payment->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="button" class="btn btn-success btn-sm confirm-btn" title="Confirm this payment">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No pending payments found for this SR.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($payments->count() > 0)
                    <div class="card mt-4 shadow-sm border-left-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-double mr-2"></i> Confirm All Payments</h5>
                        </div>
                        <div class="card-body text-center">
                            <p class="card-text h5">
                                {{ $user->name }}-এর মোট <strong class="text-danger">৳{{ number_format($amount, 2) }}</strong> টাকা কনফার্ম করতে চান?
                            </p>
                            <form action="{{ route('payments.confirmAll', $user->id) }}" method="post" class="mt-3">
                                @csrf
                                <button type="button" class="btn btn-lg btn-success all-confirm-btn">
                                    <i class="fas fa-check-circle mr-2"></i> Confirm All
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @else
                @if(request()->has('user_id'))
                     <div class="alert alert-warning">No user found with the selected ID.</div>
                @else
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle mr-2"></i> Select an SR from the dropdown to load pending payments.
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown change event
    document.getElementById('user_id').addEventListener('change', function () {
        if (this.value) {
            this.form.submit();
        } else {
            window.location.href = "{{ route('srcustomersdues') }}";
        }
    });

    // SweetAlert for single confirm
    document.querySelectorAll('.confirm-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to confirm this payment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // SweetAlert for 'Confirm All'
    const confirmAllBtn = document.querySelector('.all-confirm-btn');
    if (confirmAllBtn) {
        confirmAllBtn.addEventListener('click', function () {
            const form = this.closest('form');
            Swal.fire({
                title: 'Confirm All Payments?',
                text: "This action will mark all pending payments for this SR as confirmed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm all!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
@stop