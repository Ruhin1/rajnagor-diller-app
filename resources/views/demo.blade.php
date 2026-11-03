@extends('adminlte::page')

@section('title', 'ডিলার বিক্রয়')

@section('content_header')
    <h1 style="color: green">ডিলার বিক্রয় </h1> 
@stop

@section('content')


@stop 

@section('css')
<!-- ToastMagic -->
{!! ToastMagic::styles() !!}
 <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@stop
 
@section('js')
<!-- ToastMagic -->
{!! ToastMagic::scripts() !!}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

  Swal.fire({
            title: 'ডিলার রেইটে পণ্য বিক্রয় বন্ধ করা হয়েছে',
            text: "আপনি ডিলার রেইটে পণ্য বিক্রয় করতে পারবেন না।",
            icon: 'error',
            showCancelButton: true,
            allowOutsideClick: false       // বাইরে ক্লিক করলে বন্ধ হবে না
        });
});
</script> 
@stop