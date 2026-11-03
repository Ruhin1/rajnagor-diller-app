<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;






class ReportController extends Controller
{

 


    // PDF Export
//     public function exportPDF(Request $request)
// {
//     $s = $request->query('s'); // 1 = বিক্রয়, 0 = অর্ডার

//     $sales = Sale::where('order_status', '=', $s)
//         ->with('customer', 'dealer')
//         ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
//         ->when($request->to,   fn($q) => $q->whereDate('created_at', '<=', $request->to))
//         ->get();

//     $pdf = Pdf::loadView('reports.sales_pdf', compact('sales'))
//         ->setPaper('a4', 'landscape');

//     $fileName = $s == 1 ? 'sales_report.pdf' : 'orders_report.pdf';

//     return $pdf->download($fileName);
// } 
public function exportPDF(Request $request)
{
    $s = $request->query('s');

    $sales = Sale::where('order_status', '=', $s)
    ->with('customer', 'dealer')
    ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
    ->when($request->to,   fn($q) => $q->whereDate('created_at', '<=', $request->to))
    ->reorder('created_at', 'ASC')
    ->get();

    $from = $request->from;
    $to   = $request->to;
    $type = $s == 1 ? 'sales' : 'orders';

    return view('reports.sales_pdf', compact('sales', 'from', 'to', 'type'));
}

