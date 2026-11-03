<?php

namespace App\Http\Controllers;
use App\Services\BanglaPrinterService;
use App\Services\BluetoothPrinterService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Validation\ValidationException;
use App\Models\Dealer;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use PDF;
use App\Services\StatementPrinterService;
use App\Services\DillerStatementPrinterService;
use App\Services\BluetoothStatementPrinterService;


class SaleController extends Controller
{
    

// public function store(Request $request)
// {
//     // ১. ভ্যালিডেশন আপডেট করা হয়েছে
//     $data = $request->validate([
//         "sale_type" => ["required", "in:customer,guest"], // কোন ধরনের বিক্রি
//         "customer_id" => ["required_if:sale_type,customer", "nullable", "exists:customers,id"],
//         "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
//         "guest_phone" => ["nullable", "string", "max:20"],
//         "ddate" => ["nullable", "date"],
//         "discount" => ["nullable", "numeric", "min:0"],
//         "shipping" => ["nullable", "numeric", "min:0"],
//         "paid" => ["required", "numeric", "min:0"],
//         "payment_method" => ["nullable", "string", "max:50"],
//         "items" => ["required", "array", "min:1"],
//         "items.*.product_id" => ["required", "exists:products,id"],
//         "items.*.price" => ["required", "numeric", "min:0"],
//         "items.*.quantity" => ["required", "integer", "min:1"],
//     ]);

//     return DB::transaction(function () use ($data) {
//         $subtotal = 0;
//         $itemsForCreate = [];

//         foreach ($data["items"] as $row) {
//             $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
//             if (!$product || $product->quantity < $row["quantity"]) {
//                 throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।"]]);
//             }
//             $lineTotal = bcmul($row["price"], $row["quantity"], 2);
//             $subtotal = bcadd($subtotal, $lineTotal, 2);
//             $product->decrement("quantity", $row["quantity"]);
//             $itemsForCreate[] = [
//                 "product_id" => $product->id, "price" => $row["price"], "quantity" => $row["quantity"],
//                 "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
//             ];
//         }

//         $discount = $data["discount"] ?? 0;
//         $shipping = $data["shipping"] ?? 0;
//         $total = bcadd($subtotal - $discount, $shipping, 2);
//         $paid = $data["paid"];
//         $due = max(0, (float) $total - (float) $paid);
        
//         // ২. গেস্ট কাস্টমারের জন্য বকেয়া চেক
//         if ($data['sale_type'] === 'guest' && $due > 0) {
//             throw ValidationException::withMessages([
//                 "paid" => ["গেস্ট কাস্টমারের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
//             ]);
//         }

//         $saleData = [
//             "customer_id" => $data["customer_id"] ?? null,
//             "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
//             "total" => $total, "paid" => $paid, "due" => $due,
//             "payment_method" => $data["payment_method"] ?? null,
//             "status" => $due > 0 ? "pending" : "paid",
//             "order_status" => $due > 0 ? 0 : 1,
//             "ddate" => $data["ddate"] ?? now()->toDateString(),
//             "srname" => auth()->user()->name, "user_id" => auth()->user()->id,
//         ];

//         // ৩. গেস্টের তথ্য যুক্ত করা
//         if ($data['sale_type'] === 'guest') {
//             $saleData['guest_name'] = $data['guest_name'];
//             $saleData['guest_phone'] = $data['guest_phone'];
//             $saleData['guest_sell_type'] = 1;

            
//         }

//         $sale = Sale::create($saleData);
//         $sale->items()->createMany($itemsForCreate);

//         return response()->json([
//             "status" => "success",
//             "sale_id" => $sale->id,
//             "redirect" => route("sales.show", $sale->id), // Normal Print URL
//             "pos_url"  => route("sales.pos.print", $sale->id), // POS Print URL (নতুন যুক্ত করা হয়েছে)
//             "bluetooth_print_url" => route("sales.bluetooth.print", $sale->id), // Bluetooth Print URL (নতুন যুক্ত করা হয়েছে)
//         ]);
//     });
// }



public function store(Request $request)
{
    $data = $request->validate([
        "sale_type" => ["required", "in:customer,guest"],
        "customer_id" => ["required_if:sale_type,customer", "nullable", "exists:customers,id"],
        "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
        "guest_phone" => ["nullable", "string", "max:20"],
        "ddate" => ["nullable", "date"],
        "discount" => ["nullable", "numeric", "min:0"],
        "shipping" => ["nullable", "numeric", "min:0"],
        "paid" => ["required", "numeric", "min:0"],
        "payment_method" => ["nullable", "string", "max:50"],
        "items" => ["required", "array", "min:1"],
        "items.*.product_id" => ["required", "exists:products,id"],
        "items.*.price" => ["required", "numeric", "min:0"],
        "items.*.quantity" => ["required", "integer", "min:1"],
    ]);

    return DB::transaction(function () use ($data) {
        $subtotal = 0;
        $itemsForCreate = [];

        foreach ($data["items"] as $row) {
            $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
            
            // অর্ডার করার সময়ও চেক করছি যেন স্টক না থাকলে অর্ডারটি ক্রিয়েট না হয় (তবে স্টক মাইনাস হবে না)
            if (!$product || $product->quantity < $row["quantity"]) {
                throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।"]]);
            }
            
            $lineTotal = bcmul($row["price"], $row["quantity"], 2);
            $subtotal = bcadd($subtotal, $lineTotal, 2);
            
            // NOTE: এখান থেকে $product->decrement() বাদ দেওয়া হয়েছে।

            $itemsForCreate[] = [
                "product_id" => $product->id, "price" => $row["price"], "quantity" => $row["quantity"],
                "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
            ];
        }

        $discount = $data["discount"] ?? 0;
        $shipping = $data["shipping"] ?? 0;
        $total = bcadd($subtotal - $discount, $shipping, 2);
        $paid = $data["paid"];
        $due = max(0, (float) $total - (float) $paid);
        
        if ($data['sale_type'] === 'guest' && $due > 0) {
            throw ValidationException::withMessages([
                "paid" => ["গেস্ট কাস্টমারের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
            ]);
        }

        $saleData = [
            "customer_id" => $data["customer_id"] ?? null,
            "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
            "total" => $total, "paid" => $paid, "due" => $due,
            "payment_method" => $data["payment_method"] ?? null,
            
            // পরিবর্তন করা হয়েছে
            "status" => "ordered", 
            "order_status" => 3, 
            
            "ddate" => $data["ddate"] ?? now()->toDateString(),
            "srname" => auth()->user()->name, 
            "user_id" => auth()->user()->id,
        ];

        if ($data['sale_type'] === 'guest') {
            $saleData['guest_name'] = $data['guest_name'];
            $saleData['guest_phone'] = $data['guest_phone'];
            $saleData['guest_sell_type'] = 1;
        }

        $sale = Sale::create($saleData);
        $sale->items()->createMany($itemsForCreate);

        return response()->json([
            "status" => "success",
            "sale_id" => $sale->id,
            "redirect" => route("sales.show", $sale->id),
            "pos_url"  => route("sales.pos.print", $sale->id),
            "bluetooth_print_url" => route("sales.bluetooth.print", $sale->id),
        ]);
    });
}

// নতুন অর্ডার দেখানোর জন্য মেথড (Pending এর হুবহু)
    public function newOrders(Request $request)
    {
        // order_status 3 (ordered) ফিল্টার করা হয়েছে
        $query = Sale::with(["customer", "dealer"])->where("order_status", 3);

        if ($request->filled("search_term")) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where("id", "like", "%{$term}%")
                  ->orWhereHas("customer", function ($subQuery) use ($term) {
                      $subQuery->where("name", "like", "%{$term}%")->orWhere("shop_name", "like", "%{$term}%");
                  })
                  ->orWhereHas("dealer", function ($subQuery) use ($term) {
                      $subQuery->where("name", "like", "%{$term}%")->orWhere("pname", "like", "%{$term}%");
                  });
            });
        }

        if ($request->filled("filter")) {
            switch ($request->filter) {
                case "today": $query->whereDate("created_at", Carbon::today()); break;
                case "this_week": $query->whereBetween("created_at", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                case "this_month": $query->whereMonth("created_at", Carbon::now()->month)->whereYear("created_at", Carbon::now()->year); break;
            }
        }

        if ($request->filled("from") && $request->filled("to")) {
            $query->whereBetween("created_at", [$request->from, $request->to]);
        } 

        if ($request->filled("sale_type")) {
            if ($request->sale_type === "dealer") $query->whereNotNull("dealer_id");
            elseif ($request->sale_type === "customer") $query->whereNotNull("customer_id");
        }

        if (auth()->user()->role == 1 && $request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        $sales = $query->orderBy("id", "desc")->paginate(20);
        $users = auth()->user()->role == 1 ? User::where('role', '!=', 3)->orderBy("name", "asc")->get(["id", "name"]) : [];

        return view("sales.new_orders", compact("sales", "users"));
    }

    // ডেলিভারি প্রসেস করার মেথড
    public function processDelivery($id)
    {
        return DB::transaction(function () use ($id) {
            // শুধু order_status 3 আছে এমন ডেটাই ডেলিভারি করা যাবে
            $sale = Sale::with('items')->where('order_status', 3)->lockForUpdate()->findOrFail($id);

            // ১. স্টক চেক এবং মাইনাস করা
            foreach ($sale->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                
                // if (!$product || $product->quantity < $item->quantity) {
                //     // স্টক কম থাকলে ডেলিভারি বাতিল করে এরর মেসেজ পাঠাবে
                //     return redirect()->back()->with('error', "{$product->name} স্টকে পর্যাপ্ত পণ্য নেই (প্রয়োজন: {$item->quantity}, স্টকে আছে: {$product->quantity})। ডেলিভারি সম্ভব নয়।");
                // }
                
               // স্টক কমানো হচ্ছে
                $product->decrement('quantity', $item->quantity);
            }

            // ২. সেলস টেবিল আপডেট করা (ডিউ এর উপর ভিত্তি করে)
            $sale->status = $sale->due > 0 ? "pending" : "paid";
            $sale->order_status = $sale->due > 0 ? 0 : 1;
            $sale->ddate = now()->toDateString();
            $sale->save();

            return redirect()->back()->with('success', 'ডেলিভারি সফলভাবে সম্পন্ন হয়েছে এবং প্রোডাক্টের স্টক আপডেট করা হয়েছে।');
        });
    }

    public function revertToNewOrder($id)
{
    // শুধুমাত্র অ্যাডমিন এটি করতে পারবে (সিকিউরিটির জন্য চেক)
    if (auth()->user()->role != 1) {
        return redirect()->back()->with('error', 'আপনার এই কাজটি করার অনুমতি নেই।');
    }

    return DB::transaction(function () use ($id) {
        $sale = Sale::with('items')->lockForUpdate()->findOrFail($id);

        // ১. যে স্টকগুলো ডেলিভারির সময় মাইনাস হয়েছিল, সেগুলো পুনরায় প্লাস করা
        foreach ($sale->items as $item) {
            Product::where('id', $item->product_id)->lockForUpdate()->increment('quantity', $item->quantity);
        }

        // ২. সেলস স্ট্যাটাস আগের অবস্থায় (Ordered) ফিরিয়ে আনা
        $sale->status = 'ordered';
        $sale->order_status = 3; 
        $sale->save();

        return redirect()->back()->with('success', 'অর্ডারটি সফলভাবে ডেলিভারি (New Orders) পেজে ফেরত পাঠানো হয়েছে এবং স্টক পুনরায় অ্যাড করা হয়েছে।');
    });
}

// public function isdipu(Request $request)
// {
//     // ১. ভ্যালিডেশন আপডেট করা হয়েছে
//     $data = $request->validate([
//         "sale_type" => ["required", "in:customer,guest"], // কোন ধরনের বিক্রি
//         "customer_id" => ["required_if:sale_type,customer", "nullable", "exists:customers,id"],
//         "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
//         "guest_phone" => ["nullable", "string", "max:20"],
//         "ddate" => ["nullable", "date"],
//         "discount" => ["nullable", "numeric", "min:0"],
//         "shipping" => ["nullable", "numeric", "min:0"],
//         "paid" => ["required", "numeric", "min:0"],
//         "payment_method" => ["nullable", "string", "max:50"],
//         "items" => ["required", "array", "min:1"],
//         "items.*.product_id" => ["required", "exists:products,id"],
//         "items.*.price" => ["required", "numeric", "min:0"],
//         "items.*.quantity" => ["required", "integer", "min:1"],
//     ]);

//     return DB::transaction(function () use ($data) {
//         $subtotal = 0;
//         $itemsForCreate = [];

//         foreach ($data["items"] as $row) {
//             $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
//             if (!$product || $product->quantity < $row["quantity"]) {
//                 throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।"]]);
//             }
//             $lineTotal = bcmul($row["price"], $row["quantity"], 2);
//             $subtotal = bcadd($subtotal, $lineTotal, 2);
//             $product->decrement("quantity", $row["quantity"]);
//             $itemsForCreate[] = [
//                 "product_id" => $product->id, "price" => $row["price"], "quantity" => $row["quantity"],
//                 "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
//             ];
//         }

//         $discount = $data["discount"] ?? 0;
//         $shipping = $data["shipping"] ?? 0;
//         $total = bcadd($subtotal - $discount, $shipping, 2);
//         $paid = $data["paid"];
//         $due = max(0, (float) $total - (float) $paid);
        
//         // ২. গেস্ট কাস্টমারের জন্য বকেয়া চেক
//         if ($data['sale_type'] === 'guest' && $due > 0) {
//             throw ValidationException::withMessages([
//                 "paid" => ["গেস্ট কাস্টমারের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
//             ]);
//         }

//         $saleData = [
//             "customer_id" => $data["customer_id"] ?? null,
//             "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
//             "total" => $total, "paid" => $paid, "due" => $due,
//             "payment_method" => $data["payment_method"] ?? null,
//             "status" => $due > 0 ? "pending" : "paid",
//             "order_status" => $due > 0 ? 0 : 1,
//             "ddate" => $data["ddate"] ?? now()->toDateString(),
//             "srname" => auth()->user()->name, "user_id" => auth()->user()->id,
//             "is_dipu" => 1,
//         ];

//         // ৩. গেস্টের তথ্য যুক্ত করা
//         if ($data['sale_type'] === 'guest') {
//             $saleData['guest_name'] = $data['guest_name'];
//             $saleData['guest_phone'] = $data['guest_phone'];
//             $saleData['guest_sell_type'] = 1;

            
//         }

//         $sale = Sale::create($saleData);
//         $sale->items()->createMany($itemsForCreate);

//         return response()->json([
//             "status" => "success",
//             "sale_id" => $sale->id,
//             "redirect" => route("sales.show", $sale->id), // Normal Print URL
//             "pos_url"  => route("sales.pos.print", $sale->id), // POS Print URL (নতুন যুক্ত করা হয়েছে)
//         ]);
//     });
// }


public function isdipu(Request $request)
    {
        // ১. ভ্যালিডেশন 
        $data = $request->validate([
            "sale_type" => ["required", "in:customer,guest"],
            "customer_id" => ["required_if:sale_type,customer", "nullable", "exists:customers,id"],
            "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
            "guest_phone" => ["nullable", "string", "max:20"],
            "ddate" => ["nullable", "date"],
            "discount" => ["nullable", "numeric", "min:0"],
            "shipping" => ["nullable", "numeric", "min:0"],
            "paid" => ["required", "numeric", "min:0"],
            "payment_method" => ["nullable", "string", "max:50"],
            "items" => ["required", "array", "min:1"],
            "items.*.product_id" => ["required", "exists:products,id"],
            "items.*.price" => ["required", "numeric", "min:0"],
            "items.*.quantity" => ["required", "integer", "min:1"],
        ]);

        return DB::transaction(function () use ($data) {
            $subtotal = 0;
            $itemsForCreate = [];

            foreach ($data["items"] as $row) {
                $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
                
                // স্টক চেক করা হচ্ছে, কিন্তু এখান থেকে মাইনাস করা হবে না
                if (!$product || $product->quantity < $row["quantity"]) {
                    throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।"]]);
                }
                
                $lineTotal = bcmul($row["price"], $row["quantity"], 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);
                
                // NOTE: এখান থেকে $product->decrement() বাদ দেওয়া হয়েছে।

                $itemsForCreate[] = [
                    "product_id" => $product->id, "price" => $row["price"], "quantity" => $row["quantity"],
                    "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
                ];
            }

            $discount = $data["discount"] ?? 0;
            $shipping = $data["shipping"] ?? 0;
            $total = bcadd($subtotal - $discount, $shipping, 2);
            $paid = $data["paid"];
            $due = max(0, (float) $total - (float) $paid);
            
            // ২. গেস্ট কাস্টমারের জন্য বকেয়া চেক
            if ($data['sale_type'] === 'guest' && $due > 0) {
                throw ValidationException::withMessages([
                    "paid" => ["গেস্ট কাস্টমারের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
                ]);
            }

            $saleData = [
                "customer_id" => $data["customer_id"] ?? null,
                "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
                "total" => $total, "paid" => $paid, "due" => $due,
                "payment_method" => $data["payment_method"] ?? null,
                
                // ৩. স্ট্যাটাস পরিবর্তন করা হয়েছে
                "status" => "ordered",
                "order_status" => 3,
                
                "ddate" => $data["ddate"] ?? now()->toDateString(),
                "srname" => auth()->user()->name, 
                "user_id" => auth()->user()->id,
                "is_dipu" => 1,
            ];

            // ৪. গেস্টের তথ্য যুক্ত করা
            if ($data['sale_type'] === 'guest') {
                $saleData['guest_name'] = $data['guest_name'];
                $saleData['guest_phone'] = $data['guest_phone'];
                $saleData['guest_sell_type'] = 1;
            }

            $sale = Sale::create($saleData);
            $sale->items()->createMany($itemsForCreate);

            // ৫. রেসপন্সে ব্লুটুথ ইউআরএল যুক্ত করা হয়েছে
            return response()->json([
                "status" => "success",
                "sale_id" => $sale->id,
                "redirect" => route("sales.show", $sale->id), // Normal Print URL
                "pos_url"  => route("sales.pos.print", $sale->id), // POS Print URL 
                "bluetooth_print_url" => route("sales.bluetooth.print", $sale->id), // Bluetooth Print URL 
            ]);
        });
    }

public function show(Sale $sale)
{       
        $totalCustomerDue = null;
        $dueInvoiceCount = 0; 

        if ($sale->customer_id) {
            $sale->load("items.product", "customer","payments");
            $customerSales = Sale::where('customer_id', $sale->customer_id);
            $totalCustomerDue = $customerSales->sum('due');
            $dueInvoiceCount = $customerSales->where('due', '>', 0)->count();
            return view("sales.show", compact("sale", "totalCustomerDue", "dueInvoiceCount"));
        }elseif($sale->dealer_id){  
            $sale->load("items.product", "dealer", "payments");
            $dealerSales = Sale::where('dealer_id', $sale->dealer_id);
            $totalDealerDue = $dealerSales->sum('due');
            $dueInvoiceCount = $dealerSales->where('due', '>', 0)->count();
            return view("dealers.show", compact("sale", "totalDealerDue", "dueInvoiceCount"));
        }elseif($sale->guest_sell_type == 1){
            
            $sale->load("items.product", "payments");
            $customerSales = Sale::where('guest_sell_type', $sale->guest_sell_type);
            $totalCustomerDue = $customerSales->sum('due');
            $dueInvoiceCount = $customerSales->where('due', '>', 0)->count();
            return view("sales.show", compact("sale", "totalCustomerDue", "dueInvoiceCount"));

        }elseif($sale->guest_sell_type == 2){
            
            $sale->load("items.product", "payments");
            $dealerSales = Sale::where('guest_sell_type', $sale->guest_sell_type);
            $totalDealerDue = $dealerSales->sum('due');
            $dueInvoiceCount = $dealerSales->where('due', '>', 0)->count();
            return view("dealers.show", compact("sale", "totalDealerDue", "dueInvoiceCount"));
        } 
}

    public function dealershow(Sale $sale)
    {
        return redirect()->action([SaleController::class, 'show'], ['sale' => $sale->id]);
        // // ১. আগের মতোই প্রয়োজনীয় রিলেশনশিপ লোড করা
        // $sale->load("items.product", "dealer", "payments");

        // $totalDealerDue = null;
        // $dueInvoiceCount = 0; // ডিফল্ট মান

        // // ২. যদি সেলটি কোনো নিবন্ধিত ডিলারের হয়
        // if ($sale->dealer_id) {
        //     $dealerSales = Sale::where('dealer_id', $sale->dealer_id);
        //     $totalDealerDue = $dealerSales->sum('due');
        //     // *** নতুন: মোট বকেয়া ইনভয়েসের সংখ্যা গণনা ***
        //     $dueInvoiceCount = $dealerSales->where('due', '>', 0)->count();
        // }
        // // যদি সেলটি গেস্টের হয়, তাহলে এই ভেরিয়েবলগুলো null/0 থাকবে

        // // ৩. ভিউতে নতুন ভেরিয়েবলগুলো পাঠানো
        // return view("dealers.show", compact("sale", "totalDealerDue", "dueInvoiceCount"));

        
    }

  
    public function pending(Request $request)
    {
        $query = Sale::with(["customer", "dealer"])->where("order_status", 0);

        
        if ($request->filled("search_term")) {
            $term = $request->search_term;
            
            $query->where(function ($q) use ($term) {
                
                // ১. সরাসরি 'sales' টেবিলের কলামগুলোতে সার্চ
                $q->where("id", "like", "%{$term}%");

                // ২. 'customer' রিলেশনশিপের একাধিক কলামে একসাথে সার্চ
                $q->orWhereHas("customer", function ($subQuery) use ($term) {
                    $subQuery->where("name", "like", "%{$term}%")
                            ->orWhere("shop_name", "like", "%{$term}%"); // <-- এখানে orWhere ব্যবহার করা হয়েছে
                });

                // ৩. 'dealer' রিলেশনশিপের একাধিক কলামে একসাথে সার্চ
                $q->orWhereHas("dealer", function ($subQuery) use ($term) {
                    $subQuery->where("name", "like", "%{$term}%")
                            ->orWhere("pname", "like", "%{$term}%"); // <-- এখানে orWhere ব্যবহার করা হয়েছে
                });
                
            });
        }

        // 2. তারিখের কুইক ফিল্টার (দৈনিক, সাপ্তাহিক, মাসিক)
        if ($request->filled("filter")) {
            switch ($request->filter) {
                case "today":
                    $query->whereDate("created_at", Carbon::today());
                    break;
                case "this_week":
                    $query->whereBetween("created_at", [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case "this_month":
                    $query
                        ->whereMonth("created_at", Carbon::now()->month)
                        ->whereYear("created_at", Carbon::now()->year);
                    break;
            }
        }

        // 3. কাস্টম তারিখ ফিল্টার
        if ($request->filled("from") && $request->filled("to")) {
            $query->whereBetween("created_at", [$request->from, $request->to]);
        } 

        // 4. বিক্রয় টাইপ ফিল্টার (ডিলার/কাস্টমার)
        if ($request->filled("sale_type")) {
            if ($request->sale_type === "dealer") {
                $query->whereNotNull("dealer_id");
            } elseif ($request->sale_type === "customer") {
                $query->whereNotNull("customer_id");
            }
        }

        if (auth()->user()->role == 1 && $request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }

        $sales = $query->orderBy("id", "desc")->paginate(20); // প্রতি পেইজে ২০টি আইটেম
        // এডমিনের জন্য ভিউতে সব ব্যবহারকারীর তালিকা পাঠানো হচ্ছে
        $users = [];
        if (auth()->user()->role == 1) {
            $users = User::where('role', '!=', 3) ->orderBy("name", "asc")->get(["id", "name"]);
        }

        return view("sales.pending", compact("sales", "users"));
    }

    // app/Http/Controllers/SaleController.php

    public function downloadPendingPDF(Request $request)
    {
        $query = Sale::with(["customer", "dealer"])->where("order_status", 0);

        // pending() মেথড থেকে ফিল্টারিং লজিক কপি করে আনা হয়েছে
        if ($request->filled("search_term")) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where("id", "like", "%{$term}%")
                    ->orWhereHas("customer", function ($subQuery) use ($term) {
                        $subQuery->where("name", "like", "%{$term}%");
                    })
                    ->orWhereHas("dealer", function ($subQuery) use ($term) {
                        $subQuery->where("name", "like", "%{$term}%");
                    });
            });
        }
        if ($request->filled("filter")) {
            switch ($request->filter) {
                case "today":
                    $query->whereDate("created_at", Carbon::today());
                    break;
                case "this_week":
                    $query->whereBetween("created_at", [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case "this_month":
                    $query
                        ->whereMonth("created_at", Carbon::now()->month)
                        ->whereYear("created_at", Carbon::now()->year);
                    break;
            }
        }
        if ($request->filled("from") && $request->filled("to")) {
            $query->whereBetween("created_at", [$request->from, $request->to]);
        }
        if ($request->filled("sale_type")) {
            if ($request->sale_type === "dealer") {
                $query->whereNotNull("dealer_id");
            } elseif ($request->sale_type === "customer") {
                $query->whereNotNull("customer_id");
            }
        }

        // --- নতুন পরিবর্তন এখানে ---
        // ইউজার অনুযায়ী ফিল্টার (শুধুমাত্র এডমিনের জন্য)
        if (auth()->user()->role == 1 && $request->filled("user_id")) {
            $query->where("user_id", $request->user_id);
        }
        // --- পরিবর্তন শেষ ---

        // PDF এর জন্য সব ডেটা আনা হচ্ছে
        $sales = $query->orderBy("id", "desc")->get();
        $totals = [
            "total" => $sales->sum("total"),
            "paid" => $sales->sum("paid"),
            "due" => $sales->sum("due"),
        ];

        $filterInfo = [
            "from" => $request->from,
            "to" => $request->to,
            "filter" => $request->filter,
            "search_term" => $request->search_term,
            "sale_type" => $request->sale_type,
            "user_id" => $request->user_id, // PDF ভিউতে দেখানোর জন্য (ঐচ্ছিক)
        ];

        // যদি কোনো নির্দিষ্ট ইউজার সিলেক্ট করা হয়, তাহলে তার নামও PDF এ পাঠানো হচ্ছে
        if ($request->filled("user_id")) {
            $filterInfo["user_name"] =
                \App\Models\User::find($request->user_id)->name ?? "Unknown SR";
        }

        $pdf = PDF::loadView(
            "sales.pending_pdf",
            compact("sales", "totals", "filterInfo")
        );

        return $pdf
            ->setPaper("a4", "landscape")
            ->download("pending-orders-report.pdf");
    }
    public function confrim(Request $request)
    {
        $query = Sale::with(["customer", "dealer"])->where("order_status", 1);

        if ($request->filled("search_term")) {
            $term = $request->search_term;
            
            $query->where(function ($q) use ($term) {
                
                // ১. সরাসরি 'sales' টেবিলের কলামগুলোতে সার্চ
                $q->where("id", "like", "%{$term}%");

                // ২. 'customer' রিলেশনশিপের একাধিক কলামে একসাথে সার্চ
                $q->orWhereHas("customer", function ($subQuery) use ($term) {
                    $subQuery->where("name", "like", "%{$term}%")
                            ->orWhere("shop_name", "like", "%{$term}%"); // <-- এখানে orWhere ব্যবহার করা হয়েছে
                });

                // ৩. 'dealer' রিলেশনশিপের একাধিক কলামে একসাথে সার্চ
                $q->orWhereHas("dealer", function ($subQuery) use ($term) {
                    $subQuery->where("name", "like", "%{$term}%")
                            ->orWhere("pname", "like", "%{$term}%"); // <-- এখানে orWhere ব্যবহার করা হয়েছে
                });
                 
                // ৪. নতুন যুক্ত করা: গেস্ট কাস্টমারের নামেও সার্চ
                $q->orWhere('guest_name', 'like', "%{$term}%");
            });
        }

        // 2. তারিখের কুইক ফিল্টার (দৈনিক, সাপ্তাহিক, মাসিক)
        if ($request->filled("filter")) {
            switch ($request->filter) {
                case "today":
                    $query->whereDate("created_at", Carbon::today());
                    break;
                case "this_week":
                    $query->whereBetween("created_at", [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case "this_month":
                    $query
                        ->whereMonth("created_at", Carbon::now()->month)
                        ->whereYear("created_at", Carbon::now()->year);
                    break;
            }
        }

        // 3. কাস্টম তারিখ ফিল্টার
        if ($request->filled("from") && $request->filled("to")) {
            $query->whereBetween("created_at", [$request->from, $request->to]);
        }

        // 4. বিক্রয় টাইপ ফিল্টার (ডিলার/কাস্টমার)
        if ($request->filled("sale_type")) {
            if ($request->sale_type === "dealer") {
                $query->whereNotNull("dealer_id");
            } elseif ($request->sale_type === "customer") {
                $query->whereNotNull("customer_id");
            }
        }

        $sales = $query->orderBy("id", "desc")->paginate(20); // প্রতি পেইজে ২০টি আইটেম

        return view("sales.confrim", compact("sales"));
    }

    public function downloadConfrimPDF(Request $request)
    {
        $query = Sale::with(["customer", "dealer"])->where("order_status", 1);

        // confrim() মেথড থেকে ফিল্টারিং লজিক কপি করে আনা হয়েছে
        if ($request->filled("search_term")) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where("id", "like", "%{$term}%")
                    ->orWhereHas("customer", function ($subQuery) use ($term) {
                        $subQuery->where("name", "like", "%{$term}%");
                    })
                    ->orWhereHas("dealer", function ($subQuery) use ($term) {
                        $subQuery->where("name", "like", "%{$term}%");
                    });
            });
        }
        if ($request->filled("filter")) {
            switch ($request->filter) {
                case "today":
                    $query->whereDate("created_at", Carbon::today());
                    break;
                case "this_week":
                    $query->whereBetween("created_at", [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case "this_month":
                    $query
                        ->whereMonth("created_at", Carbon::now()->month)
                        ->whereYear("created_at", Carbon::now()->year);
                    break;
            }
        }
        if ($request->filled("from") && $request->filled("to")) {
            $query->whereBetween("created_at", [$request->from, $request->to]);
        }
        if ($request->filled("sale_type")) {
            if ($request->sale_type === "dealer") {
                $query->whereNotNull("dealer_id");
            } elseif ($request->sale_type === "customer") {
                $query->whereNotNull("customer_id");
            }
        }

        // PDF এর জন্য সব ডেটা আনা হচ্ছে
        $sales = $query->orderBy("id", "desc")->get();
        $totals = [
            "total" => $sales->sum("total"),
            "paid" => $sales->sum("paid"),
            "due" => $sales->sum("due"),
        ];

        $filterInfo = [
            "from" => $request->from,
            "to" => $request->to,
            "filter" => $request->filter,
            "search_term" => $request->search_term,
            "sale_type" => $request->sale_type,
        ];

        // PDF তৈরি করার জন্য একটি আলাদা ভিউ ফাইল ব্যবহার করা হচ্ছে
        // return view("sales.confrim_pdf", compact("sales", "totals", "filterInfo"));
        $pdf = PDF::loadView(
            "sales.confrim_pdf",
            compact("sales", "totals", "filterInfo")
        );

        return $pdf->download("confrim-orders-report.pdf");
    }

    public function complete($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->order_status = 1;
        $sale->paid = $sale->total;
        $sale->due = 0;
        $sale->save();

        return redirect()
            ->back()
            ->with("success", "Order marked as completed!");
    }

    public function edit(Sale $sale)
    {
        // পুরোনো সেল + আইটেম + কাস্টমার
        $sale->load(["items.product", "customer"]);

        // JS-এর জন্য কার্ট ইনিশিয়াল ডেটা
        $initialCart = $sale->items
            ->map(function ($it) {
                return [
                    "item_id" => $it->id, // sale_items id (চিন্হিত রাখতে)
                    "id" => $it->product_id, // product id (আগের POS কোডে id নামে ব্যবহার হচ্ছে)
                    "product_id" => $it->product_id,
                    "name" => $it->product->name,
                    "price" => (float) $it->price,
                    "qty" => (int) $it->quantity,
                ];
            })
            ->values();

        return view("sales.edit", [
            "sale" => $sale,
            "initialCart" => $initialCart,
        ]);
    }
    public function delaredit(Sale $sale)
    {
        // পুরোনো সেল + আইটেম + কাস্টমার
        $sale->load(["items.product", "dealer"]);

        // JS-এর জন্য কার্ট ইনিশিয়াল ডেটা
        $initialCart = $sale->items
            ->map(function ($it) {
                return [
                    "item_id" => $it->id, // sale_items id (চিন্হিত রাখতে)
                    "id" => $it->product_id, // product id (আগের POS কোডে id নামে ব্যবহার হচ্ছে)
                    "product_id" => $it->product_id,
                    "name" => $it->product->name,
                    "price" => (float) $it->price,
                    "qty" => (int) $it->quantity,
                ];
            })
            ->values();

        return view("dealers.edit", [
            "sale" => $sale,
            "initialCart" => $initialCart,
        ]);
    }

    // public function update(Request $request, Sale $sale)
    // {
    //     $data = $request->validate([
    //         "ddate" => ["nullable", "date"],
    //         "discount" => ["nullable", "numeric", "min:0"],
    //         "tax" => ["nullable", "numeric", "min:0"],
    //         "shipping" => ["nullable", "numeric", "min:0"],
    //         "paid" => ["required", "numeric", "min:0"],
    //         "payment_method" => ["nullable", "string", "max:50"],
    //         "note" => ["nullable", "string", "max:1000"],

    //         // কার্ট
    //         "items" => ["required", "array", "min:1"],
    //         "items.*.product_id" => ["required", "exists:products,id"],
    //         "items.*.price" => ["required", "numeric", "min:0"],
    //         "items.*.quantity" => ["required", "integer", "min:1"],
    //         // পুরোনো sale_items থাকলে তা item_id দিয়ে আসতে পারে (optional)
    //         "items.*.item_id" => ["nullable", "integer"],
    //     ]);

    //     return DB::transaction(function () use ($sale, $data) {
    //         // আগের আইটেমগুলো ইনডেক্স করে নেই (product_id => quantity)
    //         $oldItems = $sale
    //             ->items()
    //             ->lockForUpdate()
    //             ->get(); // লকড রিড
    //         $oldByProduct = [];
    //         foreach ($oldItems as $it) {
    //             $oldByProduct[$it->product_id] = [
    //                 "item_id" => $it->id,
    //                 "quantity" => (int) $it->quantity,
    //             ];
    //         }

    //         // নতুন ইনপুট ম্যাপ (product_id => data)
    //         $newByProduct = [];
    //         foreach ($data["items"] as $row) {
    //             $pid = (int) $row["product_id"];
    //             if (!isset($newByProduct[$pid])) {
    //                 $newByProduct[$pid] = [
    //                     "price" => (float) $row["price"],
    //                     "quantity" => (int) $row["quantity"],
    //                 ];
    //             } else {
    //                 // যদি একই প্রোডাক্ট একাধিকবার আসে, qty অ্যাড করে দেই
    //                 $newByProduct[$pid]["quantity"] += (int) $row["quantity"];
    //             }
    //         }

    //         // স্টক ডেল্টা অ্যাপ্লাই: newQty - oldQty
    //         foreach ($newByProduct as $pid => $ndata) {
    //             $newQty = $ndata["quantity"];
    //             $oldQty = $oldByProduct[$pid]["quantity"] ?? 0;
    //             $delta = $newQty - $oldQty;

    //             if ($delta === 0) {
    //                 continue;
    //             }

    //             $product = Product::where("id", $pid)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if ($delta > 0) {
    //                 // আগের চেয়ে বাড়ছে → স্টক কমাতে হবে
    //                 if ($product->quantity < $delta) {
    //                     throw \Illuminate\Validation\ValidationException::withMessages(
    //                         [
    //                             "items" => [
    //                                 "{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।",
    //                             ],
    //                         ]
    //                     );
    //                 }
    //                 $product->decrement("quantity", $delta);
    //             } else {
    //                 // কমছে → স্টক ফেরত দিতে হবে
    //                 $product->increment("quantity", abs($delta));
    //             }
    //         }

    //         // যেসব পুরোনো প্রোডাক্ট নতুন লিস্টে নেই → সম্পূর্ণ রিমুভ + স্টক ফেরত
    //         foreach ($oldByProduct as $pid => $ov) {
    //             if (!isset($newByProduct[$pid])) {
    //                 // পুরো লাইন বাদ → স্টক back
    //                 $product = Product::where("id", $pid)
    //                     ->lockForUpdate()
    //                     ->first();
    //                 if ($product) {
    //                     $product->increment("quantity", $ov["quantity"]);
    //                 }
    //             }
    //         }

    //         // sale_items টেবিলে আপসার্ট (সিম্পল: পুরোনো সব ডিলিট করে আবার ইনসার্ট করা)
    //         $sale->items()->delete();
    //         $subtotal = 0;
    //         $rowsForCreate = [];

    //         foreach ($newByProduct as $pid => $ndata) {
    //             $lineTotal = round($ndata["price"] * $ndata["quantity"], 2);
    //             $subtotal += $lineTotal;

    //             $rowsForCreate[] = [
    //                 "product_id" => $pid,
    //                 "price" => $ndata["price"],
    //                 "quantity" => $ndata["quantity"],
    //                 "total" => $lineTotal,
    //                 "created_at" => now(),
    //                 "updated_at" => now(),
    //             ];
    //         }
    //         $sale->items()->createMany($rowsForCreate);

    //         $discount = (float) ($data["discount"] ?? 0);
    //         $tax = (float) ($data["tax"] ?? 0);
    //         $shipping = (float) ($data["shipping"] ?? 0);

    //         if ($discount > $subtotal) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 "discount" => ["Discount cannot exceed subtotal."],
    //             ]);
    //         }

    //         $total = round($subtotal - $discount + $tax + $shipping, 2);
    //         $paid = (float) $data["paid"];
    //         if ($paid > $total) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 "paid" => ["Paid cannot exceed total."],
    //             ]);
    //         }
    //         $due = max(0, $total - $paid);

    //         $sale->update([
    //             "subtotal" => $subtotal,
    //             "discount" => $discount,
    //             "tax" => $tax,
    //             "shipping" => $shipping,
    //             "total" => $total,
    //             "paid" => $paid,
    //             "due" => $due,
    //             "payment_method" => $data["payment_method"] ?? null,
    //             "note" => $data["note"] ?? null,
    //             "status" => $due > 0 ? "pending" : "paid",
    //             "order_status" => $due > 0 ? 0 : 1,
    //             "ddate" => $data["ddate"] ?? $sale->ddate,
    //         ]);

    //         return response()->json([
    //             "status" => "success",
    //             "sale_id" => $sale->id,
    //             "redirect" => route("sales.pending"),
    //         ]);
    //     });
    // }

    
    public function update(Request $request, Sale $sale)
{
    $data = $request->validate([
        "ddate" => ["nullable", "date"],
        "discount" => ["nullable", "numeric", "min:0"],
        "tax" => ["nullable", "numeric", "min:0"],
        "shipping" => ["nullable", "numeric", "min:0"],
        "paid" => ["required", "numeric", "min:0"],
        "payment_method" => ["nullable", "string", "max:50"],
        "note" => ["nullable", "string", "max:1000"],

        // কার্ট
        "items" => ["required", "array", "min:1"],
        "items.*.product_id" => ["required", "exists:products,id"],
        "items.*.price" => ["required", "numeric", "min:0"],
        "items.*.quantity" => ["required", "integer", "min:1"],

        // পুরোনো sale_items থাকলে item_id দিয়ে আসতে পারে (optional)
        "items.*.item_id" => ["nullable", "integer"],
    ]);

    return DB::transaction(function () use ($sale, $data) {

        $oldItems = $sale
            ->items()
            ->lockForUpdate()
            ->get();

        $oldByProduct = [];

        foreach ($oldItems as $item) {
            $oldByProduct[$item->product_id] = [
                "item_id" => $item->id,
                "quantity" => (int) $item->quantity,
            ];
        }

        $newByProduct = [];

        foreach ($data["items"] as $row) {

            $pid = (int) $row["product_id"];

            if (!isset($newByProduct[$pid])) {

                $newByProduct[$pid] = [
                    "price" => (float) $row["price"],
                    "quantity" => (int) $row["quantity"],
                ];

            } else {

                $newByProduct[$pid]["quantity"] += (int) $row["quantity"];
            }
        }

        
        if ((int) $sale->order_status !== 3) {

            
            foreach ($newByProduct as $pid => $ndata) {

                $newQty = (int) $ndata["quantity"];
                $oldQty = $oldByProduct[$pid]["quantity"] ?? 0;

                // Positive হলে stock কমবে
                // Negative হলে stock বাড়বে
                $delta = $newQty - $oldQty;

                if ($delta === 0) {
                    continue;
                }

                $product = Product::where("id", $pid)
                    ->lockForUpdate()
                    ->firstOrFail();

                
                if ($delta > 0) {

                    if ($product->quantity < $delta) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items" => [
                                "{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।",
                            ],
                        ]);
                    }

                    $product->decrement("quantity", $delta);
                }

                
                else {

                    $product->increment("quantity", abs($delta));
                }
            }

            foreach ($oldByProduct as $pid => $oldItem) {

                if (!isset($newByProduct[$pid])) {

                    $product = Product::where("id", $pid)
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->increment(
                            "quantity",
                            $oldItem["quantity"]
                        );
                    }
                }
            }
        }

        $sale->items()->delete();

        $subtotal = 0;
        $rowsForCreate = [];

        foreach ($newByProduct as $pid => $ndata) {

            $lineTotal = round(
                $ndata["price"] * $ndata["quantity"],
                2
            );

            $subtotal += $lineTotal;

            $rowsForCreate[] = [
                "product_id" => $pid,
                "price" => $ndata["price"],
                "quantity" => $ndata["quantity"],
                "total" => $lineTotal,
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        $sale->items()->createMany($rowsForCreate);

        $discount = (float) ($data["discount"] ?? 0);
        $tax = (float) ($data["tax"] ?? 0);
        $shipping = (float) ($data["shipping"] ?? 0);

        if ($discount > $subtotal) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                "discount" => [
                    "Discount cannot exceed subtotal.",
                ],
            ]);
        }

        $total = round(
            $subtotal - $discount + $tax + $shipping,
            2
        );

        $paid = (float) $data["paid"];

        if ($paid > $total) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                "paid" => [
                    "Paid cannot exceed total.",
                ],
            ]);
        }


        $due = max(0, $total - $paid);

        $sale->update([
            "subtotal" => $subtotal,
            "discount" => $discount,
            "tax" => $tax,
            "shipping" => $shipping,
            "total" => $total,
            "paid" => $paid,
            "due" => $due,
            "payment_method" => $data["payment_method"] ?? null,
            "note" => $data["note"] ?? null,

            "status" => $due > 0 ? "pending" : "paid",

            
            // "order_status" => $due > 0 ? 3 : 1,
            "order_status" => 3,


            "ddate" => $data["ddate"] ?? $sale->ddate,
        ]);

        return response()->json([
            "status" => "success",
            "sale_id" => $sale->id,
            "redirect" => route("sales.pending"),
        ]);
    });
}
    // public function dupdate(Request $request, Sale $sale)
    // {
    //     $data = $request->validate([
    //         "ddate" => ["nullable", "date"],
    //         "discount" => ["nullable", "numeric", "min:0"],
    //         "tax" => ["nullable", "numeric", "min:0"],
    //         "shipping" => ["nullable", "numeric", "min:0"],
    //         "paid" => ["required", "numeric", "min:0"],
    //         "payment_method" => ["nullable", "string", "max:50"],
    //         "note" => ["nullable", "string", "max:1000"],

    //         // কার্ট
    //         "items" => ["required", "array", "min:1"],
    //         "items.*.product_id" => ["required", "exists:products,id"],
    //         "items.*.price" => ["required", "numeric", "min:0"],
    //         "items.*.quantity" => ["required", "integer", "min:1"],
    //         // পুরোনো sale_items থাকলে তা item_id দিয়ে আসতে পারে (optional)
    //         "items.*.item_id" => ["nullable", "integer"],
    //     ]);

    //     return DB::transaction(function () use ($sale, $data) {
    //         // আগের আইটেমগুলো ইনডেক্স করে নেই (product_id => quantity)
    //         $oldItems = $sale
    //             ->items()
    //             ->lockForUpdate()
    //             ->get(); // লকড রিড
    //         $oldByProduct = [];
    //         foreach ($oldItems as $it) {
    //             $oldByProduct[$it->product_id] = [
    //                 "item_id" => $it->id,
    //                 "quantity" => (int) $it->quantity,
    //             ];
    //         }

    //         // নতুন ইনপুট ম্যাপ (product_id => data)
    //         $newByProduct = [];
    //         foreach ($data["items"] as $row) {
    //             $pid = (int) $row["product_id"];
    //             if (!isset($newByProduct[$pid])) {
    //                 $newByProduct[$pid] = [
    //                     "price" => (float) $row["price"],
    //                     "quantity" => (int) $row["quantity"],
    //                 ];
    //             } else {
    //                 // যদি একই প্রোডাক্ট একাধিকবার আসে, qty অ্যাড করে দেই
    //                 $newByProduct[$pid]["quantity"] += (int) $row["quantity"];
    //             }
    //         }

    //         // স্টক ডেল্টা অ্যাপ্লাই: newQty - oldQty
    //         foreach ($newByProduct as $pid => $ndata) {
    //             $newQty = $ndata["quantity"];
    //             $oldQty = $oldByProduct[$pid]["quantity"] ?? 0;
    //             $delta = $newQty - $oldQty;

    //             if ($delta === 0) {
    //                 continue;
    //             }

    //             $product = Product::where("id", $pid)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if ($delta > 0) {
    //                 // আগের চেয়ে বাড়ছে → স্টক কমাতে হবে
    //                 if ($product->quantity < $delta) {
    //                     throw \Illuminate\Validation\ValidationException::withMessages(
    //                         [
    //                             "items" => [
    //                                 "{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।",
    //                             ],
    //                         ]
    //                     );
    //                 }
    //                 $product->decrement("quantity", $delta);
    //             } else {
    //                 // কমছে → স্টক ফেরত দিতে হবে
    //                 $product->increment("quantity", abs($delta));
    //             }
    //         }

    //         // যেসব পুরোনো প্রোডাক্ট নতুন লিস্টে নেই → সম্পূর্ণ রিমুভ + স্টক ফেরত
    //         foreach ($oldByProduct as $pid => $ov) {
    //             if (!isset($newByProduct[$pid])) {
    //                 // পুরো লাইন বাদ → স্টক back
    //                 $product = Product::where("id", $pid)
    //                     ->lockForUpdate()
    //                     ->first();
    //                 if ($product) {
    //                     $product->increment("quantity", $ov["quantity"]);
    //                 }
    //             }
    //         }

    //         // sale_items টেবিলে আপসার্ট (সিম্পল: পুরোনো সব ডিলিট করে আবার ইনসার্ট করা)
    //         $sale->items()->delete();
    //         $subtotal = 0;
    //         $rowsForCreate = [];

    //         foreach ($newByProduct as $pid => $ndata) {
    //             $lineTotal = round($ndata["price"] * $ndata["quantity"], 2);
    //             $subtotal += $lineTotal;

    //             $rowsForCreate[] = [
    //                 "product_id" => $pid,
    //                 "price" => $ndata["price"],
    //                 "quantity" => $ndata["quantity"],
    //                 "total" => $lineTotal,
    //                 "created_at" => now(),
    //                 "updated_at" => now(),
    //             ];
    //         }
    //         $sale->items()->createMany($rowsForCreate);

    //         $discount = (float) ($data["discount"] ?? 0);
    //         $tax = (float) ($data["tax"] ?? 0);
    //         $shipping = (float) ($data["shipping"] ?? 0);

    //         if ($discount > $subtotal) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 "discount" => ["Discount cannot exceed subtotal."],
    //             ]);
    //         }

    //         $total = round($subtotal - $discount + $tax + $shipping, 2);
    //         $paid = (float) $data["paid"];
    //         if ($paid > $total) {
    //             throw \Illuminate\Validation\ValidationException::withMessages([
    //                 "paid" => ["Paid cannot exceed total."],
    //             ]);
    //         }
    //         $due = max(0, $total - $paid);

    //         $sale->update([
    //             "subtotal" => $subtotal,
    //             "discount" => $discount,
    //             "tax" => $tax,
    //             "shipping" => $shipping,
    //             "total" => $total,
    //             "paid" => $paid,
    //             "due" => $due,
    //             "payment_method" => $data["payment_method"] ?? null,
    //             "note" => $data["note"] ?? null,
    //             "status" => $due > 0 ? "pending" : "paid",
    //             "order_status" => $due > 0 ? 0 : 1,
    //             "ddate" => $data["ddate"] ?? $sale->ddate,
    //         ]);

    //         return response()->json([
    //             "status" => "success",
    //             "sale_id" => $sale->id,
    //             "redirect" => route("sales.pending"),
    //         ]);
    //     });
    // }

   
    public function dupdate(Request $request, Sale $sale)
{
    $data = $request->validate([
        "ddate" => ["nullable", "date"],
        "discount" => ["nullable", "numeric", "min:0"],
        "tax" => ["nullable", "numeric", "min:0"],
        "shipping" => ["nullable", "numeric", "min:0"],
        "paid" => ["required", "numeric", "min:0"],
        "payment_method" => ["nullable", "string", "max:50"],
        "note" => ["nullable", "string", "max:1000"],

        // কার্ট
        "items" => ["required", "array", "min:1"],
        "items.*.product_id" => ["required", "exists:products,id"],
        "items.*.price" => ["required", "numeric", "min:0"],
        "items.*.quantity" => ["required", "integer", "min:1"],

        // পুরোনো sale_items থাকলে item_id দিয়ে আসতে পারে (optional)
        "items.*.item_id" => ["nullable", "integer"],
    ]);

    return DB::transaction(function () use ($sale, $data) {

        /*
        |--------------------------------------------------------------------------
        | পুরোনো Sale Items
        |--------------------------------------------------------------------------
        */
        $oldItems = $sale
            ->items()
            ->lockForUpdate()
            ->get();

        $oldByProduct = [];

        foreach ($oldItems as $item) {
            $oldByProduct[$item->product_id] = [
                "item_id" => $item->id,
                "quantity" => (int) $item->quantity,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | নতুন Input Items
        |--------------------------------------------------------------------------
        | একই Product একাধিকবার এলে quantity যোগ হবে
        |--------------------------------------------------------------------------
        */
        $newByProduct = [];

        foreach ($data["items"] as $row) {

            $pid = (int) $row["product_id"];

            if (!isset($newByProduct[$pid])) {

                $newByProduct[$pid] = [
                    "price" => (float) $row["price"],
                    "quantity" => (int) $row["quantity"],
                ];

            } else {

                $newByProduct[$pid]["quantity"] += (int) $row["quantity"];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STOCK ADJUSTMENT
        |--------------------------------------------------------------------------
        | পুরোনো order_status == 3 হলে কোনো stock adjustment হবে না।
        |
        | অর্থাৎ:
        | - decrement হবে না
        | - increment হবে না
        | - কোনো removed product-এর stock ফেরত যাবে না
        |--------------------------------------------------------------------------
        */
        if ((int) $sale->order_status !== 3) {

            /*
            |--------------------------------------------------------------------------
            | নতুন quantity বনাম পুরোনো quantity অনুযায়ী Stock Delta
            |--------------------------------------------------------------------------
            */
            foreach ($newByProduct as $pid => $ndata) {

                $newQty = (int) $ndata["quantity"];
                $oldQty = $oldByProduct[$pid]["quantity"] ?? 0;

                $delta = $newQty - $oldQty;

                if ($delta === 0) {
                    continue;
                }

                $product = Product::where("id", $pid)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Quantity বৃদ্ধি পেয়েছে → Stock কমবে
                |--------------------------------------------------------------------------
                */
                if ($delta > 0) {

                    if ($product->quantity < $delta) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items" => [
                                "{$product->name} স্টকে পর্যাপ্ত পণ্য নেই।",
                            ],
                        ]);
                    }

                    $product->decrement("quantity", $delta);
                }

                /*
                |--------------------------------------------------------------------------
                | Quantity কমেছে → Stock ফেরত যাবে
                |--------------------------------------------------------------------------
                */
                else {

                    $product->increment(
                        "quantity",
                        abs($delta)
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | পুরোনো Product নতুন Cart-এ নেই
            |--------------------------------------------------------------------------
            | পুরো Item remove হলে পুরোনো quantity Stock-এ ফেরত যাবে
            |--------------------------------------------------------------------------
            */
            foreach ($oldByProduct as $pid => $oldItem) {

                if (!isset($newByProduct[$pid])) {

                    $product = Product::where("id", $pid)
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->increment(
                            "quantity",
                            $oldItem["quantity"]
                        );
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sale Items Update
        |--------------------------------------------------------------------------
        | পুরোনো Items Delete করে নতুন Items Insert
        |--------------------------------------------------------------------------
        */
        $sale->items()->delete();

        $subtotal = 0;
        $rowsForCreate = [];

        foreach ($newByProduct as $pid => $ndata) {

            $lineTotal = round(
                $ndata["price"] * $ndata["quantity"],
                2
            );

            $subtotal += $lineTotal;

            $rowsForCreate[] = [
                "product_id" => $pid,
                "price" => $ndata["price"],
                "quantity" => $ndata["quantity"],
                "total" => $lineTotal,
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        $sale->items()->createMany($rowsForCreate);

        /*
        |--------------------------------------------------------------------------
        | Discount / Tax / Shipping
        |--------------------------------------------------------------------------
        */
        $discount = (float) ($data["discount"] ?? 0);
        $tax = (float) ($data["tax"] ?? 0);
        $shipping = (float) ($data["shipping"] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Discount Validation
        |--------------------------------------------------------------------------
        */
        if ($discount > $subtotal) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                "discount" => [
                    "Discount cannot exceed subtotal.",
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */
        $total = round(
            $subtotal - $discount + $tax + $shipping,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Paid Validation
        |--------------------------------------------------------------------------
        */
        $paid = (float) $data["paid"];

        if ($paid > $total) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                "paid" => [
                    "Paid cannot exceed total.",
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Due
        |--------------------------------------------------------------------------
        */
        $due = max(0, $total - $paid);

        /*
        |--------------------------------------------------------------------------
        | Sale Update
        |--------------------------------------------------------------------------
        */
        $sale->update([
            "subtotal" => $subtotal,
            "discount" => $discount,
            "tax" => $tax,
            "shipping" => $shipping,
            "total" => $total,
            "paid" => $paid,
            "due" => $due,
            "payment_method" => $data["payment_method"] ?? null,
            "note" => $data["note"] ?? null,
            "status" => $due > 0 ? "pending" : "paid",

            // Due থাকলে 0, আর Due না থাকলে 1
            // "order_status" => $due > 0 ? 0 : 1,
            "order_status" => 3,

            "ddate" => $data["ddate"] ?? $sale->ddate,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */
        return response()->json([
            "status" => "success",
            "sale_id" => $sale->id,
            "redirect" => route("sales.pending"),
        ]);
    });
}
    public function destroy($id)
    {
        $sale = Sale::with(["items", "payments"])->find($id);

        if (!$sale) {
            return redirect()
                ->back()
                ->with("error", "Sale not found.");
        }

        if (auth()->user()->role == 1 || auth()->user()->id == $sale->user_id) {
            // স্টক ফেরত দিন (প্রতিটি পণ্য আবার quantity তে যোগ করুন)
            if($sale->order_status != 3){
                foreach ($sale->items as $item) {
                    $product = $item->product; // ধরে নিচ্ছি relation আছে: SaleItem belongsTo Product
                    if ($product) {
                            $product->increment("quantity", $item->quantity);
                    }
                }
            }  

            // Sale Items ডিলিট
            if ($sale->items()->exists()) {
                $sale->items()->delete();
            }

            // Payments ডিলিট
            if ($sale->payments()->exists()) {
                $sale->payments()->delete();
            }

            // Finally delete sale
            $sale->delete();

            return redirect()
                ->back()
                ->with("success", "পণ্য ডিলেট করা হয়েছে এবং স্টক আপডেট হয়েছে।");
        }

        return redirect()
            ->back()
            ->with(
                "error",
                "এই ইনভয়েস আপনি তৈরী করেননি। আপনি এটি ডিলেট করতে পারবেন না।"
            );
    }

    public function dealerSalePage(Request $request)
    {
        // if($request->host() == 'mb.gffoodandbeverage.com'){
        //     return view('demo');
        // }
        
        $dealers = Dealer::orderBy("name", "asc")->get([
            "id",
            "name",
            "phone",
            "address",
            "pname",
        ]);
        return view("dealers.sale", ["dealers" => $dealers]);
    }

  

//     public function storeDealerSale(Request $request)
// {
//     // ১. ভ্যালিডেশন আপডেট করা হয়েছে
//     $data = $request->validate([
//         "sale_type" => ["required", "in:dealer,guest"], // কোন ধরনের বিক্রি
//         "dealer_id" => ["required_if:sale_type,dealer", "nullable", "exists:dealers,id"],
//         "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
//         "guest_phone" => ["nullable", "string", "max:20"],
//         "discount" => ["nullable", "numeric", "min:0"],
//         "shipping" => ["nullable", "numeric", "min:0"],
//         "paid" => ["required", "numeric", "min:0"],
//         "ddate" => ["nullable", "date"],
//         "items" => ["required", "array", "min:1"],
//         "items.*.product_id" => ["required", "exists:products,id"],
//         "items.*.quantity" => ["required", "integer", "min:1"],
//     ]);

//     return DB::transaction(function () use ($data) {
//         $subtotal = 0;
//         $itemsForCreate = [];

//         foreach ($data["items"] as $row) {
//             $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
//             if (!$product || $product->quantity < $row["quantity"]) {
//                 throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত নেই।"]]);
//             }
//             $lineTotal = bcmul($product->dp, $row["quantity"], 2);
//             $subtotal = bcadd($subtotal, $lineTotal, 2);
//             $product->decrement("quantity", $row["quantity"]);
//             $itemsForCreate[] = [
//                 "product_id" => $product->id, "price" => $product->dp, "quantity" => $row["quantity"],
//                 "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
//             ];
//         }

//         $discount = $data["discount"] ?? 0;
//         $shipping = $data["shipping"] ?? 0;
//         $total = $subtotal - $discount + $shipping;
//         $paid = $data["paid"];
//         $due = max(0, $total - $paid);

//         // ২. গেস্ট বিক্রির জন্য বকেয়া চেক
//         if ($data['sale_type'] === 'guest' && $due > 0) {
//             throw ValidationException::withMessages([
//                 "paid" => ["গেস্টের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
//             ]);
//         }
        
//         $saleData = [
//             "dealer_id" => $data["dealer_id"] ?? null,
//             "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
//             "total" => $total, "paid" => $paid, "due" => $due,
//             "status" => $due > 0 ? "pending" : "paid",
//             "order_status" => $due > 0 ? 0 : 1,
//             "ddate" => $data["ddate"] ?? now()->toDateString(),
//             "srname" => auth()->user()->name, "user_id" => auth()->user()->id,
//         ];

//         // ৩. গেস্টের তথ্য যুক্ত করা
//         if ($data['sale_type'] === 'guest') {
//             $saleData['guest_name'] = $data['guest_name'];
//             $saleData['guest_phone'] = $data['guest_phone'];
//             $saleData['guest_sell_type'] = 2;

//         }

//         $sale = Sale::create($saleData);
//         $sale->items()->createMany($itemsForCreate);
        
//         // পেমেন্ট রেকর্ড (ঐচ্ছিক)
//         if ($paid > 0) {
//             $sale->payments()->create(["amount" => $paid, "method" => "cash", "paid_at" => now()]);
//         }

//         return response()->json([
//             "status" => "success",
//             "sale_id" => $sale->id,
//             "redirect" => route("sales.show", $sale->id), // Normal Print URL
//             "pos_url"  => route("sales.pos.print", $sale->id), // POS Print URL (নতুন যুক্ত করা হয়েছে)
//             "bluetooth_url" => route("sales.bluetooth.print", $sale->id), // Bluetooth Print URL (নতুন যুক্ত করা হয়েছে)
//         ]);
//     });
//     }

    // public function allcustomerdues(Request $request)
    // {
    //     $query = Customer::query()->whereHas("sales", function ($q) {
    //         $q->where("due", ">", 0);
    //     });

    //     // সার্চ
    //     if ($request->filled("search")) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             // **সংশোধন:** এখানে টেবিলের নাম (customers.id) উল্লেখ করে দেওয়া হয়েছে
    //             $q->where("customers.id", "like", "%{$search}%")
    //                 ->orWhere("name", "like", "%{$search}%")
    //                 ->orWhere("number", "like", "%{$search}%")
    //                 ->orWhere("shop_name", "like", "%{$search}%");
    //         });
    //     } 

    //     // সর্টিং এর জন্য একটি সাব-কোয়েরি ব্যবহার করা হচ্ছে
    //     $dueSumSubquery = \App\Models\Sale::selectRaw("sum(due)")
    //         ->whereColumn("sales.customer_id", "customers.id")
    //         ->where("due", ">", 0);

    //     if ($request->get("sort") == "asc") {
    //         $query->orderBy($dueSumSubquery, "asc");
    //     } else {
    //         $query->orderBy($dueSumSubquery, "desc");
    //     }

    //     $customers = $query->paginate(30)->withQueryString();

    //     // প্রতিটি কাস্টমারের জন্য অতিরিক্ত তথ্য যোগ করা
    //     $customers->getCollection()->transform(function ($customer) {
    //         $sales = $customer
    //             ->sales()
    //             ->where("due", ">", 0)
    //             ->get();
    //         $customer->total_due = $sales->sum("due");
    //         $customer->total_invoices = $sales->count();
    //         unset($customer->sales);
    //         return $customer;
    //     });

    //     if ($request->ajax()) {
    //         // সারসংক্ষেপের জন্য কোয়েরি
    //         $summaryQuery = Customer::query()->whereHas(
    //             "sales",
    //             fn($q) => $q->where("due", ">", 0)
    //         );

    //         if ($request->filled("search")) {
    //             $search = $request->search;
    //             $summaryQuery->where(function ($q) use ($search) {
    //                 // **সংশোধন:** এখানেও টেবিলের নাম উল্লেখ করা হয়েছে
    //                 $q->where("customers.id", "like", "%{$search}%")
    //                     ->orWhere("name", "like", "%{$search}%")
    //                     ->orWhere("number", "like", "%{$search}%");
    //             });
    //         }

    //         $totalDueAmount = $summaryQuery
    //             ->join("sales", "customers.id", "=", "sales.customer_id")
    //             ->where("sales.due", ">", 0)
    //             ->sum("sales.due");
    //         $dueCustomersCount = $summaryQuery
    //             ->distinct("customers.id")
    //             ->count("customers.id");

    //         return response()->json([
    //             "customers" => $customers,
    //             "summary" => [
    //                 "dueCustomersCount" => $dueCustomersCount,
    //                 "totalDueAmount" => number_format($totalDueAmount, 2),
    //             ],
    //         ]);
    //     }

    //     return view("sales.allcustomerdues", compact("customers"));
    // }


    public function storeDealerSale(Request $request)
    {
        // ১. ভ্যালিডেশন 
        $data = $request->validate([
            "sale_type" => ["required", "in:dealer,guest"], // কোন ধরনের বিক্রি
            "dealer_id" => ["required_if:sale_type,dealer", "nullable", "exists:dealers,id"],
            "guest_name" => ["required_if:sale_type,guest", "nullable", "string", "max:255"],
            "guest_phone" => ["nullable", "string", "max:20"],
            "discount" => ["nullable", "numeric", "min:0"],
            "shipping" => ["nullable", "numeric", "min:0"],
            "paid" => ["required", "numeric", "min:0"],
            "ddate" => ["nullable", "date"],
            "items" => ["required", "array", "min:1"],
            "items.*.product_id" => ["required", "exists:products,id"],
            "items.*.quantity" => ["required", "integer", "min:1"],
        ]);

        return DB::transaction(function () use ($data) {
            $subtotal = 0;
            $itemsForCreate = [];

            foreach ($data["items"] as $row) {
                $product = Product::where("id", $row["product_id"])->lockForUpdate()->first();
                
                // স্টক চেক করা হচ্ছে, কিন্তু এখান থেকে মাইনাস করা হবে না
                if (!$product || $product->quantity < $row["quantity"]) {
                    throw ValidationException::withMessages(["items" => ["{$product->name} স্টকে পর্যাপ্ত নেই।"]]);
                }
                
                $lineTotal = bcmul($product->dp, $row["quantity"], 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);
                
                // NOTE: এখান থেকে $product->decrement() বাদ দেওয়া হয়েছে।

                $itemsForCreate[] = [
                    "product_id" => $product->id, "price" => $product->dp, "quantity" => $row["quantity"],
                    "total" => $lineTotal, "created_at" => now(), "updated_at" => now(),
                ];
            }

            $discount = $data["discount"] ?? 0;
            $shipping = $data["shipping"] ?? 0;
            $total = $subtotal - $discount + $shipping;
            $paid = $data["paid"];
            $due = max(0, $total - $paid);

            // ২. গেস্ট বিক্রির জন্য বকেয়া চেক
            if ($data['sale_type'] === 'guest' && $due > 0) {
                throw ValidationException::withMessages([
                    "paid" => ["গেস্টের কাছে বাকিতে বিক্রি করা যাবে না। সম্পূর্ণ টাকা পেমেন্ট করতে হবে।"],
                ]);
            }
            
            $saleData = [
                "dealer_id" => $data["dealer_id"] ?? null,
                "subtotal" => $subtotal, "discount" => $discount, "shipping" => $shipping,
                "total" => $total, "paid" => $paid, "due" => $due,
                
                // ৩. স্ট্যাটাস পরিবর্তন করা হয়েছে
                "status" => "ordered",
                "order_status" => 3,
                
                "ddate" => $data["ddate"] ?? now()->toDateString(),
                "srname" => auth()->user()->name, "user_id" => auth()->user()->id,
            ];

            // ৪. গেস্টের তথ্য যুক্ত করা
            if ($data['sale_type'] === 'guest') {
                $saleData['guest_name'] = $data['guest_name'];
                $saleData['guest_phone'] = $data['guest_phone'];
                $saleData['guest_sell_type'] = 2;
            }

            $sale = Sale::create($saleData);
            $sale->items()->createMany($itemsForCreate);
            
            // পেমেন্ট রেকর্ড (ঐচ্ছিক)
            if ($paid > 0) {
                $sale->payments()->create(["amount" => $paid, "method" => "cash", "paid_at" => now()]);
            }

            return response()->json([
                "status" => "success",
                "sale_id" => $sale->id,
                "redirect" => route("sales.show", $sale->id), // Normal Print URL
                "pos_url"  => route("sales.pos.print", $sale->id), // POS Print URL
                "bluetooth_url" => route("sales.bluetooth.print", $sale->id), // Bluetooth Print URL
            ]);
        });
    }
    public function allcustomerdues(Request $request)
{
    $query = Customer::query()
        ->whereHas("sales", function ($q) {
            $q->where("due", ">", 0);
        })
        ->where('customers.is_dipu', '!=', 1); // **যোগ করা হয়েছে:** dipu বাদে বাকিদের ডাটা আনবে

    // সার্চ
    if ($request->filled("search")) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where("customers.id", "like", "%{$search}%")
                ->orWhere("name", "like", "%{$search}%")
                ->orWhere("number", "like", "%{$search}%")
                ->orWhere("shop_name", "like", "%{$search}%");
        });
    } 

    // সর্টিং এর জন্য একটি সাব-কোয়েরি ব্যবহার করা হচ্ছে
    $dueSumSubquery = \App\Models\Sale::selectRaw("sum(due)")
        ->whereColumn("sales.customer_id", "customers.id")
        ->where("due", ">", 0);

    if ($request->get("sort") == "asc") {
        $query->orderBy($dueSumSubquery, "asc");
    } else {
        $query->orderBy($dueSumSubquery, "desc");
    }

    $customers = $query->paginate(30)->withQueryString();

    // প্রতিটি কাস্টমারের জন্য অতিরিক্ত তথ্য যোগ করা
    $customers->getCollection()->transform(function ($customer) {
        $sales = $customer
            ->sales()
            ->where("due", ">", 0)
            ->get();
        $customer->total_due = $sales->sum("due");
        $customer->total_invoices = $sales->count();
        unset($customer->sales);
        return $customer;
    });

    if ($request->ajax()) {
        // সারসংক্ষেপের জন্য কোয়েরি
        $summaryQuery = Customer::query()
            ->whereHas("sales", fn($q) => $q->where("due", ">", 0))
            ->where('customers.is_dipu', '!=', 1); // **যোগ করা হয়েছে:** এখানেও ফিল্টার করা হলো

        if ($request->filled("search")) {
            $search = $request->search;
            $summaryQuery->where(function ($q) use ($search) {
                $q->where("customers.id", "like", "%{$search}%")
                    ->orWhere("name", "like", "%{$search}%")
                    ->orWhere("number", "like", "%{$search}%");
            });
        }

        $totalDueAmount = $summaryQuery
            ->join("sales", "customers.id", "=", "sales.customer_id")
            ->where("sales.due", ">", 0)
            ->sum("sales.due");
        $dueCustomersCount = $summaryQuery
            ->distinct("customers.id")
            ->count("customers.id");

        return response()->json([
            "customers" => $customers,
            "summary" => [
                "dueCustomersCount" => $dueCustomersCount,
                "totalDueAmount" => number_format($totalDueAmount, 2),
            ],
        ]);
    }

    return view("sales.allcustomerdues", compact("customers"));
}
    public function alldcustomerdues(Request $request)
    {
        $query = Customer::query()
            ->where('customers.is_dipu', 1)
            ->whereHas("sales", function ($q) {
                $q->where("due", ">", 0);
            });

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("customers.id", "like", "%{$search}%")
                ->orWhere("customers.name", "like", "%{$search}%")
                ->orWhere("customers.number", "like", "%{$search}%")
                ->orWhere("customers.shop_name", "like", "%{$search}%");
            });
        }

        $dueSumSubquery = \App\Models\Sale::selectRaw("sum(due)")
            ->whereColumn("sales.customer_id", "customers.id")
            ->where("due", ">", 0);

        if ($request->get("sort") == "asc") {
            $query->orderBy($dueSumSubquery, "asc");
        } else {
            $query->orderBy($dueSumSubquery, "desc");
        }

        $customers = $query->paginate(30)->withQueryString();

        $customers->getCollection()->transform(function ($customer) {
            $sales = $customer->sales()->where("due", ">", 0)->get();
            $customer->total_due = $sales->sum("due");
            $customer->total_invoices = $sales->count();
            unset($customer->sales);
            return $customer;
        });

        if ($request->ajax()) {

            // ✅ সবসময় নতুন query — join এর আগে clone করা
            $summaryBase = Customer::query()
                ->where('customers.is_dipu', 1)
                ->whereHas("sales", fn($q) => $q->where("due", ">", 0));

            if ($request->filled("search")) {
                $search = $request->search;
                $summaryBase->where(function ($q) use ($search) {
                    $q->where("customers.id", "like", "%{$search}%")
                    ->orWhere("customers.name", "like", "%{$search}%")
                    ->orWhere("customers.number", "like", "%{$search}%");
                });
            }

            // ✅ count এর জন্য আলাদা query — join ছাড়া
            $dueCustomersCount = (clone $summaryBase)
                ->distinct('customers.id')
                ->count('customers.id');

            // ✅ sum এর জন্য আলাদা query — join সহ
            $totalDueAmount = (clone $summaryBase)
                ->join("sales", "customers.id", "=", "sales.customer_id")
                ->where("sales.due", ">", 0)
                ->sum("sales.due");

            return response()->json([
                "customers" => $customers,
                "summary" => [
                    "dueCustomersCount" => $dueCustomersCount,
                    "totalDueAmount" => number_format($totalDueAmount, 2),
                ],
            ]);
        }
    
        

        return view("sales.alldcustomerdues", compact("customers"));
    }

    

    public function alldealerdues(Request $request)
    {
        
        // if($request->host() == 'mb.gffoodandbeverage.com'){
        //     return view('demo');
        // }

        // Dealer মডেল এবং এর 'sales' রিলেশন ব্যবহার করা হচ্ছে
        $query = Dealer::query()->whereHas("sales", function ($q) {
            $q->where("due", ">", 0);
        });

        // সার্চ (id, নাম বা ফোন দিয়ে)
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("dealers.id", "like", "%{$search}%")
                    ->orWhere("name", "like", "%{$search}%")
                    ->orWhere("phone", "like", "%{$search}%")
                    ->orWhere("pname", "like", "%{$search}%");
            });
        }

        // সর্টিং এর জন্য সাব-কোয়েরি
        $dueSumSubquery = Sale::selectRaw("sum(due)")
            ->whereColumn("sales.dealer_id", "dealers.id")
            ->where("due", ">", 0);

        if ($request->get("sort") == "asc") {
            $query->orderBy($dueSumSubquery, "asc");
        } else {
            $query->orderBy($dueSumSubquery, "desc");
        }

        $dealers = $query->paginate(15)->withQueryString();

        // প্রতিটি ডিলারের জন্য অতিরিক্ত তথ্য যোগ করা
        $dealers->getCollection()->transform(function ($dealer) {
            $sales = $dealer
                ->sales()
                ->where("due", ">", 0)
                ->get();
            $dealer->total_due = $sales->sum("due");
            $dealer->total_invoices = $sales->count();
            unset($dealer->sales); // Eager loaded রিলেশন আনসেট করা হচ্ছে
            return $dealer;
        });

        if ($request->ajax()) {
            // সারসংক্ষেপের জন্য কোয়েরি
            $summaryQuery = Dealer::query()->whereHas(
                "sales",
                fn($q) => $q->where("due", ">", 0)
            );

            if ($request->filled("search")) {
                $search = $request->search;
                $summaryQuery->where(function ($q) use ($search) {
                    $q->where("dealers.id", "like", "%{$search}%")
                        ->orWhere("name", "like", "%{$search}%")
                        ->orWhere("phone", "like", "%{$search}%");
                });
            }

            $totalDueAmount = $summaryQuery
                ->join("sales", "dealers.id", "=", "sales.dealer_id")
                ->where("sales.due", ">", 0)
                ->sum("sales.due");
            $dueDealersCount = $summaryQuery
                ->distinct("dealers.id")
                ->count("dealers.id");

            return response()->json([
                "dealers" => $dealers,
                "summary" => [
                    "dueDealersCount" => $dueDealersCount,
                    "totalDueAmount" => number_format($totalDueAmount, 2),
                ],
            ]);
        }

        return view("sales.alldealerdues", compact("dealers"));
    }

    public function customerDues(Customer $customer, Request $request)
    {
        // eager load items & payments if needed
        $sales = Sale::where("customer_id", $customer->id)
            ->where("due", ">", 0)
            ->orderBy("created_at", "asc") // oldest first
            ->with("items")
            ->get();

        // optionally pass totals
        $totalDue = $sales->sum("due");
        return view(
            "sales.customer-dues",
            compact("customer", "sales", "totalDue")
        );
    }
    // customerDuesserch
    public function customerDuesserch(Customer $customer)
    {
        // eager load items & payments if needed
        $sales = Sale::where("customer_id", $customer->id)
            ->where("due", ">", 0)
            ->orderBy("created_at", "asc") // oldest first
            ->with("items")
            ->get();

        // optionally pass totals
        $totalDue = $sales->sum("due");
        $sales = $sales->count();
        return response()->json([
            "status" => "success",
            "invoise" => $sales,
            "totalDue" => $totalDue,
            "redirect" => route("customers.dues", $customer->id),
        ]);
    }

    // Apply payment to earliest invoices (ajax / POST)
    // public function payCustomerDues(Customer $customer, Request $request)
    // {
    //     $data = $request->validate([
    //         "amount" => "required|numeric|min:0.01",
    //         "method" => "nullable|string|max:50",
    //         "reference" => "nullable|string|max:255",
    //     ]);

    //     $amountToApply = (float) $data["amount"];
    //     if ($amountToApply <= 0) {
    //         throw ValidationException::withMessages([
    //             "amount" => "Invalid amount.",
    //         ]);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // fetch dues ordered oldest first and lock rows for update (concurrency-safe)
    //         $sales = Sale::where("customer_id", $customer->id)
    //             ->where("due", ">", 0)
    //             ->orderBy("created_at", "asc")
    //             ->lockForUpdate()
    //             ->get();

           
            

    //         if ($sales->isEmpty()) {
    //             throw ValidationException::withMessages([
    //                 "amount" => "This customer has no due invoices.",
    //             ]);
    //         }

    //         $applied = 0;
    //         $paymentsCreated = [];

    //         foreach ($sales as $sale) {
    //             if ($amountToApply <= 0) {
    //                 break;
    //             }

    //             $due = (float) $sale->due;
    //             if ($due <= 0) {
    //                 continue;
    //             }

    //             $payAmount = min($due, $amountToApply);

    //             // update sale paid & due
    //             $sale->paid = bcadd($sale->paid, $payAmount, 2);
    //             $sale->due = max(0, bcsub($sale->due, $payAmount, 2));
    //             $sale->status = $sale->due <= 0 ? "paid" : $sale->status;
    //             $sale->order_status = $sale->due <= 0 ? 1 : 0;
    //             $sale->save();

    //             // create payment record for this sale
    //             $payment = $sale->payments()->create([
    //                 "amount" => $payAmount,
    //                 "method" => $data["method"] ?? "unknown",
    //                 "reference" => $data["reference"] ?? null,
    //                 "paid_at" => now(),
    //                 "user_id" => auth()->user()->id,
    //                 "srname" => auth()->user()->name,
    //                 "customer_id" => $sale->customer_id,
    //                 "confrim" => 0,
    //             ]);

    //             $paymentsCreated[] = $payment;
    //             $amountToApply = round($amountToApply - $payAmount, 2);
    //             $applied += $payAmount;
    //         }

    //         DB::commit();

    //         // return updated sales summary (you can change to return full sales)
    //         $remainingDues = Sale::where("customer_id", $customer->id)
    //             ->where("due", ">", 0)
    //             ->sum("due");

    //         return response()->json([
    //             "status" => "success",
    //             "applied" => round($applied, 2), 
    //             "remaining_due" => (float) $remainingDues,
    //             "payments" => $paymentsCreated,
    //             "normal_url" => route("customers.statement", $customer->id),
    //             "pos_url" => route("customers.statement.pos.print", $customer->id),
    //             "bluetooth_url" => route("customers.statement.bluetooth", $customer->id),
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(
    //             [
    //                 "status" => "error",
    //                 "message" => $e->getMessage(),
    //             ],
    //             422
    //         );
    //     }
    // }
    
    public function payCustomerDues(Customer $customer, Request $request)
    {
        $data = $request->validate([
            "amount"    => "required|numeric|min:0.01",
            "method"    => "nullable|string|max:50",
            "reference" => "nullable|string|max:255",
        ]);

        $amountToApply = (float) $data["amount"];

        DB::beginTransaction();
        try {
            $sales = Sale::where("customer_id", $customer->id)
                ->where("due", ">", 0)
                ->orderBy("created_at", "asc")
                ->lockForUpdate()
                ->get();

            if ($sales->isEmpty()) {
                throw ValidationException::withMessages([
                    "amount" => "This customer has no due invoices.",
                ]);
            }

            // ✅ একটিই Payment রো — sale_id NULL কারণ এটি multiple sale cover করে
            $is_dipo = Sale::where('customer_id', $customer->id)
                ->where('is_dipu', 1)
                ->exists() ? 1 : 0;

            $data = [
                "sale_id"     => null,
                "amount"      => $amountToApply,
                "method"      => $data["method"] ?? "unknown",
                "reference"   => $data["reference"] ?? null,
                "paid_at"     => now(),
                "user_id"     => auth()->id(),
                "srname"      => auth()->user()->name,
                "customer_id" => $customer->id,
                "confrim"     => 0,
                "is_dipo"     => $is_dipo,
            ];

            $payment = Payment::create($data);

            $applied   = 0;
            $remaining = $amountToApply;

            foreach ($sales as $sale) {
                if ($remaining <= 0) break;

                $due = (float) $sale->due;
                if ($due <= 0) continue;

                $payAmount = min($due, $remaining);

                // Sale আপডেট করুন
                $sale->paid   = bcadd((string) $sale->paid, (string) $payAmount, 2);
                $sale->due    = max(0, bcsub((string) $sale->due, (string) $payAmount, 2));
                $sale->status = $sale->due <= 0 ? "paid" : $sale->status;
                $sale->order_status = $sale->due <= 0 ? 1 : 0;
                $sale->save();

                $remaining = round($remaining - $payAmount, 2);
                $applied  += $payAmount;
            }

            DB::commit();

            $remainingDues = Sale::where("customer_id", $customer->id)
                ->where("due", ">", 0)
                ->sum("due");

            return response()->json([
                "status"        => "success",
                "applied"       => round($applied, 2),
                "remaining_due" => (float) $remainingDues,
                "payments"      => [$payment],
                "normal_url"    => route("customers.statement", $customer->id),
                "pos_url"       => route("customers.statement.pos.print", $customer->id),
                "bluetooth_url" => route("customers.statement.bluetooth", $customer->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage(),
            ], 422);
        }
    }

    public function paymentReport(Request $request)
    {
        // ফিল্টার প্যারামিটার রিসিভ করা
        $dateFilter  = $request->input('date_filter', 'today');
        $userId      = $request->input('user_id');
        $paymentType = $request->input('payment_type'); // customer / dealer / dipo / all

        $query = Payment::with(['customer', 'dealer', 'user', 'sale']);

        // ১. SR / ইউজার আইডি ফিল্টার
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        // ২. পেমেন্টের ধরন ফিল্টার (কাস্টমার, ডিলার এবং ডিপু)
        if ($paymentType == 'customer') {
            $query->whereNotNull('customer_id')
                ->where(function($q) {
                    $q->where('is_dipo', '!=', 1)->orWhereNull('is_dipo');
                });
        } elseif ($paymentType == 'dealer') {
            $query->whereNotNull('dealer_id');
        } elseif ($paymentType == 'dipo') {
            $query->whereNotNull('customer_id')->where('is_dipo', 1);
        }

        // ৩. তারিখ অনুযায়ী ফিল্টার
        switch ($dateFilter) {
            case 'yesterday':
                $query->whereDate('paid_at', Carbon::yesterday());
                break;
            case 'today':
                $query->whereDate('paid_at', Carbon::today());
                break;
            case 'last_7_days':
                $query->whereBetween('paid_at', [Carbon::today()->subDays(6), Carbon::today()]);
                break;
            case 'last_15_days':
                $query->whereBetween('paid_at', [Carbon::today()->subDays(14), Carbon::today()]);
                break;
            case 'last_month':
                $query->whereBetween('paid_at', [Carbon::today()->subDays(29), Carbon::today()]);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('paid_at', [$request->start_date, $request->end_date]);
                }
                break;
        }

        // ৪. সামারি হিসাব
        $totalPaymentCount = (clone $query)->count();
        $totalAmount       = (clone $query)->sum('amount');

        // ৫. তারিখ অনুযায়ী প্রথম থেকে সাজানো (Ascending Order)
        $query->orderBy('paid_at', 'asc')->orderBy('id', 'asc');

        // ৬. প্রিন্ট রিকোয়েস্ট চেক (প্রিন্ট এ ক্লিক করলে পেজিনেশন ছাড়া সব ডাটা আসবে)
        if ($request->has('print')) {
            $payments = $query->get();
            return view('payments.print_report', compact(
                'payments', 'dateFilter', 'paymentType', 'totalPaymentCount', 'totalAmount'
            ));
        }

        // সাধারণ পেজিনেশন ডাটা
        $payments = $query->paginate(20);
        $users    = User::all();

        return view('payments.report', compact(
            'payments', 'users', 'dateFilter', 
            'paymentType', 'totalPaymentCount', 'totalAmount'
        ));
    }

    public function dealerDues(Dealer $customer, Request $request)
    {
        // eager load items & payments if needed
        $sales = Sale::where("dealer_id", $customer->id)
            ->where("due", ">", 0)
            ->orderBy("created_at", "asc") // oldest first
            ->with("items")
            ->get();

        // optionally pass totals
        $totalDue = $sales->sum("due");
        return view(
            "sales.deler-dues",
            compact("customer", "sales", "totalDue")
        );
    }

    public function dealerDuesserch(Dealer $customer, Request $request)
    {
        // eager load items & payments if needed
        $sales = Sale::where("dealer_id", $customer->id)
            ->where("due", ">", 0)
            ->orderBy("created_at", "asc") // oldest first
            ->with("items")
            ->get();

        // optionally pass totals
        // optionally pass totals
        $totalDue = $sales->sum("due");
        $sales = $sales->count();
        return response()->json([
            "status" => "success",
            "invoise" => $sales,
            "totalDue" => $totalDue,
            "redirect" => route("dealer.dues", $customer->id),
        ]);
    }

    // public function payDealerDues(Dealer $customer, Request $request)
    // {
    //     $data = $request->validate([
    //         "amount" => "required|numeric|min:0.01",
    //         "method" => "nullable|string|max:50",
    //         "reference" => "nullable|string|max:255",
    //     ]);

    //     $amountToApply = (float) $data["amount"];
    //     if ($amountToApply <= 0) {
    //         throw ValidationException::withMessages([
    //             "amount" => "Invalid amount.",
    //         ]);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // fetch dues ordered oldest first and lock rows for update (concurrency-safe)
    //         $sales = Sale::where("dealer_id", $customer->id)
    //             ->where("due", ">", 0)
    //             ->orderBy("created_at", "asc")
    //             ->lockForUpdate()
    //             ->get();

    //         if ($sales->isEmpty()) {
    //             throw ValidationException::withMessages([
    //                 "amount" => "This customer has no due invoices.",
    //             ]);
    //         }

    //         $applied = 0;
    //         $paymentsCreated = [];

    //         foreach ($sales as $sale) {
    //             if ($amountToApply <= 0) {
    //                 break;
    //             }

    //             $due = (float) $sale->due;
    //             if ($due <= 0) {
    //                 continue;
    //             }

    //             $payAmount = min($due, $amountToApply);

    //             // update sale paid & due
    //             $sale->paid = bcadd($sale->paid, $payAmount, 2);
    //             $sale->due = max(0, bcsub($sale->due, $payAmount, 2));
    //             $sale->status = $sale->due <= 0 ? "paid" : $sale->status;
    //             $sale->order_status = $sale->due <= 0 ? 1 : 0;
    //             $sale->save();

    //             // create payment record for this sale
    //             $payment = $sale->payments()->create([
    //                 "amount" => $payAmount,
    //                 "method" => $data["method"] ?? "unknown",
    //                 "reference" => $data["reference"] ?? null,
    //                 "paid_at" => now(),
    //                 "user_id" => auth()->user()->id,
    //                 "srname" => auth()->user()->name,
    //                 "dealer_id" => $sale->dealer_id,
    //                 "confrim" => 0,
    //             ]);

    //             $paymentsCreated[] = $payment;
    //             $amountToApply = round($amountToApply - $payAmount, 2);
    //             $applied += $payAmount;
    //         }

    //         DB::commit();

    //         // return updated sales summary (you can change to return full sales)
    //         $remainingDues = Sale::where("dealer_id", $customer->id)
    //             ->where("due", ">", 0)
    //             ->sum("due");

    //         return response()->json([
    //             "status" => "success",
    //             "applied" => round($applied, 2),
    //             "remaining_due" => (float) $remainingDues,
    //             "payments" => $paymentsCreated,
    //             "normal_url" => route("diller.statement", $customer->id),
    //             "pos_url" => route("diller.statement.pos.print", $customer->id),
    //             "bluetooth_url" => route("diller.statement.bluetooth", $customer->id),
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(
    //             [
    //                 "status" => "error",
    //                 "message" => $e->getMessage(),
    //             ],
    //             422
    //         );
    //     }
    // }

    public function payDealerDues(Dealer $customer, Request $request)
    {
        $data = $request->validate([
            "amount"    => "required|numeric|min:0.01",
            "method"    => "nullable|string|max:50",
            "reference" => "nullable|string|max:255",
        ]);

        $amountToApply = (float) $data["amount"];

        DB::beginTransaction();
        try {
            $sales = Sale::where("dealer_id", $customer->id)
                ->where("due", ">", 0)
                ->orderBy("created_at", "asc")
                ->lockForUpdate()
                ->get();

            if ($sales->isEmpty()) {
                throw ValidationException::withMessages([
                    "amount" => "This dealer has no due invoices.",
                ]);
            }

            // ✅ একটিই Payment রো — sale_id NULL কারণ এটি multiple sale cover করে

            $payment = Payment::create([
                "sale_id"     => null, // multiple sale এর জন্য NULL
                "amount"      => $amountToApply,
                "method"      => $data["method"] ?? "unknown",
                "reference"   => $data["reference"] ?? null,
                "paid_at"     => now(),
                "user_id"     => auth()->id(),
                "srname"      => auth()->user()->name,
                "dealer_id"   => $customer->id,
                "confrim"     => 0,
            ]);

            $applied   = 0;
            $remaining = $amountToApply;

            foreach ($sales as $sale) {
                if ($remaining <= 0) break;

                $due = (float) $sale->due;
                if ($due <= 0) continue;

                $payAmount = min($due, $remaining);

                // Sale আপডেট করুন
                $sale->paid   = bcadd((string) $sale->paid, (string) $payAmount, 2);
                $sale->due    = max(0, bcsub((string) $sale->due, (string) $payAmount, 2));
                $sale->status = $sale->due <= 0 ? "paid" : $sale->status;
                $sale->order_status = $sale->due <= 0 ? 1 : 0;
                $sale->save();

                $remaining = round($remaining - $payAmount, 2);
                $applied  += $payAmount;
            }

            DB::commit();

            $remainingDues = Sale::where("dealer_id", $customer->id)
                ->where("due", ">", 0)
                ->sum("due");

            return response()->json([
                "status"        => "success",
                "applied"       => round($applied, 2),
                "remaining_due" => (float) $remainingDues,
                "payments"      => [$payment],
                "normal_url"    => route("diller.statement", $customer->id),
                "pos_url"       => route("diller.statement.pos.print", $customer->id),
                "bluetooth_url" => route("diller.statement.bluetooth", $customer->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage(),
            ], 422);
        }
    }

    
// ========== CONTROLLER ==========
public function customerStatement(Customer $customer)
{
    // সব সেল আইডি দিয়ে সেল গুলো পান
    $sales = $customer->sales()->orderBy('created_at', 'asc')->get();

    // সব পেমেন্ট ডাটা পান (সব ধরনের - sale related এবং standalone)
    $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();

    // সেল এবং পেমেন্ট একসাথে করুন
    $transactions = collect();
    
    // প্রথমে সব সেল যোগ করুন
    foreach ($sales as $sale) {
        $transactions->push([
            'type' => 'sale',
            'data' => $sale,
            'sort_date' => $sale->created_at,
        ]);
    }
    
    // তারপর সব পেমেন্ট আলাদাভাবে যোগ করুন
    foreach ($allPayments as $payment) {
        $transactions->push([
            'type' => 'payment',
            'data' => $payment,
            'sort_date' => $payment->paid_at,
        ]);
    }

    // তারিখ অনুযায়ী সাজান
    $transactions = $transactions->sortBy('sort_date')->values();

    $totalSalesAmount = $sales->sum("total");
    $totalPaidOnInvoice = $sales->sum("paid");
    $totalStandalonePayments = $allPayments->sum("amount");
    $totalPaymentAmount = $totalStandalonePayments;
    $currentDue = $sales->sum("due");

    return view(
        "sales.customer_statement",
        compact(
            "customer",
            "transactions",
            "totalSalesAmount",
            "totalPaymentAmount",
            "currentDue"
        )
    );
}

// ========== CONTROLLER METHOD ==========
public function dillerStatement(Dealer $customer)
{
    // সব সেল আইডি দিয়ে সেল গুলো পান
    $sales = $customer->sales()->orderBy('created_at', 'asc')->get();

    // সব পেমেন্ট ডাটা পান (সব ধরনের - sale related এবং standalone)
    $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();

    // সেল এবং পেমেন্ট একসাথে করুন
    $transactions = collect();
    
    // প্রথমে সব সেল যোগ করুন
    foreach ($sales as $sale) {
        $transactions->push([
            'type' => 'sale',
            'data' => $sale,
            'sort_date' => $sale->created_at,
        ]);
    }
    
    // তারপর সব পেমেন্ট আলাদাভাবে যোগ করুন
    foreach ($allPayments as $payment) {
        $transactions->push([
            'type' => 'payment',
            'data' => $payment,
            'sort_date' => $payment->paid_at,
        ]);
    }

    // তারিখ অনুযায়ী সাজান
    $transactions = $transactions->sortBy('sort_date')->values();

    // হিসাব গণনা
    $totalSalesAmount = $sales->sum("total");
    $totalPaidOnInvoice = $sales->sum("paid");
    $totalStandalonePayments = $allPayments->sum("amount");
    $totalPaymentAmount = $totalStandalonePayments;
    $currentDue = $sales->sum("due");

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

/**
 * Customer Bluetooth Statement Print
 */
public function customerStatementBluetooth(Customer $customer)
{
    // Sales data
    $sales = $customer->sales()->orderBy('created_at', 'asc')->get();

    // Payments data
    $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();

    // Transactions একত্রিত করুন
    $transactions = collect();

    foreach ($sales as $sale) {
        $transactions->push([
            'type'      => 'sale',
            'data'      => $sale,
            'sort_date' => $sale->created_at,
        ]);
    }

    foreach ($allPayments as $payment) {
        $transactions->push([
            'type'      => 'payment',
            'data'      => $payment,
            'sort_date' => $payment->paid_at,
        ]);
    }

    $transactions = $transactions->sortBy('sort_date')->values();

    // হিসাব
    $totalSalesAmount    = $sales->sum('total');
    $totalPaymentAmount  = $allPayments->sum('amount');
    $currentDue          = $sales->sum('due');

    // BluetoothStatementPrinterService এর জন্য data তৈরি
    $printData = [
        'type'            => 'customer',
        'customer_id'     => $customer->id,
        'customer_name'   => $customer->name,
        'shop_name'       => $customer->shop_name   ?? null,
        'mobile'          => $customer->mobile       ?? null,
        'address'         => $customer->address      ?? null,
        'statement_date'  => now()->format('d-m-Y'),
        'transactions'    => $transactions->toArray(),
        'total_sales'     => $totalSalesAmount,
        'total_payments'  => $totalPaymentAmount,
        'current_due'     => $currentDue,
    ];

    // Bluetooth print command generate করুন
    $printerService = new BluetoothStatementPrinterService();
    $result = $printerService->generateBluetoothStatement($printData);

    if (!$result['success']) {
        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Print error occurred.',
        ], 500);
    }

    // intent_url মোবাইলে পাঠান (RawBT app এর জন্য)
    return response()->json([
        'success'    => true,
        'intent_url' => $result['intent_url'],
        'customer'   => $customer->name,
    ]);
}

/**
 * Dealer Bluetooth Statement Print
 */
public function dillerStatementBluetooth(Dealer $customer)
{
    // Sales data
    $sales = $customer->sales()->orderBy('created_at', 'asc')->get();

    // Payments data
    $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();

    // Transactions একত্রিত করুন
    $transactions = collect();

    foreach ($sales as $sale) {
        $transactions->push([
            'type'      => 'sale',
            'data'      => $sale,
            'sort_date' => $sale->created_at,
        ]);
    }

    foreach ($allPayments as $payment) {
        $transactions->push([
            'type'      => 'payment',
            'data'      => $payment,
            'sort_date' => $payment->paid_at,
        ]);
    }

    $transactions = $transactions->sortBy('sort_date')->values();

    // হিসাব
    $totalSalesAmount    = $sales->sum('total');
    $totalPaymentAmount  = $allPayments->sum('amount');
    $currentDue          = $sales->sum('due');

    // BluetoothStatementPrinterService এর জন্য data তৈরি
    $printData = [
        'type'            => 'dealer',
        'customer_id'     => $customer->id,
        'customer_name'   => $customer->name,
        'shop_name'       => $customer->shop_name   ?? null,
        'mobile'          => $customer->mobile       ?? null,
        'address'         => $customer->address      ?? null,
        'statement_date'  => now()->format('d-m-Y'),
        'transactions'    => $transactions->toArray(),
        'total_sales'     => $totalSalesAmount,
        'total_payments'  => $totalPaymentAmount,
        'current_due'     => $currentDue,
    ];

    // Bluetooth print command generate করুন
    $printerService = new BluetoothStatementPrinterService();
    $result = $printerService->generateBluetoothStatement($printData);

    if (!$result['success']) {
        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Print error occurred.',
        ], 500);
    }

    return response()->json([
        'success'    => true,
        'intent_url' => $result['intent_url'],
        'dealer'     => $customer->name,
    ]);
}

public function printPos(Sale $sale, BanglaPrinterService $printerService)
{
    try {
        // ১. ডেটা লোড করা (আপনার show মেথডের লজিক অনুযায়ী)
        $sale->load("items.product", "customer", "dealer", "payments");
        
        $totalDue = 0;
        $prevDueCount = 0;
        $customerName = '';
        $shopName = '';
        $address = '';

        // কাস্টমার লজিক
        if ($sale->customer_id) {
            $customerSales = Sale::where('customer_id', $sale->customer_id);
            $totalDue = $customerSales->sum('due');
            $prevDueCount = $customerSales->where('due', '>', 0)->count();
            
            $shopName = $sale->customer->shop_name;
            $customerName = $sale->customer->name;
            $address = $sale->customer->shop_address;
            $clinetType = 'Customer Invoice';
            $mobile = $sale->customer->number; 
        } 
        // ডিলার লজিক
        elseif ($sale->dealer_id) {
            $dealerSales = Sale::where('dealer_id', $sale->dealer_id);
            $totalDue = $dealerSales->sum('due');
            $prevDueCount = $dealerSales->where('due', '>', 0)->count();

            $shopName = $sale->dealer->pname ?? 'Dealer Shop';
            $customerName = $sale->dealer->name;
            $address = $sale->dealer->address;
            $clinetType = 'Dealer Invoice';
            $mobile = $sale->dealer->phone;
        } 
        // গেস্ট লজিক
        elseif ($sale->guest_sell_type == 1) {
            $customerName = $sale->guest_name;
            
            $clinetType = 'Guest Customer Invoice';
            $mobile = $sale->guest_phone;
        }
        // ৪. যদি গেস্ট টাইপ ২ হয় (ডিলার ভিউ)
        elseif ($sale->guest_sell_type == 2) {  
            $customerName = $sale->guest_name;
            
            $clinetType = 'Guest Dealer Invoice';
            $mobile = $sale->guest_phone;
        }

        // ২. প্রিন্ট ডেটা প্রস্তুত করা
        $invoiceData = [
            'id' => $sale->id,
            'date' => $sale->created_at->format('d-m-Y h:i A'),
            'srname' => $sale->srname,
            'ddate' => $sale->ddate->format('d-m-Y'),
            'client_type'=> $clinetType,
            'mobile'=> $mobile,
            'shop_name' => $shopName,
            'customer_name' => $customerName,
            'address' => $address,
            'items' => $sale->items, // আইটেম রিলেশনশিপ
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'shipping' => $sale->shipping, // Other Cost
            'total' => $sale->total,
            'paid' => $sale->paid,
            'due' => $sale->due,
            'total_due' => $totalDue, // গ্র্যান্ড টোটাল ডিউ
            'prev_due_count' => $prevDueCount > 0 ? $prevDueCount - 1 : 0
        ];

        
        



        // ৩. সার্ভিস কল করা (এটি আমরা নিচে আপডেট করছি)
        $result = $printerService->printPosInvoice($invoiceData);

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function printBluetooth(Sale $sale, BluetoothPrinterService $bluetoothService)
{
    try {
        // Same data preparation as printPos method
        $sale->load("items.product", "customer", "dealer", "payments");
        
        $totalDue = 0;
        $prevDueCount = 0;
        $customerName = '';
        $shopName = '';
        $address = '';
        $clinetType = '';
        $mobile = '';

        // Customer logic
        if ($sale->customer_id) {
            $customerSales = Sale::where('customer_id', $sale->customer_id);
            $totalDue = $customerSales->sum('due');
            $prevDueCount = $customerSales->where('due', '>', 0)->count();
            
            $shopName = $sale->customer->shop_name;
            $customerName = $sale->customer->name;
            $address = $sale->customer->shop_address;
            $clinetType = 'Customer Invoice';
            $mobile = $sale->customer->number; 
        } 
        // Dealer logic
        elseif ($sale->dealer_id) {
            $dealerSales = Sale::where('dealer_id', $sale->dealer_id);
            $totalDue = $dealerSales->sum('due');
            $prevDueCount = $dealerSales->where('due', '>', 0)->count();

            $shopName = $sale->dealer->pname ?? 'Dealer Shop';
            $customerName = $sale->dealer->name;
            $address = $sale->dealer->address;
            $clinetType = 'Dealer Invoice';
            $mobile = $sale->dealer->phone;
        } 
        // Guest type 1
        elseif ($sale->guest_sell_type == 1) {
            $customerName = $sale->guest_name;
            $clinetType = 'Guest Customer Invoice';
            $mobile = $sale->guest_phone;
        }
        // Guest type 2
        elseif ($sale->guest_sell_type == 2) {  
            $customerName = $sale->guest_name;
            $clinetType = 'Guest Dealer Invoice';
            $mobile = $sale->guest_phone;
        }

        $invoiceData = [
            'id' => $sale->id,
            'date' => $sale->created_at->format('d-m-Y h:i A'),
            'srname' => $sale->srname,
            'ddate' => $sale->ddate ? $sale->ddate->format('d-m-Y') : '',
            'client_type'=> $clinetType,
            'mobile'=> $mobile,
            'shop_name' => $shopName,
            'customer_name' => $customerName,
            'address' => $address,
            'items' => $sale->items,
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'shipping' => $sale->shipping,
            'total' => $sale->total,
            'paid' => $sale->paid,
            'due' => $sale->due,
            'total_due' => $totalDue,
            'prev_due_count' => $prevDueCount > 0 ? $prevDueCount - 1 : 0
        ];

        // Bluetooth Service call
        $result = $bluetoothService->generateBluetoothPrint($invoiceData);

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function customerStatementPosPrint(Customer $customer, StatementPrinterService $printerService)
{
    try {
        // Same data preparation as customerStatement method
        $sales = $customer->sales()->orderBy('created_at', 'asc')->get();
        $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();
        
        // সেল এবং পেমেন্ট একসাথে করুন
        $transactions = collect();
        
        foreach ($sales as $sale) {
            $transactions->push([
                'type' => 'sale',
                'data' => $sale,
                'sort_date' => $sale->created_at,
            ]);
        }
        
        foreach ($allPayments as $payment) {
            $transactions->push([
                'type' => 'payment',
                'data' => $payment,
                'sort_date' => $payment->paid_at,
            ]);
        }
        
        // তারিখ অনুযায়ী সাজান
        $transactions = $transactions->sortBy('sort_date')->values();
        
        $totalSalesAmount = $sales->sum("total");
        $totalPaymentAmount = $allPayments->sum("amount");
        $currentDue = $sales->sum("due");
        
        // Statement data prepare করুন
        $statementData = [
            'id' => $customer->id,
            'customer_name' => $customer->name,
            'shop_name' => $customer->shop_name ?? 'N/A',
            'mobile' => $customer->number ?? '',
            'address' => $customer->shop_address ?? '',
            'transactions' => $transactions,
            'total_sales' => $totalSalesAmount,
            'total_payments' => $totalPaymentAmount,
            'current_due' => $currentDue,
            'statement_date' => date('d-m-Y'),
        ];
        
        // Printer Service call করুন
        $result = $printerService->printPosInvoice($statementData);
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function dillerStatementPosPrint(Dealer $customer, DillerStatementPrinterService $printerService)
{
    try {
        // Same data preparation as customerStatement method
        $sales = $customer->sales()->orderBy('created_at', 'asc')->get();
        $allPayments = $customer->payments()->orderBy('paid_at', 'asc')->get();
        
        // সেল এবং পেমেন্ট একসাথে করুন
        $transactions = collect();
        
        foreach ($sales as $sale) {
            $transactions->push([
                'type' => 'sale',
                'data' => $sale,
                'sort_date' => $sale->created_at,
            ]);
        }
        
        foreach ($allPayments as $payment) {
            $transactions->push([
                'type' => 'payment',
                'data' => $payment,
                'sort_date' => $payment->paid_at,
            ]);
        }
        
        // তারিখ অনুযায়ী সাজান
        $transactions = $transactions->sortBy('sort_date')->values();
        
        $totalSalesAmount = $sales->sum("total");
        $totalPaymentAmount = $allPayments->sum("amount");
        $currentDue = $sales->sum("due");
        
        // Statement data prepare করুন
        $statementData = [
            'id' => $customer->id,
            'customer_name' => $customer->name,
            'shop_name' => $customer->pname ?? 'N/A',
            'mobile' => $customer->phone ?? '',
            'address' => $customer->address ?? '',
            'transactions' => $transactions,
            'total_sales' => $totalSalesAmount,
            'total_payments' => $totalPaymentAmount,
            'current_due' => $currentDue,
            'statement_date' => date('d-m-Y'),
        ];
        
        // Printer Service call করুন
        $result = $printerService->printPosInvoice($statementData);
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function salesReportall(Request $request)
    {
        // ডিফল্টভাবে 'আজ' (today) ফিল্টার সেট করা হলো
        $dateFilter = $request->input('date_filter', 'today');
        $userId = $request->input('user_id');

        // order_status = 3 (Ordered) বাদ দিয়ে বাকি সব ডাটা
        $query = Sale::with(['customer', 'dealer', 'usersr'])->where('order_status', '!=', 3);

        // ১. ইউজার আইডি অনুযায়ী ফিল্টার
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        // ২. তারিখ অনুযায়ী ফিল্টার
        switch ($dateFilter) {
            case 'yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'last_7_days':
                $query->whereBetween('created_at', [Carbon::today()->subDays(6)->startOfDay(), Carbon::now()]);
                break;
            case 'last_15_days':
                $query->whereBetween('created_at', [Carbon::today()->subDays(14)->startOfDay(), Carbon::now()]);
                break;
            case 'last_month':
                $query->whereBetween('created_at', [Carbon::today()->subDays(29)->startOfDay(), Carbon::now()]);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
                break;
        }

        // সামারি হিসাব (ফিল্টার অনুযায়ী মোট হিসাব)
        $totalSalesCount = (clone $query)->count();
        $totalAmount     = (clone $query)->sum('total');
        $totalPaid       = (clone $query)->sum('paid');
        $totalDue        = (clone $query)->sum('due');

        // ৩. তারিখ অনুযায়ী প্রথম থেকে সাজানো (Ascending Order - পুরানো তারিখ থেকে নতুন তারিখ)
        $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');

        // ৪. প্রিন্ট/PDF রিকোয়েস্ট চেক (প্রিন্ট বাটনে ক্লিক করলে পেজিনেশন ছাড়া সব ডাটা আসবে)
        if ($request->has('print')) {
            $sales = $query->get(); // পেজিনেশন ছাড়া সব ডাটা
            return view('sales.print_reportall', compact(
                'sales', 'dateFilter', 
                'totalSalesCount', 'totalAmount', 'totalPaid', 'totalDue'
            ));
        }

        // সাধারণ পেজিনেশন ডাটা (স্ক্রিনের জন্য)
        $sales = $query->paginate(20);
        $users = User::all();

        return view('sales.reportall', compact(
            'sales', 'users', 'dateFilter', 
            'totalSalesCount', 'totalAmount', 'totalPaid', 'totalDue'
        ));
    }

}
