<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MorichProduct;
use Carbon\Carbon;
use App\Models\MorichDailySaleItem;

class MorichProductController extends Controller
{
    public function index()
    {
        $products = MorichProduct::latest()->paginate(10);
        return view('morich.products.index', compact('products'));
    }

    public function create()
    {
        return view('morich.products.create');
    }

    public function store(Request $request)
    {
        // ১. ভ্যালিডেশন আপডেট করা হয়েছে
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:morich_products,name',
            'stock_kg' => 'nullable|integer|min:0',
            'stock_gram' => 'nullable|integer|min:0|max:999',
        ]);

        // কেজি এবং গ্রাম থেকে মোট গ্রাম গণনা
        $kgInGrams = ($validated['stock_kg'] ?? 0) * 1000;
        $grams = $validated['stock_gram'] ?? 0;
        $totalGrams = $kgInGrams + $grams;

        // ২. নিশ্চিত করা যে অন্তত কিছু স্টক ইনপুট দেওয়া হয়েছে
        if ($totalGrams <= 0) {
            // যদি কেজি এবং গ্রাম উভয়ই খালি বা ০ থাকে, তাহলে একটি ভ্যালিডেশন এরর পাঠানো
            return back()
                ->withInput()
                ->withErrors(['stock_total' => 'অনুগ্রহ করে কেজি বা গ্রামে স্টকের পরিমাণ লিখুন।']);
        }

        // ৩. ডেটাবেজে নতুন পণ্য তৈরি
        MorichProduct::create([
            'name' => $validated['name'], 
            'stock_in_grams' => $totalGrams
        ]);

        return redirect()->route('morich.products.index')
            ->with('success', 'নতুন পণ্য সফলভাবে যোগ করা হয়েছে।');
    }

    public function edit(MorichProduct $product)
    {
        return view('morich.products.edit', compact('product'));
    }

    public function update(Request $request, MorichProduct $product)
    {
        // ১. ভ্যালিডেশন আপডেট করা হয়েছে
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:morich_products,name,' . $product->id,
            'add_stock_kg' => 'nullable|integer|min:0',
            'add_stock_gram' => 'nullable|integer|min:0|max:999',
        ]);
        
        // পণ্যের নাম আপডেট করা
        $product->name = $validated['name'];

        // কেজি এবং গ্রাম থেকে মোট গ্রাম গণনা
        $kgInGrams = ($validated['add_stock_kg'] ?? 0) * 1000;
        $grams = $validated['add_stock_gram'] ?? 0;
        $totalGramsToAdd = $kgInGrams + $grams;

        // ২. যদি নতুন স্টক যোগ করার জন্য কোনো ইনপুট দেওয়া হয়
        if ($totalGramsToAdd > 0) {
            // Eloquent-এর increment মেথড ব্যবহার করে পারমাণবিক (atomic) ভাবে স্টক বাড়ানো হলো
            $product->increment('stock_in_grams', $totalGramsToAdd);
        }
        
        // ৩. শুধুমাত্র নাম পরিবর্তন হলে সেভ করা (যদি স্টক যোগ না হয়)
        if ($product->isDirty('name')) {
            $product->save();
        }

        return redirect()->route('morich.products.index')
            ->with('success', 'পণ্যের তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(MorichProduct $product)
    {
        try {
            $product->delete();
            return redirect()->route('morich.products.index')->with('success', 'পণ্যটি সফলভাবে ডিলেট করা হয়েছে।');
        } catch (\Exception $e) {
            return redirect()->route('morich.products.index')->with('error', 'পণ্যটি ডিলেট করা যায়নি। এটি সম্ভবত কোনো বিক্রির সাথে যুক্ত আছে।');
        }
    }
   
    public function show()
    {
        
         // সব পণ্য এবং তাদের স্টক লোড করা হচ্ছে, নাম অনুযায়ী সাজিয়ে
        $products = MorichProduct::orderBy('name')->get();

        // ভিউতে ডেটা পাঠানো হচ্ছে
        return view('morich.products.stock_report', compact('products'));
    }

    public function salesReport(Request $request)
    {
        
        // ১. সব পণ্যের তালিকা আনা হলো, যা রিপোর্টের ভিত্তি হিসেবে কাজ করবে
        $allProducts = \App\Models\MorichProduct::orderBy('name')->get();

        // ২. বিক্রিত আইটেমের উপর বেস কোয়েরি তৈরি
        $query = \App\Models\MorichDailySaleItem::query();

        // ৩. তারিখ অনুযায়ী ফিল্টার করার লজিক
        $filter = $request->query('filter', 'all');
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($startDate && $endDate) {
            // join করে sales টেবিলের sale_date অনুযায়ী ফিল্টার করা হচ্ছে
            $query->whereHas('sale', function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
            });
            $filter = 'custom';
        } else {
            switch ($filter) {
                case 'today':
                    $query->whereHas('sale', fn($q) => $q->whereDate('sale_date', Carbon::today()));
                    break;
                case 'week':
                    $query->whereHas('sale', fn($q) => $q->whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]));
                    break;
                case 'month':
                    $query->whereHas('sale', fn($q) => $q->whereYear('sale_date', Carbon::now()->year)->whereMonth('sale_date', Carbon::now()->month));
                    break;
            } 
        }
        
        // ৪. ফিল্টার করা ডেটা থেকে প্রতিটি পণ্যের মোট বিক্রির পরিমাণ বের করা
        // pluck() ব্যবহার করে একটি key-value অ্যারে তৈরি করা হচ্ছে (product_id => total_sold)
        $salesData = $query->groupBy('morich_product_id')
            ->selectRaw('morich_product_id, SUM(quantity_sold_in_grams) as total_sold')
            ->pluck('total_sold', 'morich_product_id');

        return view('morich.products.sales_report', compact(
            'allProducts', 
            'salesData', 
            'filter', 
            'startDate', 
            'endDate'
        ));
    }
    public function stockEntries()
{
    $entries = \App\Models\MorichStockEntry::with(['product', 'user'])
        ->latest() // নতুন এন্ট্রিগুলো আগে দেখাবে
        ->paginate(20);

    return view('morich.products.stock_entries', compact('entries'));
}
// app/Http/Controllers/MorichProductController.php

