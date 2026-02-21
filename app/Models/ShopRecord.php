<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRecord extends Model
{
    protected $fillable = [
        'product_id',
        'opening_stock',
        'quantity_sold',
        'transfer_quantity',
        'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
