@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        Sale #{{ $sale->id }} Details
        <button class="btn btn-danger btn-sm float-right" data-toggle="modal" data-target="#returnModal">
            Return Product
        </button>
    </div>
    <div class="card-body">
        <h5>Customer: {{ $sale->customer->name }}</h5>
        <h5>Date: {{ $sale->created_at->format('d M, Y') }}</h5>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>৳{{ $item->price }}</td>
                    <td>৳{{ $item->quantity * $item->price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Return Product Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('returns.store', $sale->id) }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="returnModalLabel">Return Product</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
                <div class="form-group">
                    <label for="product_id">Select Product</label>
                    <select name="product_id" class="form-control">
                        @foreach($sale->items as $item)
                            <option value="{{ $item->product_id }}">
                                {{ $item->product->name }} (Sold: {{ $item->quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Return Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label for="reason">Reason</label>
                    <textarea name="reason" class="form-control"></textarea>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger">Return</button>
          </div>
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

    </script> 
@stop

