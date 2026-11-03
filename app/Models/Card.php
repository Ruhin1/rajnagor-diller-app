<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'product_name','is_used','used_at','used_by','sr_pay','identy_number','user_id','customer_name','awards','product_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
