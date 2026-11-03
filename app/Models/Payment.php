<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = ['sale_id','amount','method','paid_at','reference','user_id','srname','customer_id','confrim','dealer_id','is_dipo'];
    public function sale(){ return $this->belongsTo(Sale::class); }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
 