<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MorichDailySaleItem extends Model
{
    use HasFactory;
    protected $fillable = ['morich_daily_sale_id', 'morich_product_id', 'quantity_sold_in_grams'];

    public function product()
    {
        return $this->belongsTo(MorichProduct::class, 'morich_product_id');
    }
    public function sale()
    {
        return $this->belongsTo(MorichDailySale::class, 'morich_daily_sale_id');
    }
}
