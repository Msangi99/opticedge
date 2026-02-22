<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListItem extends Model
{
    protected $table = 'product_list';

    protected $fillable = [
        'stock_id',
        'category_id',
        'model',
        'imei_number',
        'product_id',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function agentSale()
    {
        return $this->belongsTo(AgentSale::class, 'agent_sale_id');
    }

    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }
}
