@extends('adminlte::page')
@section('title', 'স্টক এন্ট্রির ইতিহাস')

@section('content')
<div class="card mt-3 shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">স্টক এন্ট্রির ইতিহাস</h3>
        <button class="btn btn-info btn-sm print-hide" onclick="window.print()"><i class="fas fa-print mr-1"></i> প্রিন্ট করুন</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>তারিখ ও সময়</th>
                        <th>পণ্যের নাম</th>
                        <th class="text-right">যোগ করা পরিমাণ</th>
                        <th>এন্ট্রির ধরণ</th>
                        <th>এন্ট্রি করেছেন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entries->firstItem() + $loop->index }}</td>
                        <td>{{ $entry->created_at->format('d M, Y h:i A') }}</td>
                        <td>{{ $entry->product->name ?? 'N/A' }}</td>
                        <td class="text-right font-weight-bold">
                            @php
                                $kg = floor($entry->quantity_added_in_grams / 1000);
                                $gram = $entry->quantity_added_in_grams % 1000;
                            @endphp
                            @if($kg > 0) {{ $kg }} কেজি @endif
                            @if($gram > 0) {{ $gram }} গ্রাম @endif
                        </td>
                        <td>
                            @if($entry->type == 'initial_stock')
                                <span class="badge badge-primary">প্রাথমিক স্টক</span>
                            @else
                                <span class="badge badge-success">নতুন স্টক</span>
                            @endif
                        </td>
                        <td>{{ $entry->user->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">কোনো স্টক এন্ট্রির ইতিহাস পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer print-hide">
        {{ $entries->links() }}
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .print-hide, .main-header, .main-sidebar, .main-footer, .content-header { display: none !important; }
        .content-wrapper, .content, .card { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
    }
</style>
@stop