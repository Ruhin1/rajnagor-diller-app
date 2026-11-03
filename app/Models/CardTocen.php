<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardTocen extends Model
{
    use HasFactory; 
    protected $fillable = ['product_name','token','card_amount','awards']; 
}
