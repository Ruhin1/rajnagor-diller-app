<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MorichDailySale extends Model
{
    use HasFactory;
    protected $fillable = ['sale_date', 'notes', 'user_id'];

    public function items()
    {
        return $this->hasMany(MorichDailySaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
