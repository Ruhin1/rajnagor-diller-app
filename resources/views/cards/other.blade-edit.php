<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cards Print</title>
    <style>
        /* A4 সেটিংস, প্রিন্টে বাম, ডান, টপ, বটম মার্জিন 0 */
        @page {
            size: A4; 
            margin: 0;
        }

        body { 
            margin: 0;
            padding: 0;
            /* font-family: Arial, sans-serif; */
            background-color: #fff;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px; 
            /* padding: 8px; */
        }

        /* test */ 

        .card {
            width: 120px;
            height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            box-shadow: 1px 1px 4px rgba(0,0,0,0.2);

            /* প্রিন্টে background color আসার জন্য */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;

            page-break-inside: avoid;
        } 

       

        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .cards-container {
                gap: 6px;
                padding: 0;
            }
            .card {
                box-shadow: none; /* print-এ shadow সাধারণত না দেখায় ভালো */
            }
        }
         .card h4 {
            margin: 2px 0;
            font-size: 16px; 
            
        }
        .card p {
            margin: 0; 
            font-size: 10px;
            font: bold;
        }

   

{{-- লাল ডিজাইন ('রোস্ট সস্' ও 'বিরানী সস' এর জন্য) --}}
.card-design-red {
    background-color: #b22222; /* ফায়ারব্রিক রেড */
}
.card-design-red p {
    color: #f5f5f5; /* হোয়াইট স্মোক */
}
.card-design-red .cname,
.card-design-red .pname {
    color: #ffd700; /* গোল্ড */
    font-weight: bold;
}
.card-design-red .code {
    color: #ffffff; /* সাদা */
}

{{-- হলুদ ডিজাইন ('মিট সস' এর জন্য) --}}
.card-design-yellow {
    background-color: #FFDB58; /* মাস্টার্ড ইয়োলো */
}
.card-design-yellow p {
    color: #343a40; /* ডার্ক চারকোল */
}
.card-design-yellow .cname,
.card-design-yellow .pname {
    color: #000000; /* কালো */
    font-weight: bold;
}
.card-design-yellow .code {
    color: #0056b3; /* শক্তিশালী নীল */
}



    </style>
</head>
<body>
    <div class="cards-container">
       @foreach ($cards as $card)
    {{-- কন্ডিশন ব্যবহার করে সঠিক CSS ক্লাস নির্ধারণ করা হচ্ছে --}}
    @php
        $cardClass = 'card-design-default'; // ডিফল্ট ক্লাস

        if ($card->product_name == 'রোস্ট সস্' || $card->product_name == 'বিরানী সস') {
            $cardClass = 'card-design-red';
        } elseif ($card->product_name == 'মিট সস') {
            $cardClass = 'card-design-yellow';
        }
    @endphp

    {{-- নির্ধারিত ক্লাসটি এখানে যুক্ত করা হচ্ছে --}}
    <div class="card {{ $cardClass }}">
        <p>Golden Food</p>
       <p class="cname">01756697683</p>
        <h4 class="code">{{ $card->code }}</h4>
        <p class="pname">{{ $card->product_name }}</p> 
    </div>
@endforeach
    </div>
</body>
</html>