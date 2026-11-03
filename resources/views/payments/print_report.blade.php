<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>পেমেন্ট রিপোর্ট প্রিন্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'SolaimanLipi', sans-serif, Arial; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid py-3">
    <div class="text-center mb-3">
        <h3>পেমেন্ট রিপোর্ট</h3>
        <p class="m-0">মোট ট্রানজেকশন: <strong>{{ number_format($totalPaymentCount) }} টি</strong> | মোট কালেকশন: <strong>৳ {{ number_format($totalAmount, 2) }}</strong></p>
        <small class="text-muted">প্রিন্টের তারিখ: {{ date('d-M-Y h:i A') }}</small>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light text-center">
            <tr>
                <th>#</th>
                <th>তারিখ</th>
                <th>কাস্টমার / ডিলার / ডিপু</th>
                <th>মেথড</th>
                <th>রেফারেন্স</th>
                <th>SR / ইউজার</th>
                <th class="text-end">পরিমাণ (টাকা)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $index => $payment)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d-M-Y h:i A') }}</td>
                    <td>
                        @if($payment->customer_id && $payment->is_dipo == 1)
                            [ডিপু] {{ $payment->customer->name ?? 'N/A' }}
                        @elseif($payment->dealer_id)
                            [ডিলার] {{ $payment->dealer->name ?? 'N/A' }}
                        @elseif($payment->customer_id)
                            [কাস্টমার] {{ $payment->customer->name ?? 'N/A' }}
                        @else
                            গেস্ট / অন্যান্য
                        @endif
                    </td>
                    <td class="text-center">{{ ucfirst($payment->method ?? 'Cash') }}</td>
                    <td>{{ $payment->reference ?? '-' }}</td>
                    <td>{{ $payment->srname ?? $payment->user->name ?? '-' }}</td>
                    <td class="text-end fw-bold">৳ {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-3">কোনো রেকর্ড পাওয়া যায়নি।</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">সর্বমোট:</th>
                <th class="text-end">৳ {{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>