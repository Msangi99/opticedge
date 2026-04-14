<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNeed extends Model
{
    protected $fillable = [
        'agent_id',
        'category_id',
        'product_id',
        'customer_name',
        'customer_phone',
        'branch_id',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
