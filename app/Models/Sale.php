<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
     protected $casts = [
        'ddate' => 'date', // এখানে বলছি এটা date টাইপ
    ];
    use HasFactory;
    protected $fillable = [
        'customer_id','subtotal','discount','tax','shipping','total',
        'paid','due','payment_method','note','status','ddate','srname','dealer_id','user_id','order_status','guest_name','guest_phone','guest_sell_type','is_dipu',
    ];
    public function items(){ return $this->hasMany(SaleItem::class); }
    public function dealer(){ return $this->belongsTo(Dealer::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
    
    public function usersr()
    {
        return $this->belongsTo(User::class); 
    }
 
 

    
}
