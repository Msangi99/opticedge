<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentProductTransferItem extends Model
{
    protected $fillable = [
        'agent_product_transfer_id',
        'product_list_id',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(AgentProductTransfer::class, 'agent_product_transfer_id');
    }

    public function productListItem(): BelongsTo
    {
        return $this->belongsTo(ProductListItem::class, 'product_list_id');
    }
}
