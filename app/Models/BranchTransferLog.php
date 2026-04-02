<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTransferLog extends Model
{
    protected $fillable = [
        'product_list_id',
        'from_branch_id',
        'to_branch_id',
        'admin_id',
    ];

    public function productListItem(): BelongsTo
    {
        return $this->belongsTo(ProductListItem::class, 'product_list_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
