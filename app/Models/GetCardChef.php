<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GetCardChef extends Model
{
    use HasFactory; 
    protected $fillable = ['chef_id','p_name'];
}
