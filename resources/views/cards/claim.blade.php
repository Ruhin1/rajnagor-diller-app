@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">ইউজার পেমেন্ট ক্লেইম</div>
                <div class="card-body">

                    <!-- ===== সফল বা ব্যর্থ হওয়ার মেসেজ দেখানোর জন্য ===== -->
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- ===== সার্চ ফর্ম ===== -->
                    <form action="{{ route('payment.claim.show') }}" method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="number" name="user_id" class="form-control" placeholder="ইউজার আইডি দিয়ে সার্চ করুন..." value="{{ request('user_id') }}" required>
                            <button class="btn btn-primary" type="submit">সার্চ করুন</button>
                        </div>
                    </form>

                    <!-- ===== সার্চের ফলাফল দেখানোর অংশ ===== -->
                    @if ($user)
                        <hr>
                        <h4>ইউজার: {{ $user->name }} (ID: {{ $user->id }})</h4>

                        @if($claimableCards->isNotEmpty()) 
                            <div class="alert alert-info">
                                আপনি এই <strong>{{ $claimableCards->count() }}</strong> টি কার্ড ক্লেইম করেছেন। আপনি <strong>{{ $claimableCards->sum('awards') }}</strong>  টাকা পাবেন।
                            </div>

                            {{-- ক্লেইম করা কার্ডের তালিকা --}}
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>কার্ড আইডি</th>
                                        <th>পণ্যের নাম</th>
                                        <th>বাবুর্চির নাম / ব্যাক্তিগত</th>
                                        <th>ক্লেইমের তারিখ ও সময়</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($claimableCards as $card)
                                        <tr>
                                            <td>{{ $card->id }}</td>
                                            <td>{{ $card->product_name }}</td>
                                            <td>{{ $card->customer_name }}</td>
                                            {{-- used_at যদি কার্বন অবজেক্ট না হয় তাহলে ->format() কাজ করবে না --}}
                                            <td>{{ \Carbon\Carbon::parse($card->used_at)->format('d M, Y h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- ===== কনফার্ম বাটন ===== -->
                            <form action="{{ route('payment.claim.confirm', $user->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে পেমেন্ট কনফার্ম করতে চান?');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">কনফার্ম করুন</button>
                            </form>
                        @else
                            <div class="alert alert-warning">এই ইউজারের কোনো ক্লেইম করার মতো কার্ড নেই।</div>
                        @endif

                    @elseif(request('user_id'))
                        <div class="alert alert-danger">এই আইডিতে কোনো ইউজার খুঁজে পাওয়া যায়নি।</div>
                    @endif
                </div>
            </div>
        </div>
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






