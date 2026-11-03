public function dillerStatement(Dealer $customer)
    {
        // কাস্টমারের সব সেল (ইনভয়েস) লোড করা হচ্ছে
        $sales = $customer
            ->sales()
            ->orderBy("created_at", "asc")
            ->get();

        // কাস্টমারের সব আলাদা পেমেন্ট (বকেয়া পরিশোধ) - শুধুমাত্র প্রদর্শনের জন্য
        $payments = $customer
            ->payments()
            ->orderBy("created_at", "asc")
            ->get();

        // সেল এবং পেমেন্টকে একত্রিত করে তারিখ অনুযায়ী সাজানো - শুধুমাত্র প্রদর্শনের জন্য
        $transactions = $sales->concat($payments)->sortBy("created_at");

        // --- চূড়ান্ত, সরল এবং নির্ভুল হিসাব গণনা (শুধুমাত্র sales টেবিল থেকে) ---

        // ১. মোট কত টাকার পণ্য বিক্রি করা হয়েছে (সব ইনভয়েসের যোগফল)
        $totalSalesAmount = $customer->sales()->sum("total");

        // ২. মোট কত টাকা পাওয়া গেছে (সব ইনভয়েসের মোট 'paid' কলামের যোগফল)
        // এটিই সব পেমেন্টের চূড়ান্ত হিসাব, কারণ আপনার payDues ফাংশন এই কলাম আপডেট করে।
        $totalPaymentAmount = $customer->sales()->sum("paid");

        // ৩. বর্তমান বকেয়া (সব ইনভয়েসের মোট 'due' কলামের যোগফল)
        // এটি (মোট বিল - মোট জমা) এর সমান হবে।
        $currentDue = $customer->sales()->sum("due");

        return view(
            "sales.diller_statement",
            compact(
                "customer",
                "transactions",
                "totalSalesAmount",
                "totalPaymentAmount",
                "currentDue"
            )
        );
}

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বকেয়ার বিবরণী - {{ $customer->name }}</title>
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
        .customer-details strong { display: inline-block; width: 110px; }
        .transaction-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .transaction-table th, .transaction-table td { border: 1px solid #dee2e6; padding: 12px; font-size: 14px; }
        .transaction-table thead { background-color: #343a40; color: #ffffff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-section { display: flex; justify-content: flex-end; margin-top: 30px; }
        .summary-table { width: 50%; border-collapse: collapse; }
        .summary-table td { padding: 12px; font-size: 15px; border-bottom: 1px solid #dee2e6; }
        .summary-table tr.total-due td {
            font-size: 20px; font-weight: 600;
            background-color: #dc3545; color: #ffffff;
            border-top: 3px solid #b21f2d; border-radius: 0 0 8px 8px;
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

        @media print {
            body { background-color: #ffffff; padding: 0; }
            .statement-wrapper { margin: 0; box-shadow: none; border-radius: 0; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট করুন</button>
    <div class="statement-wrapper">
        <header class="header">
            <div class="logo">
                <img src="{{ asset('logo/gf-food.jpg') }}" alt="Company Logo">
            </div>
            <div class="company-info">
                <h1>GF Food and beverage LTD</h1>
                <p>ভানুগাছ বাজার, কমলগঞ্জ, মৌলভীবাজার</p>
                <p>ফোন: 01344-558778</p>
            </div>
        </header>

        <div class="statement-title">
            <h2>বকেয়ার বিবরণী (Statement of Account)</h2>
        </div>

        <div class="customer-details">
            <div>
                <p><strong>ডিলারের  নাম:</strong> {{ $customer->name }}</p>
                <p><strong>প্রতিষ্ঠানের নাম:</strong> {{ $customer->pname ?? 'N/A' }}</p>
                <p><strong>মোবাইল:</strong> {{ $customer->phone ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p><strong>বিবরণী তৈরির তারিখ:</strong> {{ date('d-m-Y') }}</p>
                <p><strong>ঠিকানা:</strong> {{ $customer->address ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- **সংশোধন:** টেবিলের গঠন ঠিক করা হয়েছে --}}
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>বিবরণ</th>
                    <th class="text-right">বিল/ইনভয়েস (৳)</th>
                    <th class="text-right">জমা (৳)</th>
                    <th class="text-right">অবশিষ্ট (৳)</th>
                </tr>
            </thead>
            <tbody>
    @php 
        $balance = 0; // চলমান ব্যালেন্স ০ থেকে শুরু হবে
    @endphp
    @forelse($transactions->sortBy('created_at') as $transaction)
        
        {{-- ====================================================== --}}
        {{--                যদি এটি একটি Sale (ইনভয়েস) হয়                --}}
        {{-- ====================================================== --}}
        @if ($transaction instanceof \App\Models\Sale)
            @php
                // একটি নতুন ইনভয়েস মানে দেনা বাড়লো
                $balance += $transaction->total; 
            @endphp
            <tr>
                <td>{{ $transaction->created_at->format('d-m-Y') }}</td>
                <td>ইনভয়েস #{{ $transaction->id }}</td>
                <td class="text-right">{{ number_format($transaction->total, 2) }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($balance, 2) }}</td>
            </tr>

            {{-- যদি এই ইনভয়েসের সাথে কোনো পেমেন্ট থাকে, তাহলে সেটি একটি নতুন সারি হিসেবে দেখানো হবে --}}
            @if ($transaction->paid > 0)
                @php
                    // পেমেন্টের কারণে দেনা কমলো
                    $balance -= $transaction->paid;
                @endphp
                <tr>
                    <td>{{ $transaction->created_at->format('d-m-Y') }}</td>
                    <td> <em>↪ ইনভয়েস #{{ $transaction->id }} এর বিপরীতে জমা</em></td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ number_format($transaction->paid, 2) }}</td>
                    <td class="text-right">{{ number_format($balance, 2) }}</td>
                </tr>
            @endif
        @endif

    @empty
        <tr>
            <td colspan="5" class="text-center">কোনো লেনদেন পাওয়া যায়নি।</td>
        </tr>
    @endforelse
</tbody>
        </table>

        <div class="summary-section">
            <table class="summary-table">
                <tbody>
                    <tr>
                        <td><strong>সর্বমোট বিক্রয়:</strong></td>
                        <td class="text-right">{{ number_format($totalSalesAmount, 2) }} ৳</td>
                    </tr>
                    <tr>
                        <td><strong>সর্বমোট জমা:</strong></td>
                        <td class="text-right">{{ number_format($totalPaymentAmount, 2) }} ৳</td>
                    </tr>
                    <tr class="total-due">
                        <td>বর্তমান বকেয়া:</td>
                        <td class="text-right">{{ number_format($currentDue, 2) }} ৳</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer class="footer">
            <p>এটি একটি কম্পিউটার-জেনারেটেড বিবরণী এবং কোনো স্বাক্ষরের প্রয়োজন নেই।</p>
        </footer>
    </div>
</body>
</html>