public function stockMovementReport(Request $request)
{
    // ১. তারিখ ফিল্টার নির্ধারণ
    $startDate = $request->input('start_date', Carbon::today()->toDateString());
    $endDate = $request->input('end_date', $startDate);

    // ২. সব পণ্যের তালিকা আনা হলো
    $products = MorichProduct::orderBy('name')->get();

    // ৩. নির্বাচিত সময়সীমার মধ্যে মোট "বিক্রির" পরিমাণ
    $salesDuringPeriod = MorichDailySaleItem::whereHas('sale', function($q) use ($startDate, $endDate) {
            $q->whereBetween('sale_date', [$startDate, $endDate]);
        })
        ->groupBy('morich_product_id')
        ->selectRaw('morich_product_id, SUM(quantity_sold_in_grams) as total_sold')
        ->pluck('total_sold', 'morich_product_id');

    // ৪. নির্বাচিত সময়সীমার মধ্যে মোট "নতুন স্টক" যোগের পরিমাণ
    $stockInDuringPeriod = \App\Models\MorichStockEntry::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        ->groupBy('morich_product_id')
        ->selectRaw('morich_product_id, SUM(quantity_added_in_grams) as total_added')
        ->pluck('total_added', 'morich_product_id');

    // ৫. নির্বাচিত তারিখের *আগে* মোট কত স্টক যোগ হয়েছিল
    $stockInBeforePeriod = \App\Models\MorichStockEntry::where('created_at', '<', $startDate . ' 00:00:00')
        ->groupBy('morich_product_id')
        ->selectRaw('morich_product_id, SUM(quantity_added_in_grams) as total_added')
        ->pluck('total_added', 'morich_product_id');

    // ৬. নির্বাচিত তারিখের *আগে* মোট কত বিক্রি হয়েছিল
    $salesBeforePeriod = MorichDailySaleItem::whereHas('sale', function($q) use ($startDate) {
            $q->where('sale_date', '<', $startDate);
        })
        ->groupBy('morich_product_id')
        ->selectRaw('morich_product_id, SUM(quantity_sold_in_grams) as total_sold')
        ->pluck('total_sold', 'morich_product_id');
        
    // ৭. প্রতিটি পণ্যের জন্য রিপোর্ট ডেটা প্রস্তুত করা (চূড়ান্ত এবং নির্ভুল লজিক)
    $reportData = [];
    foreach ($products as $product) {
        
        // ক. পূর্বের স্টক গণনা
        $totalInBefore = $stockInBeforePeriod->get($product->id, 0);
        $totalOutBefore = $salesBeforePeriod->get($product->id, 0);
        $previousStock = $totalInBefore - $totalOutBefore;
        
        // খ. বর্তমান সময়ের লেনদেন
        $stockInPeriod = $stockInDuringPeriod->get($product->id, 0);
        $saleDuringPeriod = $salesDuringPeriod->get($product->id, 0);

        // গ. মোট স্টক (বিক্রির আগে)
        $totalStock = $previousStock + $stockInPeriod;

        // ঘ. অবশিষ্ট স্টক গণনা (দিনের শেষে)
        $closingStock = $totalStock - $saleDuringPeriod;

        $reportData[] = [
            'name' => $product->name,
            'previous_stock' => $previousStock,
            'stock_in' => $stockInPeriod,
            'total_stock' => $totalStock,
            'current_sale' => $saleDuringPeriod,
            'closing_stock' => $closingStock,
        ];
    }

    return view('morich.products.stock_movement', compact(
        'reportData', 
        'startDate', 
        'endDate'
    ));
}

