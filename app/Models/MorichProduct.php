<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MorichProduct extends Model
{
    use HasFactory;
    use HasFactory;
    protected $fillable = ['name', 'stock_in_grams'];
    public function stockEntries()
{
    return $this->hasMany(MorichStockEntry::class, 'morich_product_id');
}
}
