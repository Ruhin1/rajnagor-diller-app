<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Chef;
use App\Models\GetCardChef;
use App\Models\CardTocen;
use App\Models\User;
use App\Models\Cost;


use Illuminate\Support\Facades\DB; 

use Carbon\Carbon;

class CardController extends Controller
{
    // Card create from
    public function create()
    {
        $cards = CardTocen::latest()->paginate(10);
        return view("cards.create", compact("cards"));
    }
    // Card create database
    public function createCard(Request $request)
    {
        $request->validate([
            "product_name" => "required|string|max:255",
            "token" => "required|string|unique:card_tocens,token",
            "card_amount" => "required|integer|min:1|max:500",
        ]);

        // প্রোডাক্ট-প্রাইস ম্যাপিং
        $priceMap = [
            // 300 টাকা
            'টুয়েন্টি মসলা'   => 300,
            'মেজবান মসলা'     => 300,
            'কারি পাউডার'     => 300,
            'কালাভুনা মশলা'   => 300,
            'রোস্ট ফ্রাই মসলা'=> 300, 
            'বিরিয়ানী মশলা'   => 300,
            'মিক্স মসলা'      => 300,
            'কাঠ বাদাম গুড়া'  => 300,
            'আলু-বোখড়া'       => 300,
            'তন্ময় মাওয়া'     => 300,
            'তন্ময় ঘি'        => 300,
            'বাটার অয়েল'      => 300,
            'বাটার পোলাও'     => 300,
            'মালাই ক্রিম'     => 300,
            'মিট ক্রিম'       => 300,
            'মাছের রোস্ট ফ্রাই' => 300,
            'দুঁ পেয়াজা মশলা' => 300,
            'পেস্তাবাদাম গুড়া (5)' => 500,

            // অন্যান্য
            'রোস্ট সস্'        => 80,
            'বিরানী সস'        => 80,
            'মিট সস'           => 100,
            'ভেজিটেবল সস'    => 150,
        ];

        // product_name থেকে প্রাইস বের করা
        $price = $priceMap[$request->product_name] ?? 0;

        

        
        // Save token entry
        CardTocen::create([
            "product_name" => $request->product_name,
            "token" => $request->token,
            "card_amount" => $request->card_amount,
            "awards"=> $price,
        ]);

        // Generate unique codes
        $codes = $this->generateUniqueCodes($request->card_amount);
        $product_id_map = [
            // product_name থেকে product_id ম্যাপিং
            // ১. অ্যারে ব্যবহার করে ম্যাপিং
        'টুয়েন্টি মসলা'   => 9,
        'মেজবান মসলা'     => 10,
        'কারি পাউডার'     => 25,
        'কালাভুনা মশলা'   => 11,
        'রোস্ট ফ্রাই মসলা'=> 6, 
        'বিরিয়ানী মশলা'   => 13,
        'মিক্স মসলা'      => 24,
        'কাঠ বাদাম গুড়া'  => 7,
        'আলু-বোখড়া'       => 12,
        'তন্ময় মাওয়া'     => 23,
        'তন্ময় ঘি'        => 17,
        'বাটার অয়েল'      => 20,
        'বাটার পোলাও'     => 21,
        'মালাই ক্রিম'     => 18,
        'মিট ক্রিম'       => 19,
        'মাছের রোস্ট ফ্রাই' => 8,
        'রোস্ট সস্'        => 14, 
        'বিরানী সস'        => 15,
        'মিট সস'           => 16,
        'দুঁ পেয়াজা মশলা' => 72,
        'পেস্তাবাদাম গুড়া (5)' => 98,
        'ভেজিটেবল সস'    => 73,
    ]; 

    // ২. একটি মাত্র লাইন ব্যবহার করে product_id খুঁজে বের করুন
    $product_id = $product_id_map[$request->product_name] ?? null;

        // Prepare bulk insert
        $cards = [];
        foreach ($codes as $code) {
            $cards[] = [
                "code" => $code,
                "product_name" => $request->product_name,
                "identy_number" => $request->token,
                "created_at" => now(),
                "updated_at" => now(),
                "awards"=> $price,
                "product_id" => $product_id,
            ];
        }

        // Insert at once
        Card::insert($cards);

        return back()->with(
            "success",
            "{$request->product_name} জন্য {$request->card_amount} টি কার্ড সফল ভাবে তৈরী হয়েছে। কার্ডের টোকেন: {$request->token}"
        );
    }
    
    public function cardprint(Request $request)
    {
        $cards = Card::where("identy_number", $request->token)
                    ->get(["id", "code", "product_name"]);

        // ১. প্রথমে চেক করুন কালেকশনটি খালি কিনা
        if ($cards->isEmpty()) {
            // যদি টোকেনটি ভুল হয় বা কোনো কার্ড না পাওয়া যায়
            return back()->with('error', 'এই টোকেনের জন্য কোনো কার্ড পাওয়া যায়নি।');
        }

        // // ২. কালেকশন থেকে প্রথম কার্ডটির product_name নিন
        // $productName = $cards->first()->product_name;

        // // ৩. এখন $productName ভেরিয়েবলটি দিয়ে শর্ত চেক করুন
        // if (in_array($productName, ["রোস্ট সস্", "বিরানী সস", "মিট সস"])) {
        //     return view("cards.other", compact("cards"));
        // }

        return view("cards.print", compact("cards"));
    }
    
