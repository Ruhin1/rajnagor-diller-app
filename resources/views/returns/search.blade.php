
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h4>রিটার্ন করতে ইনভয়েস নাম্বার দিয়ে সার্চ করোন</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('returns.search.update') }}" method="GET" class="row">
            <div class="col-md-6">
                <input type="number" name="sale_id" class="form-control" placeholder="Enter Sale ID" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Search</button>
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

        // Laravel session থেকে JS variable তৈরি
    let errorMessage = @json(session('error'));

    // যদি session এ error থাকে, SweetAlert দেখাও
    if (errorMessage) {
        Swal.fire({
            title: 'Error!',
            text: errorMessage,
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

