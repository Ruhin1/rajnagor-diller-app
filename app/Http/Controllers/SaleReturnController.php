<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\selsReturn;
use App\Models\Sale;
use App\Models\Product;



class SaleReturnController extends Controller
{
    // Return form দেখানো
    public function create($sale_id)
    {
        $sale = Sale::with('items.product')->findOrFail($sale_id);
        return view('returns.create', compact('sale'));
    }

    // Return process করা
    public function store(Request $request, Sale $sale)
{
    $returns = $request->returns ?? [];

    foreach ($returns as $itemId => $qty) {
        $saleItem = $sale->items()->find($itemId);
        if ($saleItem && $qty > 0 && $qty <= $saleItem->quantity) {

            // Update stock
            $saleItem->product->increment('quantity', $qty);

            // Update sale_item
            $saleItem->quantity -= $qty;
            $saleItem->total = $saleItem->quantity * $saleItem->price;
            $saleItem->save();
        }
    }

    // Recalculate totals
    $subtotal = $sale->items()->sum('total');
    $discount = $request->discount ?? $sale->discount;
    $tax      = $request->tax ?? $sale->tax;
    $shipping = $request->shipping ?? $sale->shipping;
    $paid     = $request->paid ?? $sale->paid;

    $total = $subtotal - $discount + $tax + $shipping;
    $due   = $total - $paid;

    $sale->update([
        'subtotal' => $subtotal,
        'discount' => $discount,
        'tax'      => $tax,
        'shipping' => $shipping,
        'total'    => $total,
        'paid'     => $paid,
        'due'      => $due,
    ]);

    return redirect()->route('sales.show', $sale->id)
                     ->with('success', 'Return processed successfully!');
    
}

public function search(Request $request)
{
    // $sale = Sale::with('items.product')->find($request->sale_id);

    // if (!$sale) {
    //     return redirect()->back()->with('error', 'Sale not found.');
    // }

    // return redirect()->route('returns.create', $sale->id);
    return view('returns.search');
}

public function searchupdate(Request $request)
{
    $sale = Sale::where('order_status','=',0)->with('items.product')->find($request->sale_id);

    if (!$sale) {
        return redirect()->back()->with('error', 'এই ইনভয়েস নাম্বারে কোনো ইনভয়েস খুজে পাওয়া যায় নি');
    }

    return redirect()->route('returns.create', $sale->id);

}

}
