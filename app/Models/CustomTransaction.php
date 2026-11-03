<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['custom_customer_id', 'type', 'amount', 'description', 'transaction_date'];

    public function customer()
    {
        return $this->belongsTo(CustomCustomer::class, 'custom_customer_id');
    }
}
