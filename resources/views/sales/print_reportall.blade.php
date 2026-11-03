<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>সেলস রিপোর্ট প্রিন্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            @page { margin: 10mm; size: landscape; }
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000 !important;
            padding: 5px !important;
        }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid py-3">
    <!-- Header Summary -->
    <div class="text-center mb-3">
        <h2>সেলস রিপোর্ট</h2>
        <p class="m-0">
            মোট মেমো: <strong>{{ number_format($totalSalesCount) }} টি</strong> | 
            মোট বিক্রি: <strong>{{ number_format($totalAmount, 2) }} ৳</strong> | 
            মোট জমা: <strong>{{ number_format($totalPaid, 2) }} ৳</strong> | 
            মোট বকেয়া: <strong>{{ number_format($totalDue, 2) }} ৳</strong>
        </p>
        <small class="text-muted">প্রিন্ট তৈরির তারিখ: {{ date('d M, Y - h:i A') }}</small>
    </div>

    <!-- Data Table -->
    <table class="table table-bordered table-sm align-middle">
        <thead class="thead-light text-center">
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 11%;">তারিখ</th>
                <th>ইউজার (SR)</th>
                <th>গ্রাহকের বিবরণ</th>
                <th>সেলস টাইপ</th>
                <th class="text-right">মোট টাকা</th>
                <th class="text-right">জমা</th>
                <th class="text-right">বকেয়া</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $sale->ddate ? $sale->ddate->format('d M, Y') : (\Carbon\Carbon::parse($sale->created_at)->format('d M, Y')) }}</td>
                    <td>{{ $sale->usersr ? $sale->usersr->name : ($sale->srname ?? 'N/A') }}</td>
                    <td>
                        @if($sale->customer)
                            {{ $sale->customer->name }} (টিপি)
                        @elseif($sale->dealer)
                            {{ $sale->dealer->name }} (ডিলার)
                        @elseif($sale->guest_name)
                            {{ $sale->guest_name }} (গেস্ট)
                        @else
                            সাধারণ ক্রেতা
                        @endif
                    </td>
                    <td class="text-center">
                        @if($sale->guest_sell_type == 1)
                            টিপি গেস্ট সেলস
                        @elseif($sale->guest_sell_type == 2)
                            ডিপি গেস্ট সেলস
                        @elseif($sale->is_dipu == 1)
                            ডিপু সেলস
                        @elseif(!empty($sale->customer_id))
                            টিপি সেলস
                        @elseif(!empty($sale->dealer_id))
                            ডিপি সেলস
                        @else
                            সাধারণ সেলস
                        @endif
                    </td>
                    <td class="text-right font-weight-bold">{{ number_format($sale->total, 2) }} ৳</td>
                    <td class="text-right text-success">{{ number_format($sale->paid, 2) }} ৳</td>
                    <td class="text-right text-danger">{{ number_format($sale->due, 2) }} ৳</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3 text-danger">কোনো রেকর্ড পাওয়া যায়নি।</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="thead-light">
                <th colspan="5" class="text-right">সর্বমোট:</th>
                <th class="text-right">{{ number_format($totalAmount, 2) }} ৳</th>
                <th class="text-right text-success">{{ number_format($totalPaid, 2) }} ৳</th>
                <th class="text-right text-danger">{{ number_format($totalDue, 2) }} ৳</th>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>