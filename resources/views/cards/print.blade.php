<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cards Print</title>
    {{-- <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .cards-container {
                /* gap: 10px !important;
                padding: 0; */
            }
            .card {
                box-shadow: none;
                
            }
        }
        
        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 8px;
        }
        .card {
            width: 145px;  /* আগের চেয়ে একটু বড় */
            height: 80px;  /* আগের চেয়ে একটু বড় */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            /* border-radius: 8px; */
            background-color: #FFFFFF;
            border: 1px solid red;
            /* box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2); */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            page-break-inside: avoid;
            padding: 2px;
        }
        
        
        
        .card p {
            margin: 1px 0;
            font-size: 11px;
            font-weight: bold;
        }
        
        .card .cname,
        .card .pname {
            color: #008080;
        }
        
        .card .barcode {
            margin: 6px 0;
            
        }
        
        /* বারকোড SVG এর জন্য স্টাইল */
        
        
        .card .barcode-text {
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 1px;
            font-size: 18px;
            text-align: center;
            color: #000000 !important; /* কালো করা হয়েছে */
            font-weight: bold;
            white-space: nowrap;
            margin-top: 2px;
        }
    </style> --}}

<style>
    @page {
        size: A4;
        margin: 0;
    }
    @media print {
        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .card {
            box-shadow: none;
            background-color: #fef3c7 !important;
        }
    }

    body {
        margin: 0;
        padding: 0;
        background-color: #fff;
    }

    .cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        padding: 8px;
    }

    .card {
        width: 145px;
        height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        /* 🎨 ব্যাকগ্রাউন্ড: উষ্ণ ক্রিম */
        background-color: #fef3c7;
        /* 🎨 বর্ডার: গাঢ় নীল (ব্যাকগ্রাউন্ডের থেকে সম্পূর্ণ আলাদা) */
        border: 2px solid #1e40af;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        page-break-inside: avoid;
        padding: 2px;
    }

    .card p {
        margin: 1px 0;
        font-size: 11px;
        font-weight: bold;
    }

    /* 🎨 টেক্সট: গাঢ় বাদামি (বর্ডারের নীল থেকেও আলাদা) */
    .card .cname,
    .card .pname {
        color: #7c2d12;
    }

    /* 🎨 বারকোড হাইলাইট: পিওর সাদা বক্স */
    .card .barcode {
        margin: 4px 0;
        background: #ffffff;
        padding: 2px 4px;
        border: 1px solid #1e40af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* 🎨 বারকোডের নিচের কোড: কালো (স্ক্যানার ফ্রেন্ডলি) */
    .card .barcode-text {
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 1px;
        font-size: 18px;
        text-align: center;
        color: #000000 !important;
        font-weight: bold;
        white-space: nowrap;
        margin-top: 2px;
    }
</style>
</head>
<body>
    <div class="cards-container">
       @foreach ($cards as $card)
            <div class="card">
                <p class="cname"><span>বদরুল  /</span>  01709090957</p>

                <div class="barcode"> 
                    {!! DNS1D::getBarcodeHTML($card->code, 'C128', 1, 20) !!}    
                   
                </div>
                <span class="barcode-text">{{ $card->code }}</span>
                <p class="pname">{{ $card->product_name }}</p> 
            </div>
        @endforeach
    </div>
     
     
</body>
</html>



