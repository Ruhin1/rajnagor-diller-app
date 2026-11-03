<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ক্লিম করা কার্ডের রিপোর্ট প্রিন্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; }
        @media print {
            .no-print { display: none; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid py-3">
    <div class="text-center mb-3">
        <h2>ক্লিম করা কার্ডের রিপোর্ট</h2>
        <p class="m-0">মোট ক্লেইম হওয়া কার্ড: <strong>{{ number_format($totalCards) }} টি</strong> | মোট টাকা: <strong>{{ number_format($totalAwards, 2) }} ৳</strong></p>
        <small class="text-muted">প্রিন্ট তৈরির তারিখ: {{ date('d M, Y - h:i A') }}</small>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="thead-light text-center">
            <tr>
                <th style="width: 5%;">#</th>
                <th>কার্ড কোড</th>
                <th>প্রোডাক্টের নাম</th>
                <th>ইউজার (Used By)</th>
                <th>ক্লিম করার তারিখ</th>
                <th class="text-right">টাকা</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cards as $index => $card)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><code>{{ $card->code }}</code></td>
                    <td>{{ $card->product_name }}</td>
                    <td>{{ $card->user ? $card->user->name : $card->used_by }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($card->used_at)->format('d M, Y - h:i A') }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($card->awards, 2) }} ৳</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-3 text-danger">কোনো রেকর্ড পাওয়া যায়নি।</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">সর্বমোট:</th>
                <th class="text-right">{{ number_format($totalAwards, 2) }} ৳</th>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>