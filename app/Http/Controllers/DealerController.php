<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dealer;

class DealerController extends Controller
{
    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:dealers,phone',
            'pname' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ], [
            'name.required' => 'ডিলারের নাম আবশ্যক।',
            'phone.required' => 'মোবাইল নম্বর আবশ্যক।',
            'phone.unique' => 'এই মোবাইল নম্বর ইতিমধ্যে রেজিস্টার্ড আছে।',
        ]);

        $dealer = Dealer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'ডিলার সফলভাবে যুক্ত হয়েছে।',
            'dealer' => $dealer
        ]);
    }
    public function index(Request $request)
    {
        // if($request->host() == 'mb.gffoodandbeverage.com'){
        //     return view('demo');
        // }
        return view('dealers.index');
    }

    public function list(Request $request)
    {
        
        $q = $request->q;
        return Dealer::query()
            ->when($q, fn($st)=>$st->where('name','like',"%$q%")
                                ->orWhere('phone','like',"%$q%")
                                
                                )
                                
                                
            ->orderBy('id','desc')
            ->get(['id','name','phone','address','pname','email']); 
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20|unique:dealers,phone',
            'email'   => 'nullable',
            'address' => 'nullable|string|max:500',
            'pname' => 'nullable|string|max:500',

        ]);

        $dealer = Dealer::create($data);

        return response()->json(['status' => 'success', 'dealer' => $dealer]);
    }

    public function update(Request $request, Dealer $dealer)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20|unique:dealers,phone,'.$dealer->id,
            'email'   => 'nullable',
            'address' => 'nullable|string|max:500',
            'pname' => 'nullable|string|max:500',
        ]);

        $dealer->update($data);

        return response()->json(['status' => 'success']);
    }

    public function destroy(Dealer $dealer)
    {
        $dealer->delete();
        return response()->json(['status' => 'success']);
    }
}
