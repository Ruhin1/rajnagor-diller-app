@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

<form action="{{ route('acount.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- সব এরর একসাথে দেখাবে --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-group mt-2">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mt-2">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email')
            <span class="text-danger small">{{ $message }}</span>
        @enderror 
    </div>
    <div class="form-group mb-3">
   <label for="email">Add Role</label>
   <select class="form-control" name="role"  required>
      @switch($user->role)
      @case(1)
      <option selected value="1">Admin - (সম্পূর্ণ এপ্লিকেশনের এক্সেস)</option>
      @break
      @case(0)
      <option selected value="0">Normal Sr - (শুধু কাস্টমারের কাছে বিক্রয় করার এক্সেস)</option>
      @break
      @case(2)
      <option selected value="2">Full Sr - (কাস্টমার ও ডিলারের কাছে বিক্রয় করার এক্সেস)</option>
      @break
      @case(3)
      <option selected value="3">Card Checker - (শুধু মাত্র কার্ড চেক করার এক্সেস)</option>
      @break
      @endswitch
      <option  value="1">Admin - (সম্পূর্ণ এপ্লিকেশনের এক্সেস)</option>
      <option value="0">Normal Sr - (শুধু কাস্টমারের কাছে বিক্রয় করার এক্সেস)</option>
      <option value="2">Full Sr - (কাস্টমার ও ডিলারের কাছে বিক্রয় করার এক্সেস)</option>
      <option  value="3">Card Checker - (শুধু মাত্র কার্ড চেক করার এক্সেস)</option>
   </select>
   @error('role') <small class="text-danger">{{ $message }}</small> @enderror
</div>

    <div class="form-group mt-2">
        <label>Password (optional)</label>
        <input type="password" name="password" 
               class="form-control @error('password') is-invalid @enderror">
        @error('password')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mt-2">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <button type="submit" class="btn btn-block btn-success mt-4">Update</button>
</form>



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
    
    </script> 
@stop
