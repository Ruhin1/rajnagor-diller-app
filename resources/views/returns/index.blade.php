
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">All Returned Products</div>
    <div class="card-body">
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice ID</th>
            <th>Product</th>
            <th>Return Qty</th>
            <th>Reason</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($returns as $key=>$r)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>#INV-{{ $r->sale->id }}</td>
            <td>{{ $r->product->name }}</td>
            <td>{{ $r->quantity }}</td>
            <td>{{ $r->reason ?? '-' }}</td>
            <td>{{ $r->created_at->format('d-m-Y h:i A') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $returns->links() }}

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

