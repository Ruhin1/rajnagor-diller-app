@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container">
    <h2 class="mb-4">নতুন কার্ড তৈরী করুন</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {!! session('success') !!}
        </div>
    @endif

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('cards.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="product_name" class="form-label">পণ্যের নাম</label>
                   <select id="product_name" class="form-control @error('product_name') is-invalid @enderror" name="product_name" required>
                        <option value="রোস্ট ফ্রাই মসলা">রোস্ট ফ্রাই মসলা</option>
                        <option value="কাঠ বাদাম গুড়া">কাঠ বাদাম গুড়া</option>
                        <option value="মাছের রোস্ট ফ্রাই">মাছের রোস্ট ফ্রাই</option>
                        <option value="কাঠ বাদাম গুড়া">কাঠ বাদাম গুড়া</option>
                        <option value="টুয়েন্টি মসলা">টুয়েন্টি মসলা</option>
                        <option value="মেজবান মসলা">মেজবান মসলা</option>
                        <option value="কালাভুনা মশলা">কালাভুনা মশলা</option>
                        <option value="আলু-বোখড়া">আলু-বোখড়া</option>
                        <option value="বিরিয়ানী মশলা">বিরিয়ানী মশলা</option>
                        {{-- <option value="রোস্ট সস্">রোস্ট সস্</option>
                        <option value="বিরানী সস">বিরানী সস</option>
                        <option value="মিট সস">মিট সস</option>
                        <option value="ভেজিটেবল সস">ভেজিটেবল সস</option> --}}
                        <option value="তন্ময় ঘি">তন্ময় ঘি</option>
                        <option value="মালাই ক্রিম">মালাই ক্রিম</option>
                        <option value="মিট ক্রিম"> মিট ক্রিম</option>
                        <option value="বাটার অয়েল">বাটার অয়েল</option>
                        <option value="বাটার পোলাও">বাটার পোলাও</option>
                        <option value="তন্ময় মাওয়া">তন্ময় মাওয়া</option>
                        <option value="মিক্স মসলা">মিক্স মসলা</option>
                        <option value="কারি পাউডার">কারি পাউডার</option> 
                        <option value="দুঁ পেয়াজা মশলা">দুঁ পেয়াজা মশলা</option>  
                        <option value="পেস্তাবাদাম গুড়া (5)">পেস্তাবাদাম গুড়া (5)</option> 
                   </select> 
            @error('product_name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div> 

        <div class="mb-3">
            <label for="token" class="form-label">টোকেন</label>
            <input type="text" name="token" id="token" 
                   value="{{ old('token') }}" 
                   class="form-control @error('token') is-invalid @enderror" required>
            @error('token')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="card_amount" class="form-label">কার্ড সংখ্যা (সর্বচ্ছ 500 টি)</label>
            <input type="number" name="card_amount" id="card_amount" 
                   value="{{ old('card_amount') }}" 
                   class="form-control @error('card_amount') is-invalid @enderror" 
                   min="1" max="500" required>
            @error('card_amount')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">কার্ড তৈরী করুন</button>
    </form>
    <div class="row">
        {{-- Table --}}
    <table class="table table-bordered table-striped mt-5">
        <thead>
            <tr>
                <th>#</th>
                <th>পণ্যের নাম</th>
                <th>টোকেন</th>
                <th>এমাউন্ট</th>
                <th>পিন্ট</th>
                <th>Created At</th> 
            </tr>
        </thead>
        <tbody>
            @forelse($cards as $index => $card)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $card->product_name }}</td>
                    <td>{{ $card->token  }}</td>
                    <td>{{ $card->card_amount }}</td>
                    <td> 
                        <form action="{{ route('cards.print') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $card->token }}">
                            <input class="btn btn-primary" type="submit" value="Print">
                        </form>    
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($card->created_at)->format('d M, Y h:i A') }} <br/>
                        {{ \Carbon\Carbon::parse($card->created_at)->diffForHumans() }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">⚠️ কোনো কার্ড পাওয়া যায়নি</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $cards->links() }}
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
       let tokenInput = document.getElementById('token');

        document.getElementById('product_name').addEventListener('change', function() {
            const selectedProduct = this.value;

            const now = new Date();

            let day = String(now.getDate()).padStart(2, '0');
            let month = String(now.getMonth() + 1).padStart(2, '0');
            let year = now.getFullYear();

            let hours = now.getHours();
            let minutes = String(now.getMinutes()).padStart(2, '0');

            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 হলে 12 হবে

            let formattedDate = `${day}/${month}/${year} - ${hours}:${minutes} ${ampm}`;

            tokenInput.value = selectedProduct + '-' + formattedDate;
        });
         
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