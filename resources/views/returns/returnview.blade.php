@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">ইনভয়েস লিস্ট</h3>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ইনভয়েস নং</th>
                    <th>কাস্টমার</th>
                    <th>টোটাল</th>
                    <th>তারিখ</th>
                    <th>একশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoise as $sale)
                    <tr>
                        <td>{{ $invoise->firstItem() + $loop->index }}</td>
                        <td>{{ $sale->id}}</td>
                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td>৳{{ number_format($sale->total, 2) }}</td>
                        <td>{{ $sale->created_at->format('d M Y h:i A') }}</td>
                        <td>
                            <form action="{{ route('sale.return') }}" method="POST">
                                @csrf
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <input class="btn btn-success" type="submit" value="রিটার্ন করুন">
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">কোনো ইনভয়েস পাওয়া যায়নি</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{-- পেজিনেশন --}}
        {{ $invoise->links() }}
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

