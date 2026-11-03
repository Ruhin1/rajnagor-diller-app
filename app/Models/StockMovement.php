<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'old_stock',
        'new_stock',
        'note',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // যিনি স্টক পরিবর্তন করেছেন — আপনার User মডেলের নেমস্পেস আলাদা হলে ঠিক করে নিন
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}