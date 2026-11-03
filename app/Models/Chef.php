<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chef extends Model
{
    use HasFactory; 
    protected $fillable = ['c_number','name','m_number'];
    public function getCardChefs()
    {
        return $this->hasMany(GetCardChef::class, 'chef_id');
    }
}
