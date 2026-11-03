
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">Return Products for Sale #{{ $sale->id }}</div>
    <div class="card-body">
        <form action="{{ route('returns.store', $sale->id) }}" method="POST">
            @csrf

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold Qty</th>
                        <th>Return Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Updated Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            <input type="number" 
                                   name="returns[{{ $item->id }}]" 
                                   value="0" min="0" max="{{ $item->quantity }}" 
                                   class="form-control return-input">
                        </td>
                        <td>{{ $item->price }}</td>
                        <td>{{ $item->total }}</td>
                        <td class="updated-total">{{ $item->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h5>Adjustments</h5>
            <div class="row">
                <div class="col-md-3">
                    <label>Comation</label>
                    <input type="number" name="discount" value="{{ $sale->discount }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="tax" value="{{ $sale->tax }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="shipping" value="{{ $sale->shipping }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="paid" value="{{ $sale->paid }}" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-danger">Confirm Return</button>
            </div>
        </form>
    </div>
</div>



@stop

@section('css')
<!-- ToastMagic -->
{!! ToastMagic::styles() !!}
 <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">



@stop
 
@section('js')
<!-- ToastMagic -->
{!! ToastMagic::scripts() !!}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const toastMagic = new ToastMagic();
        // toastMagic.success("Success!", "Your data has been saved!");


        
        function showAlert() {
                Swal.fire({
                title: 'Error!',
                text: 'Something went wrong. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK' 
             });
        }

        function confrims(){
                        Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire(
                                'Deleted!',
                                'Your file has been deleted.',
                                'success'
                            )
                        }
                    });

                }

     document.querySelectorAll('.return-input').forEach(input => {
        input.addEventListener('input', function() {
            let row = this.closest('tr');
            let soldTotal = parseFloat(row.querySelector('td:nth-child(5)').innerText);
            let price = parseFloat(row.querySelector('td:nth-child(4)').innerText);
            let soldQty = parseInt(row.querySelector('td:nth-child(2)').innerText);
            let returnQty = parseInt(this.value) || 0;

            let newTotal = (soldQty - returnQty) * price;
            row.querySelector('.updated-total').innerText = newTotal;
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('.return-qty');
    const subtotalEl = document.getElementById('new-subtotal');
    const totalEl = document.getElementById('new-total');
    const dueEl = document.getElementById('new-due');

    // old values from server
    const oldDiscount = {{ $sale->discount }};
    const oldTax = {{ $sale->tax }};
    const oldShipping = {{ $sale->shipping }};
    const oldPaid = {{ $sale->paid }};

    function recalc() {
        let newSubtotal = 0;

        inputs.forEach(input => {
            let price = parseFloat(input.dataset.price);
            let soldQty = parseInt(input.dataset.qty);
            let returnQty = parseInt(input.value) || 0;
            let remainQty = soldQty - returnQty;

            let remainTotal = remainQty * price;
            newSubtotal += remainTotal;

            // update remaining column
            input.closest('tr').querySelector('.remaining').innerText = remainTotal;
        });

        let newTotal = (newSubtotal + oldTax + oldShipping) - oldDiscount;
        let newDue = newTotal - oldPaid;

        subtotalEl.innerText = newSubtotal.toFixed(2);
        totalEl.innerText = newTotal.toFixed(2);
        dueEl.innerText = newDue.toFixed(2);
    }

    inputs.forEach(input => {
        input.addEventListener('input', recalc);
    });
});

    </script> 
@stop

