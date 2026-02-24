<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'price',
        'rating',
        'stock_quantity',
        'description',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function productListItems()
    {
        return $this->hasMany(ProductListItem::class, 'product_id');
    }
}
