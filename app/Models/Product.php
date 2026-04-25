<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'mobileapi_device_id',
        'gsmarena_device_id',
        'name',
        'brand',
        'device_type',
        'price',
        'rating',
        'stock_quantity',
        'description',
        'images',
        'specifications',
    ];

    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
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
