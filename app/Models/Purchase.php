<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'name',
        'stock_id',
        'product_id',
        'quantity',
        'unit_price',
        'distributor_name',
        'total_amount',
        'paid_date',
        'paid_amount',
        'payment_status',
        'payment_receipt_image',
        'payment_option_id',
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

    public function paymentOption()
    {
        return $this->belongsTo(PaymentOption::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class)->latest('paid_date')->latest('created_at');
    }
}
