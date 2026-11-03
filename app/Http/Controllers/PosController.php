<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;


class PosController extends Controller
{
    public function otherSale(){ return view('pos.othersale'); }
    public function index(){ return view('pos.index'); }
    public function diposale(Request $request){ 
        // if($request->host() == 'mb.gffoodandbeverage.com'){
        //     return redirect()->route('pos.index');
        // }
        
        return view('dipo.diposale'); 
    }

    
    public function products(Request $request)
    {
        $q = $request->q; 

        return Product::query()
            ->where('status', 1)
            ->when($q, fn($st) => $st->where('name', 'like', "%$q%"))
            ->paginate(10, ['id','name','tp as price','quantity','image','dp','dipo']);
    }


    public function customers(Request $request)
    {
        $q = $request->q;
        return Customer::query()->where('is_dipu', 0)
            ->when($q, fn($st)=>$st->where('name','like',"%$q%")
                                ->orWhere('number','like',"%$q%")
                                ->orWhere('shop_name','like',"%$q%")
                                )
                                
                                
            ->orderBy('id','desc')
            ->get(['id','name','number','shop_name','shop_address']);    
    }

}
