<?php

namespace App\Observers;

use App\Models\MorichProduct;
use App\Models\MorichStockEntry;
use Illuminate\Support\Facades\Auth;

class MorichProductObserver
{
    /**
     * Handle the MorichProduct "created" event.
     *
     * @param  \App\Models\MorichProduct  $morichProduct
     * @return void
     */
     public function created(MorichProduct $product)
    {
        if ($product->stock_in_grams > 0) {
            MorichStockEntry::create([
                'morich_product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity_added_in_grams' => $product->stock_in_grams,
                'type' => 'initial_stock',
                'notes' => 'প্রাথমিক স্টক এন্ট্রি',
            ]);
        }
    }

    /**
     * Handle the MorichProduct "updating" event.
     * একটি পণ্য আপডেট হওয়ার আগে চেক করা হবে স্টক বেড়েছে কিনা।
     */
    public function updating(MorichProduct $product)
    {
        // শুধুমাত্র stock_in_grams কলাম পরিবর্তন হলেই এই লজিক কাজ করবে
        if ($product->isDirty('stock_in_grams')) {
            $oldStock = $product->getOriginal('stock_in_grams');
            $newStock = $product->stock_in_grams;
            
            // যদি নতুন স্টক পুরোনো স্টকের চেয়ে বেশি হয়, তার মানে স্টক যোগ করা হয়েছে
            if ($newStock > $oldStock) {
                $quantityAdded = $newStock - $oldStock;

                MorichStockEntry::create([
                    'morich_product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'quantity_added_in_grams' => $quantityAdded,
                    'type' => 'stock_in',
                    'notes' => 'নতুন স্টক যোগ করা হয়েছে',
                ]);
            }
        }
    }

    /**
     * Handle the MorichProduct "deleted" event.
     *
     * @param  \App\Models\MorichProduct  $morichProduct
     * @return void
     */
    public function deleted(MorichProduct $morichProduct)
    {
        //
    }

    /**
     * Handle the MorichProduct "restored" event.
     *
     * @param  \App\Models\MorichProduct  $morichProduct
     * @return void
     */
    public function restored(MorichProduct $morichProduct)
    {
        //
    }

    /**
     * Handle the MorichProduct "force deleted" event.
     *
     * @param  \App\Models\MorichProduct  $morichProduct
     * @return void
     */
    public function forceDeleted(MorichProduct $morichProduct)
    {
        //
    }
}
