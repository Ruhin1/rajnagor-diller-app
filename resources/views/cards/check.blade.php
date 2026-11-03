
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h3 class="mb-3">🎟 কার্ড ভ্যালিডেশন</h3>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('cards.check') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">কার্ড কোড লিখুন</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">চেক করুন</button>
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

    </script> 
@stop