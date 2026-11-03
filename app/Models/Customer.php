<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        "name","number","shop_address","shop_name"
    ];

    // এক কাস্টমারের অনেক সেল থাকতে পারে
    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    // কাস্টমারের মোট ডিউ
    public function getTotalDueAttribute()
    {
        return $this->sales()->sum('due');
    }

     public function payments()
    {
        
        
        return $this->hasMany(Payment::class, 'customer_id');
    }

    
} 
 