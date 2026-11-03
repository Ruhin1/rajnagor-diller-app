<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chef;

class ChefController extends Controller 
{
    // app/Http/Controllers/YourChefController.php

        public function create(Request $request) {
            // রিকোয়েস্ট থেকে সার্চের শব্দটি নিন
            $search = $request->search;

            $chefs = Chef::query() // query() দিয়ে শুরু করলে শর্ত যোগ করা সহজ হয়
                ->when($search, function ($query, $search) {
                    // যদি $search ভ্যারিয়েবলে কোনো মান থাকে, তবেই কেবল এই অংশটি কাজ করবে
                    return $query->where('c_number', 'like', '%' . $search . '%')
                    ->orWhere('name', 'LIKE', '%'. $search .    '%')
                    ->orWhere('m_number', 'LIKE', '%'. $search .    '%');
                   
                })
                ->withCount('getCardChefs')
                ->orderBy('get_card_chefs_count', 'desc') // কার্ড ব্যবহারের সংখ্যা অনুযায়ী সাজানো
                ->paginate(30);

            return view("chef.create", compact("chefs"));
        }

    public function store(Request $request){
        $request->validate([
            "c_number"=> "required|unique:chefs,c_number",
            "name"=> "required",
            "m_number" => "required"
        ]);

        Chef::create([
            'c_number'=> $request->c_number,
            'name' => $request->name,
            'm_number'=> $request->m_number,
        ]);

        return back()->with(
            "success",
            "বাবুর্চি ইনসার্ট করা হয়েছে"
        );
    }

    public function update(Request $request, Chef $chef)
{
    $request->validate([
        'c_number' => 'required|numeric|unique:chefs,c_number,' . $chef->id,
        'name' => 'required|string|max:255',
        'm_number' => 'required|string|max:15',
    ]);

    $chef->update($request->all());

    return redirect()->route('chef.create')->with('success', 'বাবুর্চির তথ্য সফলভাবে আপডেট হয়েছে।');
}

public function destroy(Chef $chef)
{
    try {
        $chef->delete();
        return redirect()->route('chef.create')->with('success', 'বাবুর্চিকে সফলভাবে ডিলিট করা হয়েছে।');
    } catch (\Exception $e) {
        return redirect()->route('chef.create')->with('error', 'একটি সমস্যা হয়েছে, ডিলিট করা সম্ভব হয়নি।');
    }
}
}   
