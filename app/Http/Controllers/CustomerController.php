<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255|unique:customers,number',
            'shop_name' => 'nullable|string|max:255',
            'shop_address' => 'nullable|string',
        ], [
            'name.required' => 'কাস্টমারের নাম আবশ্যক।',
            'number.required' => 'মোবাইল নম্বর আবশ্যক।',
            'number.unique' => 'এই মোবাইল নম্বর ইতিমধ্যে রেজিস্টার্ড আছে।',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'কাস্টমার সফলভাবে যুক্ত হয়েছে।',
            'customer' => $customer
        ]);
    }

    public function index(Request $request){ 
        $products = Customer::where('is_dipu', '!=', 1)->latest()->get();
        if($request->ajax()){    
            return response()->json($products);
        }else{
            return view("customers",["products"=>$products]); 
        }  
    }


    public function store(Request $request)
    { 
         $request->validate([
            'number' => 'unique:customers,number',
            // ... other validation rules
         ]);
        $product = new Customer();
        $product->name = $request->name;
        $product->number = $request->number;
        $product->shop_name = $request->shop_name;
        $product->shop_address = $request->shop_address;
        $product->save(); 
        
        return response()->json(['status' => 'success', 'product' => "product"]);
    }

    public function update(Request $request, $id)
    {
        $product = Customer::findOrFail($id);
        $product->name = $request->up_name;
        $product->number = $request->up_number;
        $product->shop_name = $request->up_shop_name;
        $product->shop_address = $request->up_shop_address;
        $product->save(); 
        return response()->json(['status' => 'success', 'product' => $product]);
    }

    public function destroy(Request $request, $id){
        $product = Customer::findOrFail($id);
        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Customer deleted successfully']);

    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $customers = Customer::where('name', 'like', "%$q%")
                    ->orWhere('number', 'like', "%$q%")
                    ->limit(10)
                    ->get();

        return response()->json($customers);
    }


    // ==================================
    public function dindex(Request $request){ 
        $products = Customer::where('is_dipu', true)->latest()->get();
        if($request->ajax()){    
            return response()->json($products);
        }else{
            return view("dcustomers",["products"=>$products]); 
        }  
    }

    public function dstore(Request $request)
    { 
        $request->validate([
            'number' => 'unique:customers,number',
            // ... other validation rules
         ]);
        $product = new Customer();
        $product->name = $request->name;
        $product->number = $request->number;
        $product->shop_name = $request->shop_name;
        $product->shop_address = $request->shop_address;
        $product->is_dipu = 1   ; // ডিপু কাস্টমার হিসেবে চিহ্নিত করা হচ্ছে
        $product->save(); 
        
        return response()->json(['status' => 'success', 'product' => "product"]);
    }

    public function dupdate(Request $request, $id)
    {
        $product = Customer::findOrFail($id);
        $product->name = $request->up_name;
        $product->number = $request->up_number;
        $product->shop_name = $request->up_shop_name;
        $product->shop_address = $request->up_shop_address;
        $product->is_dipu = 1; // ডিপু কাস্টমার হিসেবে চিহ্নিত করা হচ্ছে
        $product->save(); 
        return response()->json(['status' => 'success', 'product' => $product]);
    }

    public function ddestroy(Request $request, $id){
        $product = Customer::findOrFail($id);
        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Customer deleted successfully']);

    } 




   

   

}
