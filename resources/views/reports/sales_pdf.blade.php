<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'sales' ? 'বিক্রয় রিপোর্ট' : 'অর্ডার রিপোর্ট' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box; 
        }

        body {
            font-family: 'SolaimanLipi', 'Kalpurush', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f4f6f9;
            padding: 20px;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* ===== Header ===== */
        .report-header {
            background: linear-gradient(135deg, #1a3c5e 0%, #2d6a9f 100%);
            color: #fff;
            padding: 28px 36px 22px;
            text-align: center;
        }

        .company-name {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .zone-name {
            font-size: 15px;
            opacity: 0.88;
            margin-bottom: 10px;
        }

        .report-title {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 20px;
            padding: 4px 20px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .date-range {
            margin-top: 10px;
            font-size: 13px;
            opacity: 0.82;
        }

        /* ===== Print Button ===== */
        .print-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 14px 28px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-print {
            background: #2d6a9f;
            color: #fff;
            border: none;
            padding: 9px 24px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: background 0.2s;
        }

        .btn-print:hover { background: #1a3c5e; }

        .btn-print svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        /* ===== Table ===== */
        .table-wrapper {
            padding: 24px 28px 32px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead tr {
            background: #1a3c5e;
            color: #fff;
        }

        thead th {
            padding: 11px 14px;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #e8edf2;
        }

        tbody tr:nth-child(even) { background: #f7f9fc; }
        tbody tr:hover            { background: #eef4fb; }

        tbody td {
            padding: 10px 14px;
            vertical-align: middle;
        }

        /* Type badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-customer { background: #dbeafe; color: #1e40af; }
        .badge-dealer   { background: #d1fae5; color: #065f46; }
        .badge-guest    { background: #fef3c7; color: #92400e; }

        /*
         * Summary row — <tfoot> বাদ দিয়ে tbody-র শেষ <tr> হিসেবে রাখা হয়েছে।
         * এতে print-এ শুধু একবার, সবার শেষে দেখাবে। repeat হবে না।
         */
        .summary-row {
            background: #1a3c5e !important;
            color: #fff !important;
            font-weight: 700;
        }

        .summary-row td {
            padding: 12px 14px;
            white-space: nowrap;
            border-top: 2px solid #0f2540;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .amount      { font-variant-numeric: tabular-nums; }

        /* Empty state */
        .empty-row td {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-style: italic;
        }

        /* ===== Print styles ===== */
        @media print {
            body {
                background: #fff;
                padding: 0;
                font-size: 11px;
            }

            .page-wrapper {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            /* প্রিন্ট বাটন লুকাও */
            .print-bar { display: none !important; }

            /* Header gradient রং ঠিক রাখো */
            .report-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /*
             * thead → display: table-row-group করলে
             * browser প্রতি পেজে repeat করবে না।
             * (table-header-group হলে repeat হয়)
             */
            thead {
                display: table-row-group;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead tr {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tbody tr:nth-child(even) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-row {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table { page-break-inside: auto; }
            tr    { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>

@php
    $host        = request()->getHost();
    $isKomalganj = str_contains($host, 'app.gffoodandbeverage.com');
    $zoneName    = $isKomalganj ? 'কমলগঞ্জ জোন' : 'মৌলভীবাজার জোন';
    $reportLabel = $type === 'sales' ? 'বিক্রয় রিপোর্ট' : 'অর্ডার রিপোর্ট';

    $totalInvoices = $sales->count();
    $totalAmount   = $sales->sum('total');
    $totalPaid     = $sales->sum('paid');
    $totalDue      = $sales->sum('due');

    $guestCount    = 0;
    $dealerCount   = 0;
    $customerCount = 0;

    foreach ($sales as $sale) {
        if ($sale->dealer)       { $dealerCount++; }
        elseif ($sale->customer) { $customerCount++; }
        else                     { $guestCount++; }
    }
@endphp

<div class="page-wrapper">

    {{-- ===== Header ===== --}}
    <div class="report-header">
        <div class="company-name">গোল্ডেন ফুড লিমিটেড</div>
        <div class="zone-name">{{ $zoneName }}</div>
        <div class="report-title">{{ $reportLabel }}</div>
        <div class="date-range">
            @if($from && $to)
                তারিখ: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            @elseif($from)
                তারিখ: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} থেকে
            @elseif($to)
                তারিখ: {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }} পর্যন্ত
            @else
                সকল তারিখ
            @endif
        </div>
    </div>

    {{-- ===== Print Button ===== --}}
    <div class="print-bar">
        <button class="btn-print" onclick="window.print()">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            প্রিন্ট করুন (Ctrl+P)
        </button>
    </div>

    {{-- ===== Table ===== --}}
    <div class="table-wrapper">
        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice ID</th>
                    <th>Type</th>
                    <th>নাম</th>
                    <th>ফোন</th>
                    <th class="text-right">মোট (৳)</th>
                    <th class="text-right">পরিশোধ (৳)</th>
                    <th class="text-right">বাকি (৳)</th>
                    <th>তারিখ</th>
                </tr>
            </thead>

            <tbody>

                @forelse($sales as $index => $sale)
                    @php
                        if ($sale->dealer) {
                            $typeLabel  = 'Dealer';
                            $badgeClass = 'badge-dealer';
                            $name       = $sale->dealer->name  ?? 'N/A';
                            $phone      = $sale->dealer->phone ?? 'N/A';
                        } elseif ($sale->customer) {
                            $typeLabel  = 'Customer';
                            $badgeClass = 'badge-customer';
                            $name       = $sale->customer->name  ?? 'N/A';
                            $phone      = $sale->customer->phone ?? 'N/A';
                        } else {
                            $typeLabel  = 'Guest';
                            $badgeClass = 'badge-guest';
                            $name       = 'Guest';
                            $phone      = '—';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>#{{ $sale->id }}</strong></td>
                        <td><span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span></td>
                        <td>{{ $name }}</td>
                        <td>{{ $phone }}</td>
                        <td class="text-right amount">{{ number_format($sale->total, 2) }}</td>
                        <td class="text-right amount">{{ number_format($sale->paid, 2) }}</td>
                        <td class="text-right amount">{{ number_format($sale->due, 2) }}</td>
                        <td>{{ $sale->created_at->format('d-m-Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="9">কোনো ডেটা পাওয়া যায়নি।</td>
                    </tr>
                @endforelse

                {{--
                    Summary row — <tfoot> ব্যবহার করা হয়নি।
                    কারণ tfoot print-এ প্রতি পেজে repeat হয়।
                    tbody-র শেষ tr হিসেবে রাখলে শুধু একবার, সবার শেষে দেখাবে।
                --}}
                @if($sales->isNotEmpty())
                <tr class="summary-row">
                    <td></td>
                    <td>মোট: {{ $totalInvoices }}টি</td>
                    <td>
                        Guest: {{ $guestCount }}<br>
                        Dealer: {{ $dealerCount }}<br>
                        Customer: {{ $customerCount }}
                    </td>
                    <td colspan="3" class="text-right">
                        সর্বমোট: ৳ {{ number_format($totalAmount, 2) }}
                    </td>
                    <td class="text-right">
                        ৳ {{ number_format($totalPaid, 2) }}
                    </td>
                    <td class="text-right">
                        ৳ {{ number_format($totalDue, 2) }}
                    </td>
                    <td></td>
                </tr>
                @endif

            </tbody>

        </table>
    </div>

</div>

</body>
</html>