    // Card villedty check ui page
    public function index()
    {
        return view("cards.createnew");
    }

    // Card villedty check code
    
    public function check(Request $request)
    {
        $request->validate([
            "code" => "required|string",
        ]);

        $card = Card::where("code", $request->code)->first();

        if (!$card) {
            return response()->json([
                "status" => "error",
                "message" => "❌ কোডটি পাওয়া যায়নি",
            ]);
        }

        if ($card->is_used) {
            $usedDate = Carbon::parse($card->used_at)->format("d M, Y h:i A");
            return response()->json([
                "status" => "error",
                "message" =>
                    "⚠️ এই কোডটি ইতিমধ্যেই ব্যবহার করা হয়েছে। ব্যবহারের তারিখ: " .
                    $usedDate,
            ]);
        }

        // ভ্যালিড
        return response()->json([
            "status" => "valid",
            "card_id" => $card->id,
            "product_name" => $card->product_name,
        ]);
    }

   
    public function process(Request $request, $id)
{
    // ১. প্রথমেই প্রমাণীকরণ (Authentication) চেক করা উচিত যে ইউজার লগইন করা আছে কি না।
    // এই কাজটি সাধারণত Route Middleware দিয়ে করা হয়।

    $card = Card::findOrFail($id);
    $currentUser = auth()->user();
    $chef = null; // <-- বাগ ফিক্স ১: chef ভেরিয়েবলকে শুরুতে null হিসেবে ঘোষণা করা

    try {
        // অপটিমাইজেশন ১: ডেটাবেস ট্রানজেকশন শুরু
        DB::beginTransaction();

        if ($request->assign === "yes") {
            $request->validate([
                "c_number" => "required|string",
            ]);

            $chef = Chef::where("c_number", $request->c_number)->first();

            if (!$chef) {
                return response()->json([
                    "status" => "error",
                    "message" => "❌ Chef পাওয়া যায়নি",
                ]);
            }

            GetCardChef::create([
                "chef_id" => $chef->id,
                "p_name" => $card->product_name,
            ]);
        }

        $data = [
            "is_used" => true,
            'user_id' => $currentUser->id,
            "used_at" => now(),
            "used_by" => $currentUser->name,
            // অপটিমাইজেশন ২: টারনারি অপারেটর দিয়ে কোড সংক্ষিপ্ত করা
            "customer_name" => $chef ? $chef->name : 'ব্যাক্তিগত',
        ];

        // বাগ ফিক্স ২: update() মেথডের সঠিক ব্যবহার
        $card->update($data);

        if ($chef) 
        {
            Cost::create([
                'name' => 'কার্ড বাবদ',
                'amount'=> $card->awards,
                'type'=> 1,
                'note'=>"$chef->name কে $card->product_name কার্ডের জন্য $card->awards টাকা দেওয়া হয়েছে।",
            ]); 
        }else {
            // যদি কার্ড ব্যক্তিগতভাবে ব্যবহার করা হয়, তাহলে এখানে অন্য কোনো লজিক যোগ করতে পারেন
            Cost::create([
                'name' => 'কার্ড বাবদ',
                'amount'=> $card->awards,
                'type'=> 1,
                'note'=>"পণ্যের গ্রাহককে $card->product_name কার্ডের জন্য $card->awards টাকা দেওয়া হয়েছে।",
            ]);
        }
        

        // সবকিছু ঠিক থাকলে ট্রানজেকশন কমিট করুন
        DB::commit(); 

        return response()->json([
            "status" => "success",
            "message" => "✅ কোড ভ্যালিড! পণ্যের নাম: {$card->product_name}। {$card->awards} টাকা পেয়েছেন। কার্ডের গ্রাহককে {$card->awards} টাকা দিতে বাধ্য থাকিবেন।",
        ]);   
 
    } catch (\Exception $e) {
        // যদি কোনো এরর হয়, তাহলে ট্রানজেকশন রোলব্যাক করুন
        DB::rollBack();

        // এরর লগ করা বা অন্য কোনো ব্যবস্থা নেওয়া যেতে পারে
        // Log::error($e->getMessage());

        return response()->json([
            "status" => "error",
            "message" => "❌ একটি অপ্রত্যাশিত ত্রুটি ঘটেছে। অনুগ্রহ করে আবার চেষ্টা করুন।",
        ], 500); // 500 Internal Server Error স্ট্যাটাস কোড পাঠানো ভালো
    }
}

    public function searchChef(Request $request)
    {
        $request->validate([
            "c_number" => "required|string"
        ]);

        $chef = Chef::where("c_number", $request->c_number)->first();

        if (!$chef) {
            return response()->json([
                "status" => "error",
                "message" => "❌ এই বহি নাম্বারে কোনো বাবুর্চি পাওয়া যায় নি। বাবুর্চির নাম এন্টি করতে চাইলে “01776487185” এই নাম্বারে যোগাযোগ করোন?"
            ]);
        }

        return response()->json([
            "status" => "success",
            "chef" => [
                "id" => $chef->id,
                "name" => $chef->name,
                "mobile" => $chef->m_number,
            ]
        ]);
    }

