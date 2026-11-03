<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-{{ $sale->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* ... আপনার সম্পূর্ণ CSS কোড এখানে অপরিবর্তিত থাকবে ... */
        @page { size: A4; margin: 0; }
        body { font-family: 'Roboto', sans-serif; margin: 0; padding: 0; background-color: #e9ecef; -webkit-print-color-adjust: exact; color-adjust: exact; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .invoice-wrapper { width: 210mm; min-height: 297mm; padding: 15mm; margin: 20px auto; background: #ffffff; box-shadow: 0 0 25px rgba(0, 0, 0, 0.1); box-sizing: border-box; position: relative; }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-header .logo img { max-height: 70px; }
        .invoice-header .company-info { text-align: right; }
        .invoice-header .company-info h1 { margin: 0; font-size: 24px; font-weight: 700; color: #0d6efd; }
        .invoice-header .company-info p { margin: 2px 0; font-size: 13px; color: #555; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 14px; }
        .invoice-details .bill-to { background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; border-radius: 5px; min-width: 50%; }
        .invoice-details .bill-to h5 { margin-top: 0; margin-bottom: 10px; font-weight: 700; }
        .invoice-details p { margin: 4px 0; line-height: 1.6; }
        .invoice-details .invoice-meta { text-align: right; }
        .invoice-meta p { margin: 4px 0; }
        .invoice-meta b { display: inline-block; width: 120px; text-align: left; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .invoice-table th, .invoice-table td { border: 1px solid #dee2e6; padding: 10px; text-align: left; font-size: 13px; }
        .invoice-table thead { background-color: #0d6efd; color: #ffffff; }
        .invoice-table thead th { font-weight: 500; }
        .invoice-table tbody tr:nth-child(odd) { background-color: #f8f9fa; }
        .invoice-table .text-right { text-align: right; }
        .invoice-summary { display: flex; justify-content: flex-end; margin-top: 25px; }
        .invoice-summary .summary-table { width: 50%; font-size: 14px; }
        .invoice-summary .summary-table td { padding: 8px 10px; }
        .invoice-summary .summary-table tr.grand-total td { font-size: 18px; font-weight: 700; background-color: #0d6efd; color: #ffffff; border-top: 3px solid #0056b3; }
        .invoice-footer { position: absolute; bottom: 15mm; left: 15mm; right: 15mm; text-align: center; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 12px; color: #888; }
        .invoice-footer .signatures { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 0 50px; }
        .signatures div { padding-top: 40px; border-top: 1px solid #555; width: 180px; text-align: center; font-weight: 500; }
         /* *** নতুন: ইনভয়েস টোটাল হাইলাইট করার জন্য *** */
    .summary-table .invoice-total-highlight td {
        background-color: #e7f1ff; /* হালকা নীল ব্যাকগ্রাউন্ড */
        color: #0056b3; /* গাঢ় নীল টেক্সট */
        border-top: 1px solid #b8d6fb;
        border-bottom: 1px solid #b8d6fb;
        font-weight: bold;
    }
        @media print { body { background-color: #ffffff; display: block; } .invoice-wrapper { margin: 0; padding: 10mm; box-shadow: none; width: 100%; min-height: 100%; } .invoice-footer { position: static; margin-top: 40px; } }
    </style>
</head>
<body>
    @php
        $domin = url('/');  
    @endphp
    <div class="invoice-wrapper">
        <header class="invoice-header">
            <div class="logo">
                <img src="{{ asset('logo/gf-food.jpg') }}" alt="Company Logo">
            </div>
            <div class="company-info">
                <h1>GF Food & Beverage Ltd.</h1>
                <p>
                    @if ($domin == 'https://app.gffoodandbeverage.com')
                        Bhanugach Bazar, Kamalganj.
                    @else
                        Chadnighat, Moulvibazar.
                    @endif
                </p>
                <p>
                    @if ($domin == 'https://app.gffoodandbeverage.com')
                        Phone: 01344-558778
                    @else
                        Phone: 01756697683
                    @endif
                </p>
            </div>
        </header>

        <section class="invoice-details">
            <div class="bill-to">
                <h5>Bill To:</h5>
                
                {{-- *** মূল পরিবর্তনটি এখানে করা হয়েছে *** --}}
                @if ($sale->customer)
                    {{-- যদি নিবন্ধিত কাস্টমার হয় --}}
                    <p>
                        <strong>{{ $sale->customer->shop_name }}</strong><br>
                        Owner: {{ $sale->customer->name }}<br>
                        {{ $sale->customer->shop_address }}<br>
                        Mobile: {{ $sale->customer->number }}
                    </p>
                @else
                    {{-- যদি গেস্ট কাস্টমার হয় --}}
                    <p>
                        <strong>{{ $sale->guest_name }}</strong><br>
                        @if ($sale->guest_phone)
                            Mobile: {{ $sale->guest_phone }}<br>
                        @endif
                        (Guest Customer)
                    </p>
                @endif
            </div>

            <div class="invoice-meta">
                <p><b>Invoice No:</b> #INV-{{ $sale->id }}</p>
                <p><b>SR Name:</b> {{ $sale->srname }}</p>
                <p><b>Order Date:</b> {{ $sale->created_at->format('d M, Y') }}</p>
                {{-- ডেলিভারি তারিখ null হতে পারে কিনা তা চেক করা --}}
                @if ($sale->ddate)
                    <p><b>Delivery Date:</b> {{ \Carbon\Carbon::parse($sale->ddate)->format('d M, Y') }}</p>
                @endif
            </div>
        </section>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th style="width: 40%;">Product Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <section class="invoice-summary">
    <table class="summary-table">
        <tbody>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Commission (-):</td>
                <td class="text-right">{{ number_format($sale->discount, 2) }}</td>
            </tr>
            <tr>
                <td>Other Cost (+):</td>
                <td class="text-right">{{ number_format($sale->shipping, 2) }}</td>
            </tr>
            {{-- *** নতুন: এই সারিটিকে হাইলাইট করা হবে *** --}}
            <tr class="invoice-total-highlight">
                <td><strong>Invoice Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($sale->total, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Paid Amount:</td>
                <td class="text-right">{{ number_format($sale->paid, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Invoice Due:</strong></td>
                <td class="text-right"><strong>{{ number_format($sale->due, 2) }}</strong></td>
            </tr>

            @if (isset($totalCustomerDue))
                @php
                    $previousDue = $totalCustomerDue - $sale->due;
                @endphp
                
                @if ($previousDue > 0)
                    <tr style="border-top: 2px solid #6c757d;">
                        {{-- *** নতুন: বকেয়া ইনভয়েসের সংখ্যা দেখানো হচ্ছে *** --}}
                        <td>Previous Due ({{ $dueInvoiceCount - 1 > 0 ? $dueInvoiceCount - 1 : 0 }} Invoices):</td>
                        <td class="text-right">{{ number_format($previousDue, 2) }}</td>
                    </tr>
                @endif
                
                <tr class="grand-total">
                    <td>Total Due:</td>
                    <td class="text-right">{{ number_format($totalCustomerDue, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</section>

        <footer class="invoice-footer">
            <div class="signatures">
                <div>Authorized Signature</div>
                <div>Customer Signature</div>
            </div>
            <p>Thank you for your business!</p>
        </footer>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script> 
</body>
</html> 