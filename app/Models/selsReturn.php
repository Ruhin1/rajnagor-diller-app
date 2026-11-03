<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class selsReturn extends Model
{
    use HasFactory;
    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'price', 'subtotal', 'reason'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