     public function show(Request $request)
    {
        $user = null;
        $claimableCards = collect(); // একটি খালি কালেকশন তৈরি করুন

        // যদি কোনো user_id দিয়ে সার্চ করা হয়
        if ($request->has('user_id')) {
            $user = User::find($request->user_id);

            if ($user) {
                // রিলেশন থেকে নির্দিষ্ট শর্ত মেনে কার্ডগুলো আনুন
               $claimableCards = $user->cards()
                                      ->where('is_used', 1)
                                      ->where('sr_pay', 0)
                                      ->get();
                
            }
        }

         
 
        return view('cards.claim', [ // ভিউ ফাইলের পাথ আপনার মতো দিন
            'user' => $user,
            'claimableCards' => $claimableCards,
        ]);
    }

     public function confirm(User $user)
    {
        
        // একই শর্তে যে কার্ডগুলো আপডেট করতে হবে, সেগুলো খুঁজে বের করুন
        $cardsToUpdate = $user->cards()
                              ->where('is_used', 1)
                              ->where('sr_pay', 0);
        
        
        // যদি আপডেট করার মতো কোনো কার্ড থাকে
        if ($cardsToUpdate->count() > 0) {   
            // একসাথে সব কার্ডের sr_pay এর মান 1 করে দিন
            $cardsToUpdate->update(['sr_pay' => 1]);
            

            // সফল হওয়ার মেসেজসহ আগের পেজে রিডাইরেক্ট করুন
            return redirect()->back()->with('success', 'পেমেন্ট সফলভাবে কনফার্ম করা হয়েছে!');
        }

        // যদি কোনো কারণে আপডেট করার মতো কার্ড না পাওয়া যায়
        return redirect()->back()->with('error', 'কনফার্ম করার জন্য কোনো কার্ড পাওয়া যায়নি।');
    }
 

    private function generateRandomCode($length = 10)
    {
        $characters =
            "0123456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charactersLength - 1); // secure random
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    private function generateUniqueCodes($count, $length = 9)
    {
        $codes = [];

        while (count($codes) < $count) {
            $code = $this->generateRandomCode($length);

            // DB এবং current batch উভয় জায়গায় ডুপ্লিকেট আছে কিনা চেক
            if (
                !in_array($code, $codes) &&
                !Card::where("code", $code)->exists()
            ) {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    public function claimedCards(Request $request)
    {
        // ডিফল্টভাবে 'আজ' (today) ফিল্টার সেট করা হলো
        $dateFilter = $request->input('date_filter', 'today');
        $userId = $request->input('used_by');

        // শুধুমাত্র ক্লেইম করা কার্ড
        $query = Card::where('is_used', 1);

        // ১. ইউজার আইডি অনুযায়ী ফিল্টার
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        // ২. তারিখ অনুযায়ী ফিল্টার
        switch ($dateFilter) {
            case 'yesterday':
                $query->whereDate('used_at', Carbon::yesterday());
                break;
            case 'today':
                $query->whereDate('used_at', Carbon::today());
                break;
            case 'last_7_days':
                $query->whereBetween('used_at', [Carbon::today()->subDays(6)->startOfDay(), Carbon::now()]);
                break;
            case 'last_15_days':
                $query->whereBetween('used_at', [Carbon::today()->subDays(14)->startOfDay(), Carbon::now()]);
                break;
            case 'last_month':
                $query->whereBetween('used_at', [Carbon::today()->subDays(29)->startOfDay(), Carbon::now()]);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('used_at', [$startDate, $endDate]);
                }
                break;
        }

        // ফিল্টার অনুযায়ী মোট কার্ডের সংখ্যা এবং মোট অ্যাওয়ার্ড এর যোগফল বের করা
        $totalCards = (clone $query)->count();
        $totalAwards = (clone $query)->sum('awards');

        // ৩. তারিখ অনুযায়ী প্রথম থেকে সাজানো (Ascending Order)
        $query->orderBy('used_at', 'asc')->orderBy('id', 'asc');

        // ৪. প্রিন্ট রিকোয়েস্ট চেক (প্রিন্ট এ ক্লিক করলে পেজিনেশন ছাড়া সব ডাটা আসবে)
        if ($request->has('print')) {
            $cards = $query->get();
            return view('cards.print_claimedCards', compact(
                'cards', 'dateFilter', 'totalCards', 'totalAwards'
            ));
        }

        // সাধারণ পেজিনেশন ডাটা (স্ক্রিনের জন্য)
        $cards = $query->paginate(20);
        
        // ফিল্টারে দেখানোর জন্য সব ইউজারদের লিস্ট
        $users = User::all(); 

        return view('cards.claimedCards', compact('cards', 'users', 'dateFilter', 'totalCards', 'totalAwards'));
    }


}
