<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProductListAssignment extends Model
{
    protected $fillable = [
        'agent_id',
        'product_list_id',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function productListItem()
    {
        return $this->belongsTo(ProductListItem::class, 'product_list_id');
    }
}
