<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentAssignment extends Model
{
    protected $fillable = [
        'agent_id',
        'product_id',
        'quantity_assigned',
        'quantity_sold',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Quantity still available to sell */
    public function getQuantityRemainingAttribute(): int
    {
        return max(0, $this->quantity_assigned - $this->quantity_sold);
    }
}
