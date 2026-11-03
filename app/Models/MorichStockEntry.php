<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MorichStockEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'morich_product_id', 
        'user_id', 
        'quantity_added_in_grams', 
        'type', 
        'notes'
    ];

    public function product()
    {
        return $this->belongsTo(MorichProduct::class, 'morich_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
