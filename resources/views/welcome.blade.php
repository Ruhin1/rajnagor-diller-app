@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<button onclick="showAlert()">Click Me</button> 

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

    </script> 
@stop