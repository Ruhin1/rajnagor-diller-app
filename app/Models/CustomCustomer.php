<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCustomer extends Model
{
    use HasFactory;
     protected $fillable = ['name', 'address', 'due_amount'];

    public function transactions()
    {
        return $this->hasMany(CustomTransaction::class)->orderBy('transaction_date', 'asc');
    }
}