public function dateWiseLedger(Request $request)
{
    // ১. তারিখ ফিল্টার নির্ধারণ (ডিফল্ট বর্তমান মাস)
    $startDate = Carbon::parse($request->input('start_date', Carbon::now()->startOfMonth()));
    $endDate = Carbon::parse($request->input('end_date', Carbon::now()));

    // ২. শুধুমাত্র একটি পণ্য ফিল্টার করার জন্য (ঐচ্ছিক)
    $productId = $request->input('product_id');

    // ৩. সব পণ্যের তালিকা (ড্রপডাউনের জন্য)
    $products = MorichProduct::orderBy('name')->get();

    // ৪. নির্বাচিত তারিখের *আগে* মোট স্টক যোগ এবং বিক্রির হিসাব
    $stockInBefore = \App\Models\MorichStockEntry::where('created_at', '<', $startDate->toDateString())
        ->when($productId, fn($q) => $q->where('morich_product_id', $productId))
        ->sum('quantity_added_in_grams');

    $salesBefore = MorichDailySaleItem::whereHas('sale', function($q) use ($startDate) {
            $q->where('sale_date', '<', $startDate->toDateString());
        })
        ->when($productId, fn($q) => $q->where('morich_product_id', $productId))
        ->sum('quantity_sold_in_grams');

    // ৫. লেজারের শুরুর ব্যালেন্স (Opening Balance)
    $openingBalance = $stockInBefore - $salesBefore;

    // ৬. নির্বাচিত সময়সীমার সব "স্টক ইন" এবং "বিক্রি" তারিখ অনুযায়ী গ্রুপ করে আনা
    $stockIns = \App\Models\MorichStockEntry::whereBetween('created_at', [$startDate, $endDate->endOfDay()])
        ->when($productId, fn($q) => $q->where('morich_product_id', $productId))
        ->groupBy('date')
        ->selectRaw('DATE(created_at) as date, SUM(quantity_added_in_grams) as total')
        ->pluck('total', 'date');
    
    $sales = MorichDailySaleItem::join('morich_daily_sales', 'morich_daily_sale_items.morich_daily_sale_id', '=', 'morich_daily_sales.id')
        ->whereBetween('morich_daily_sales.sale_date', [$startDate, $endDate])
        ->when($productId, fn($q) => $q->where('morich_daily_sale_items.morich_product_id', $productId))
        ->groupBy('date')
        ->selectRaw('morich_daily_sales.sale_date as date, SUM(morich_daily_sale_items.quantity_sold_in_grams) as total')
        ->pluck('total', 'date');

    // ৭. প্রতিটি দিনের জন্য লেজার ডেটা প্রস্তুত করা
    $ledgerData = [];
    $balance = $openingBalance;
    
    // শুরু থেকে শেষ তারিখ পর্যন্ত প্রতিটি দিনের জন্য লুপ চালানো
    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
        $currentDateStr = $date->toDateString();
        
        $stockIn = $stockIns->get($currentDateStr, 0);
        $saleOut = $sales->get($currentDateStr, 0);
        
        // যদি ঐ দিনে কোনো লেনদেন না থাকে, তাহলে রিপোর্ট দেখানোর দরকার নেই
        if ($stockIn == 0 && $saleOut == 0) {
            continue;
        }

        $closingBalance = $balance + $stockIn - $saleOut;

        $ledgerData[] = [
            'date' => $currentDateStr,
            'opening_stock' => $balance,
            'stock_in' => $stockIn,
            'sale_out' => $saleOut,
            'closing_stock' => $closingBalance,
        ]; 

        // পরবর্তী দিনের জন্য ব্যালেন্স আপডেট করা
        $balance = $closingBalance;
    }

    return view('morich.products.date_wise_ledger', compact(
        'products',
        'ledgerData',
        'openingBalance',
        'startDate',
        'endDate',
        'productId'
    ));
}





}
 