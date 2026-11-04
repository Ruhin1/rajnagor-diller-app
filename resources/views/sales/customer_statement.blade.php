<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বকেয়ার বিবরণী - {{ $customer->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @page { size: A4; margin: 0; }
        body {
            font-family: 'Hind Siliguri', sans-serif;
            margin: 0; padding: 20px 0;
            background-color: #f4f5f7;
            display: flex; justify-content: center; align-items: flex-start;
            -webkit-print-color-adjust: exact; color-adjust: exact;
        }
        .statement-wrapper {
            width: 210mm; max-width: 95%; min-height: 297mm;
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            box-sizing: border-box; padding: 15mm; border-radius: 10px;
        }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 25px;
        }
        .header .logo img { max-height: 70px; }
        .header .company-info h1 { margin: 0; font-size: 24px; font-weight: 600; color: #0056b3; text-align: right; }
        .header .company-info p { margin: 2px 0; font-size: 13px; color: #555; text-align: right; }
        .statement-title { text-align: center; margin-bottom: 25px; }
        .statement-title h2 {
            display: inline-block; background: #007bff; color: #fff;
            padding: 10px 30px; border-radius: 50px;
            font-size: 20px; font-weight: 500; margin: 0; letter-spacing: 1px;
        }
        .customer-details {
            display: flex; justify-content: space-between;
            background: #f8f9fa; border: 1px solid #e3e6ea;
            padding: 20px; border-radius: 8px; margin-bottom: 30px;
        }
        .customer-details p { margin: 4px 0; font-size: 14px; line-height: 1.6; }
        .customer-details strong { display: inline-block; min-width: 110px; }
        .transaction-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .transaction-table th, .transaction-table td { border: 1px solid #dee2e6; padding: 12px; font-size: 14px; }
        .transaction-table thead { background-color: #343a40; color: #ffffff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .text-success { color: #28a745; font-weight: bold; }
        .summary-section { display: flex; justify-content: flex-end; margin-top: 30px; }
        .summary-table { width: 50%; border-collapse: collapse; }
        .summary-table td { padding: 12px; font-size: 15px; border-bottom: 1px solid #dee2e6; }
        .summary-table tr:last-child {
            background-color: #f8f9fa;
        }
        .summary-table tr.total-due td {
            font-size: 20px; font-weight: 600;
            background-color: #dc3545; color: #ffffff;
            border-top: 3px solid #b21f2d;
        }
        .footer {
            text-align: center; margin-top: 50px; padding-top: 20px;
            border-top: 1px solid #dee2e6; font-size: 12px; color: #888;
        }
        .print-button {
            position: fixed; top: 20px; right: 20px;
            background: #007bff; color: white; padding: 10px 20px;
            border: none; border-radius: 5px; cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); z-index: 1000;
            font-family: 'Hind Siliguri', sans-serif; font-size: 14px;
        }
        .print-button i { margin-right: 8px; }
        .sale-row { background-color: #ffffff; }
        .payment-row { background-color: #eaf7e9; }

        @media print {
            body { background-color: #ffffff; padding: 0; }
            .statement-wrapper { margin: 0; box-shadow: none; border-radius: 0; width: 100%; max-width: 100%; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট করুন</button>
    <div class="statement-wrapper">
        <header class="header">
            <div class="logo">
                <img src="{{ asset('logo/gf-food2.png') }}" alt="Company Logo">
            </div>
            @php
                $domin = url('/');  
            @endphp
            <div class="company-info">
                <h1>বদরুল এন্টারপ্রাইজ </h1>
                <p>
                    ইউপি রোড রাজনগর
                </p>
                <p>
                     +880 1709090957
                </p>
            </div>
        </header>

        <div class="statement-title">
            <h2>বকেয়ার বিবরণী (Statement of Account)</h2>
        </div>

        <div class="customer-details">
            <div>
                <p><strong>কাস্টমারের নাম:</strong> {{ $customer->name }}</p>
                <p><strong>দোকানের নাম:</strong> {{ $customer->shop_name ?? 'N/A' }}</p>
                <p><strong>মোবাইল:</strong> {{ $customer->number ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p><strong>বিবরণী তৈরির তারিখ:</strong> {{ date('d-m-Y') }}</p>
                <p><strong>ঠিকানা:</strong> {{ $customer->shop_address ?? 'N/A' }}</p>
            </div>
        </div>

        <table class="transaction-table">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>বিবরণ</th>
                    <th class="text-right">বিল/দেনা (৳)</th>
                    <th class="text-right">জমা (৳)</th>
                    <th class="text-right">অবশিষ্ট (৳)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $balance = 0;
                @endphp
                @forelse($transactions as $transaction)
                    
                    @if ($transaction['type'] === 'sale')
                        @php
                            $sale = $transaction['data'];
                            $balance += $sale->total;
                        @endphp
                        <tr class="sale-row">
                            <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                            <td>ইনভয়েস #{{ $sale->id }}</td>
                            <td class="text-right">{{ number_format($sale->total, 2) }}</td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right">{{ number_format($balance, 2) }}</td>
                        </tr>

                    @elseif ($transaction['type'] === 'payment')
                        @php
                            $payment = $transaction['data'];
                            $balance -= $payment->amount;
                        @endphp
                        <tr class="payment-row">
                            <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d-m-Y') }}</td>
                            <td><em>বকেয়া পরিশোধ ({{ $payment->method ?? 'unknown' }})</em></td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right text-success">{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-right">{{ number_format($balance, 2) }}</td>
                        </tr>
                    @endif

                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">এই কাস্টমারের সাথে কোনো লেনদেন পাওয়া যায়নি।</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary-section">
            <table class="summary-table">
                <tbody>
                    <tr>
                        <td><strong>সর্বমোট বিক্রয়:</strong></td>
                        <td class="text-right">{{ number_format($totalSalesAmount, 2) }} ৳</td>
                    </tr>
                    <tr>
                        <td><strong>সর্বমোট জমা:</strong></td>
                        <td class="text-right">{{ number_format($totalPaymentAmount, 2) }} ৳</td>
                    </tr>
                    <tr class="total-due">
                        <td>বর্তমান বকেয়া:</td>
                        <td class="text-right">{{ number_format($currentDue, 2) }} ৳</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer class="footer">
            <p>এটি একটি কম্পিউটার-জেনারেটেড বিবরণী এবং কোনো স্বাক্ষরের প্রয়োজন নেই।</p>
        </footer>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>