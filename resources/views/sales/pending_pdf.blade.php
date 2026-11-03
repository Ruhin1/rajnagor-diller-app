{{-- resources/views/sales/pending_pdf.blade.php --}}
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>পেন্ডিং অর্ডার রিপোর্ট</title>
    <style>
    /* এই অংশটি প্রতিস্থাপন করুন */
    * {
        font-family: 'kalpurush', sans-serif;
    }
    body { 
        font-size: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 5px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    .header, .footer { 
        text-align: center;
        width: 100%;
    }
    .header {
        margin-bottom: 20px;
    }
    .header h2 {
        margin: 0;
        padding: 0;
    }
    .total-row td {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>
</head>
<body>
    <div class="header">
        <h2>পেন্ডিং অর্ডার রিপোর্ট</h2>
        <p>
            @if($filterInfo['from'] && $filterInfo['to'])
                তারিখ: <strong>{{ date('d-m-Y', strtotime($filterInfo['from'])) }}</strong> থেকে <strong>{{ date('d-m-Y', strtotime($filterInfo['to'])) }}</strong>
            @elseif($filterInfo['filter'] == 'today')
                রিপোর্ট: <strong>আজকের</strong>
            @elseif($filterInfo['filter'] == 'this_week')
                রিপোর্ট: <strong>এই সপ্তাহের</strong>
            @elseif($filterInfo['filter'] == 'this_month')
                রিপোর্ট: <strong>এই মাসের</strong>
            @else
                রিপোর্ট: <strong>সকল পেন্ডিং অর্ডার</strong>
            @endif
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#আইডি</th>
                <th>কাস্টমার/ডিলার</th>
                <th>মোবাইল</th>
                <th>ঠিকানা</th>
                <th>দোকান/প্রতিষ্টান</th>
                <th>জমা</th>
                <th>বাকি</th>
                <th>মোট</th>
                <th>তারিখ</th>
                <th>ধরণ</th>
                <th>এসআর</th> 
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->customer->name ?? $sale->dealer->name ?? 'N/A' }}</td>
                    <td>{{ $sale->customer->number ?? $sale->dealer->phone ?? 'N/A' }}</td>
                    <td>{{ $sale->customer->shop_address ?? $sale->dealer->address ?? 'N/A' }}</td>
                    <td>{{$sale->customer->shop_name ?? $sale->dealer->pname }}</td>
                    <td>{{ number_format($sale->paid, 2) }}</td>
                    <td>{{ number_format($sale->due, 2) }}</td>
                    <td>{{ number_format($sale->total, 2) }}</td>
                    <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                    <td>{{ $sale->customer_id ? 'কাস্টমার' : 'ডিলার' }}</td>
                    <td>{{ $sale->srname }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">কোনো ডেটা পাওয়া যায়নি</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>সর্বমোট:</strong></td>
                <td><strong>{{ number_format($totals['paid'], 2) }}</strong></td>
                <td><strong>{{ number_format($totals['due'], 2) }}</strong></td>
                <td colspan="4"><strong>{{ number_format($totals['total'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>রিপোর্ট তৈরির সময়: {{ date('d-m-Y h:i A') }}</p>
    </div>
</body>
</html>