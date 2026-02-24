<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'stock_id',
        'product_id',
        'quantity',
        'unit_price',
        'distributor_name',
        'total_amount',
        'paid_date',
        'paid_amount',
        'payment_status',
        'date',
        'limit_status',
        'limit_remaining',
        'sell_price',
    ];

    public function productListItems()
    {
        return $this->hasMany(ProductListItem::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