    public function exportSalesReport(Request $request)
{
    $s = $request->query('s'); // 1 = বিক্রয় (due=0), 0 = অর্ডার (due>0)

    $sales = Sale::where('order_status', '=', $s)
        ->with('customer')
        ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
        ->when($request->to,   fn($q) => $q->whereDate('created_at', '<=', $request->to))
        ->latest()
        ->get();

    $filePrefix = $s == 1 ? 'sales_report' : 'orders_report';
    $fileName   = $filePrefix . '_' . date('Y-m-d_H-i-s') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=$fileName",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];

    $columns = ['Invoice ID', 'Customer Name', 'Customer Phone', 'Total', 'Paid', 'Due', 'Date'];

    $callback = function () use ($sales, $columns) {
        $file = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, $columns);

        foreach ($sales as $sale) {
            fputcsv($file, [
                $sale->id,
                $sale->customer->name  ?? 'N/A',
                $sale->customer->phone ?? 'N/A',
                number_format($sale->total, 2),
                number_format($sale->paid,  2),
                number_format($sale->due,   2),
                $sale->created_at->format('d-m-Y h:i A'),
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
} 

// app/Http/Controllers/ReportController.php

// public function productSalesAnalysis()
// {
//     // একটি মাত্র অপ্টিমাইজড কোয়েরি ব্যবহার করে সব ডেটা আনা হচ্ছে
//     $productsReport = SaleItem::query()
//         ->join('products', 'sale_items.product_id', '=', 'products.id')
//         ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
//         ->select(
//             'products.name as product_name',
//             // ১. মোট কত পিস বিক্রি হয়েছে (অপরিবর্তিত)
//             DB::raw('SUM(sale_items.quantity) as total_sold'),
            
//             // ২. ডিলার প্রাইজে (DP) কত পিস বিক্রি হয়েছে (অপরিবর্তিত)
//             DB::raw('SUM(CASE WHEN sales.dealer_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as sold_at_dp'),
            
//             // ৩. ট্রেড প্রাইজে (TP) কত পিস বিক্রি হয়েছে (অপরিবর্তিত)
//             DB::raw('SUM(CASE WHEN sales.customer_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as sold_at_tp'),

//             // *** নতুন: গেস্ট হিসেবে কত পিস বিক্রি হয়েছে ***
//             DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL THEN sale_items.quantity ELSE 0 END) as sold_as_guest'),
            
//             // ৪. বাকিতে কত পিস বিক্রি হয়েছে (অপরিবর্তিত)
//             DB::raw('SUM(CASE WHEN sales.due > 0 THEN sale_items.quantity ELSE 0 END) as sold_on_due'),
            
//             // ৫. নগদে কত পিস বিক্রি হয়েছে (অপরিবর্তিত)
//             DB::raw('SUM(CASE WHEN sales.due = 0 THEN sale_items.quantity ELSE 0 END) as sold_on_cash')
//         )
//         ->groupBy('products.id', 'products.name')
//         ->orderBy('total_sold', 'desc')
//         ->get();

//     return view('reports.product_sales_analysis', compact('productsReport'));
// }

//  public function productSalesAnalysis(Request $request)
//     {
       
        
//         if(url('/') == 'https://mb.gffoodandbeverage.com'){
//             // ১. মাস ফিল্টার গ্রহণ (ডিফল্ট বর্তমান মাস)
//             $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
//             $carbonDate = Carbon::createFromFormat('Y-m', $selectedMonth);

//             // ২. অপ্টিমাইজড কোয়েরি
//             $query = SaleItem::query()
//                 ->join('products', 'sale_items.product_id', '=', 'products.id')
//                 ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
//                 ->select(
//                     'products.name as product_name',
//                     DB::raw('SUM(sale_items.quantity) as total_sold'),
//                     DB::raw('SUM(CASE WHEN sales.dealer_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as sold_at_dp'),
//                     DB::raw('SUM(CASE WHEN sales.customer_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as sold_at_tp'),
//                     DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL THEN sale_items.quantity ELSE 0 END) as sold_as_guest'),
//                     DB::raw('SUM(CASE WHEN sales.due > 0 THEN sale_items.quantity ELSE 0 END) as sold_on_due'),
//                     DB::raw('SUM(CASE WHEN sales.due = 0 THEN sale_items.quantity ELSE 0 END) as sold_on_cash')
//                 )
//                 ->groupBy('products.id', 'products.name')
//                 ->orderBy('total_sold', 'desc');

//             // ৩. মাস অনুযায়ী ফিল্টার প্রয়োগ
//             if ($selectedMonth) {
//                 $query->whereYear('sales.created_at', $carbonDate->year)
//                     ->whereMonth('sales.created_at', $carbonDate->month);
//             }

//             $productsReport = $query->get();

//             // ৪. ভিউতে ডেটা পাঠানো
//             return view('reports.product_sales_analysis', compact('productsReport', 'selectedMonth'));
//         }else{
//             $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
//             $carbonDate = Carbon::createFromFormat('Y-m', $selectedMonth);

//     // বেস কোয়েরি
//             $query = SaleItem::query()
//                 ->join('products', 'sale_items.product_id', '=', 'products.id')
//                 ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
//                 ->select(
//                 'products.name as product_name',
            
//             // ১. মোট বিক্রি (এখন is_dipu = 1 বাদ দিয়ে)
//             // DB::raw('SUM(CASE WHEN sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as total_sold'),
//             DB::raw('SUM(sale_items.quantity) as total_sold'),
            
//             // ২. ডিলার প্রাইস (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.dealer_id IS NOT NULL AND sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as sold_at_dp'),
            
//             // ৩. ট্রেড প্রাইস (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.customer_id IS NOT NULL AND sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as sold_at_tp'),
            
//             // ৪. গেস্ট বিক্রি tp (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL AND sales.is_dipu != 1 AND 	sales.guest_sell_type = 1 THEN sale_items.quantity ELSE 0 END) as sold_as_guest_tp'),

//             // ৪. গেস্ট বিক্রি dp (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL AND sales.is_dipu != 1 AND 	sales.guest_sell_type = 2 THEN sale_items.quantity ELSE 0 END) as sold_as_guest_dp'),
            
//             // ৫. বাকিতে বিক্রি (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.due > 0  THEN sale_items.quantity ELSE 0 END) as sold_on_due'),
            
//             // ৬. নগদে বিক্রি (is_dipu = 1 বাদ দিয়ে)
//             DB::raw('SUM(CASE WHEN sales.due = 0 THEN sale_items.quantity ELSE 0 END) as sold_on_cash'),

//             // *** ৭. নতুন: শুধুমাত্র ডিপো সেলের জন্য গণনা ***
//             DB::raw('SUM(CASE WHEN sales.is_dipu = 1 THEN sale_items.quantity ELSE 0 END) as sold_at_dipu')
//         )
//         ->groupBy('products.id', 'products.name')
//         ->orderBy(DB::raw('SUM(sale_items.quantity)'), 'desc'); // মোট বিক্রির (ডিপো সহ) উপর ভিত্তি করে সর্ট করা

//     // মাস অনুযায়ী ফিল্টার
//     if ($selectedMonth) {
//         $query->whereYear('sales.created_at', $carbonDate->year)
//               ->whereMonth('sales.created_at', $carbonDate->month);
//     }

//     $productsReport = $query->get();

//     return view('reports.product_sales_analysis-km', compact('productsReport', 'selectedMonth'));
//         }

       

//     }

//=======================
 public function productSalesAnalysis(Request $request)
    {
            $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
            $carbonDate = Carbon::createFromFormat('Y-m', $selectedMonth);

    // বেস কোয়েরি
            $query = SaleItem::query()
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                'products.name as product_name',
            
            // ১. মোট বিক্রি (এখন is_dipu = 1 বাদ দিয়ে)
            // DB::raw('SUM(CASE WHEN sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as total_sold'),
            DB::raw('SUM(sale_items.quantity) as total_sold'),
            
            // ২. ডিলার প্রাইস (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.dealer_id IS NOT NULL AND sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as sold_at_dp'),
            
            // ৩. ট্রেড প্রাইস (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.customer_id IS NOT NULL AND sales.is_dipu != 1 THEN sale_items.quantity ELSE 0 END) as sold_at_tp'),
            
            // ৪. গেস্ট বিক্রি tp (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL AND sales.is_dipu != 1 AND 	sales.guest_sell_type = 1 THEN sale_items.quantity ELSE 0 END) as sold_as_guest_tp'),

            // ৪. গেস্ট বিক্রি dp (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.customer_id IS NULL AND sales.dealer_id IS NULL AND sales.is_dipu != 1 AND 	sales.guest_sell_type = 2 THEN sale_items.quantity ELSE 0 END) as sold_as_guest_dp'),
            
            // ৫. বাকিতে বিক্রি (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.due > 0  THEN sale_items.quantity ELSE 0 END) as sold_on_due'),
            
            // ৬. নগদে বিক্রি (is_dipu = 1 বাদ দিয়ে)
            DB::raw('SUM(CASE WHEN sales.due = 0 THEN sale_items.quantity ELSE 0 END) as sold_on_cash'),

            // *** ৭. নতুন: শুধুমাত্র ডিপো সেলের জন্য গণনা ***
            DB::raw('SUM(CASE WHEN sales.is_dipu = 1 THEN sale_items.quantity ELSE 0 END) as sold_at_dipu')
        )
        ->groupBy('products.id', 'products.name')
        ->orderBy(DB::raw('SUM(sale_items.quantity)'), 'desc'); // মোট বিক্রির (ডিপো সহ) উপর ভিত্তি করে সর্ট করা

    // মাস অনুযায়ী ফিল্টার
    if ($selectedMonth) {
        $query->whereYear('sales.created_at', $carbonDate->year)
              ->whereMonth('sales.created_at', $carbonDate->month);
    }

    $productsReport = $query->get();

    return view('reports.product_sales_analysis-km', compact('productsReport', 'selectedMonth'));
        
    }

//=======================

}




