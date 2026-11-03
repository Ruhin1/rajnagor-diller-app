<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;

class ProductController extends Controller
{
    public function index(Request $request){ 
        $products = Product::latest()->get();  
        if($request->ajax()){
            
            return response()->json($products);
        }else{
            return view("product",["products"=>$products]); 
        }  
    }
    public function store(Request $request)
    {
        $product = new Product();
        $product->name = $request->name;
        $product->quantity = $request->quantity;
        $product->cp = $request->cp;
        $product->dipo = $request->dipo;
        $product->dp = $request->dp;
        $product->tp = $request->tp;
        $product->mrp = $request->mrp;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Move করে শুধু public/products এ ফাইল রাখবে
            $image->move(public_path('products'), $filename);

            // DB তে শুধু path সেভ হবে
            $product->image = "products/" . $filename;
        }


        $product->save(); 
        
        return response()->json(['status' => 'success', 'product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->name = $request->name;
        // $product->quantity = $request->quantity;
        $product->cp = $request->cp;
        $product->dipo = $request->dipo;
        $product->dp = $request->dp;
        $product->tp = $request->tp;
        $product->mrp = $request->mrp;

        if ($request->hasFile('image')) {
            // পুরোনো ইমেজ রিমুভ করা
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            // নতুন ইমেজ আপলোড
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('products'), $filename);

            // ডাটাবেজে path সেভ
            $product->image = "products/" . $filename;
        }

        $product->save();

        return response()->json(['status' => 'success', 'product' => $product]);
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'note'     => 'nullable|string|max:255',
        ]);
    
        $product = Product::findOrFail($id);
    
        $oldStock = (int) $product->quantity;
        $newStock = (int) $request->quantity;
        $diff     = $newStock - $oldStock;
    
        // স্টক আসলে পরিবর্তন হলে তবেই লগ তৈরি হবে (একই মান দিয়ে সেভ করলে লগ হবে না)
        if ($diff !== 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type'       => $diff > 0 ? 'in' : 'out',
                'quantity'   => abs($diff),
                'old_stock'  => $oldStock,
                'new_stock'  => $newStock,
                'note'       => $request->note,
                'created_by' => auth()->id(), // অথেনটিকেশন না থাকলে null থাকবে, সমস্যা নেই
            ]);
        }
    
        $product->quantity = $newStock;
        $product->save();
    
        return response()->json(['status' => 'success', 'product' => $product]);
    }

    public function getStockMovements($id)
    {
        $product = Product::findOrFail($id);
    
        $movements = StockMovement::with('creator')
            ->where('product_id', $id)
            ->latest()
            ->get()
            ->map(function ($m) {
                return [
                    'id'         => $m->id,
                    'type'       => $m->type,
                    'quantity'   => $m->quantity,
                    'old_stock'  => $m->old_stock,
                    'new_stock'  => $m->new_stock,
                    'note'       => $m->note,
                    'created_by' => $m->creator->name ?? null,
                    'created_at' => $m->created_at->format('d M Y, h:i A'),
                ];
            });
    
        return response()->json([
            'status'       => 'success',
            'product_name' => $product->name,
            'movements'    => $movements,
        ]);
    }

    public function indexStockMovements(Request $request)
    {
        $query = StockMovement::with(['product', 'creator'])->latest();
 
        if ($request->filled('product')) {
            $search = $request->product;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
 
        if ($request->filled('type') && in_array($request->type, ['in', 'out'])) {
            $query->where('type', $request->type);
        }
 
        // ফিল্টার/পেজিনেশন লিংকে query string ধরে রাখার জন্য withQueryString()
        $movements = $query->paginate(20)->withQueryString();
 
        return view('stock_movements.index', compact('movements'));
    }

    public function updateStatus(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->status = $request->status;
        $product->save();

        return response()->json(['success' => true, 'status' => $product->status]);
    }


//sdfsdfdsfdfsdfs

}
