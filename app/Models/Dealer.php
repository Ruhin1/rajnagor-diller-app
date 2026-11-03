<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'name','phone','email','address','pname'
    ];

     // এক কাস্টমারের অনেক সেল থাকতে পারে
    public function deler()
    {
        return $this->hasMany(Sale::class, 'dealer_id');
    }

    public function sales()
    {
        // এখানে 'dealer_id' হলো sales টেবিলের foreign key
        return $this->hasMany(Sale::class, 'dealer_id');
    }

    // // কাস্টমারের মোট ডিউ
    public function getTotalDueAttribute()
    {
        return $this->deler()->sum('due');
    }
     public function payments()
    {
        
        
        return $this->hasMany(Payment::class, 'dealer_id');
    }
} 
 