<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSale extends Model
{
    protected $fillable = [
        'agent_id',
        'customer_name',
        'seller_name',
        'product_id',
        'quantity_sold',
        'purchase_price',
        'selling_price',
        'total_purchase_value',
        'total_selling_value',
        'profit',
        'commission_paid',
        'date_of_collection',
        'balance',
        'stock_remaining',
        'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
