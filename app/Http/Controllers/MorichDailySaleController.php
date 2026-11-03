<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MorichDailySale;
use App\Models\MorichProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MorichDailySaleController extends Controller
{
    public function create()
    {
        $products = MorichProduct::orderBy('name')->get();
        return view('morich.daily_sale.create', compact('products'));
    }

    public function store(Request $request)
    {
        // ১. ভ্যালিডেশন আপডেট করা হয়েছে
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:morich_products,id',
            'items.*.quantity_kg' => 'nullable|integer|min:0',
            'items.*.quantity_gram' => 'nullable|integer|min:0|max:999',
        ]);

        try {
            $dailySale = DB::transaction(function () use ($validated) {
                $sale = MorichDailySale::create([
                    'sale_date' => $validated['sale_date'],
                    'notes' => $validated['notes'],
                    'user_id' => Auth::id(),
                ]);

                foreach ($validated['items'] as $itemData) {
                    // ২. কেজি এবং গ্রাম থেকে মোট গ্রাম গণনা
                    $kgInGrams = ($itemData['quantity_kg'] ?? 0) * 1000;
                    $grams = $itemData['quantity_gram'] ?? 0;
                    $totalGramsSold = $kgInGrams + $grams;

                    // ৩. যদি কোনো পরিমাণ ইনপুট না দেওয়া হয়, তাহলে এই আইটেমটি উপেক্ষা করা
                    if ($totalGramsSold <= 0) {
                        continue; // লুপের পরবর্তী আইটেমে চলে যাবে
                    }

                    $product = MorichProduct::find($itemData['product_id']);
                    
                    // ৪. স্টক যাচাই
                    if ($product->stock_in_grams < $totalGramsSold) {
                        throw new \Exception("{$product->name}-এর পর্যাপ্ত স্টক নেই। বর্তমান স্টক: {$product->stock_in_grams} গ্রাম, বিক্রির পরিমাণ: {$totalGramsSold} গ্রাম।");
                    }

                    // ৫. সেল আইটেম সেভ (মোট গ্রাম হিসেবে)
                    $sale->items()->create([
                        'morich_product_id' => $itemData['product_id'],
                        'quantity_sold_in_grams' => $totalGramsSold,
                    ]);

                    // ৬. স্টক কমানো
                    $product->decrement('stock_in_grams', $totalGramsSold);
                }

                // যদি কোনো আইটেমই প্রসেস না হয় (সব ইনপুট খালি থাকলে)
                if ($sale->items()->count() === 0) {
                    throw new \Exception('অনুগ্রহ করে অন্তত একটি পণ্যের বিক্রির পরিমাণ লিখুন।');
                }

                return $sale;
            });
            return redirect()->route('morich.daily-sale.summary', $dailySale->id)->with('success', 'দিনের বিক্রয় সফলভাবে সংরক্ষণ করা হয়েছে।');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['general' => 'একটি সমস্যা হয়েছে: ' . $e->getMessage()]);
        }
    }

    public function summary(MorichDailySale $morichDailySale)
    {
        $morichDailySale->load('items.product', 'user');
        $allProducts = MorichProduct::orderBy('name')->get();
        return view('morich.daily_sale.summary', ['sale' => $morichDailySale, 'allProducts' => $allProducts]);
    }

     public function reports(Request $request)
    {
        $query = MorichDailySale::with('user', 'items')->latest();
        
        // ডিফল্ট ফিল্টার 'all' সেট করা হলো
        $filter = $request->query('filter', 'all');

        // তারিখের রেঞ্জ দিয়ে সার্চ করার জন্য
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('sale_date', [$request->start_date, $request->end_date]);
            // যদি তারিখ রেঞ্জ ব্যবহার হয়, তাহলে অন্য ফিল্টারকে 'custom' সেট করা হলো
            $filter = 'custom'; 
        } 
        // দ্রুত ফিল্টার বাটনগুলোর জন্য
        else {
            if ($filter === 'today') {
                $query->whereDate('sale_date', Carbon::today());
            } elseif ($filter === 'week') {
                $query->whereBetween('sale_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($filter === 'month') {
                $query->whereBetween('sale_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            }
        }

        $sales = $query->paginate(15);
        
        return view('morich.daily_sale.reports', compact('sales', 'filter'));
    }
}
