<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cards Print</title>
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

        
        
        
        .card p {
            margin: 1px 0;
            font-size: 11px;
            font-weight: bold;
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



        /* //=============================== */

.card {
    width: 145px;
    height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    page-break-inside: avoid;
    padding: 2px;

    background-color: #fbcfe8;
    border: 1px solid #9f1239;
}
.card .cname,
.card .pname {
    color: #881337;
}
.card .barcode {
    margin: 6px 0;
    background-color: #ffffff;
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



