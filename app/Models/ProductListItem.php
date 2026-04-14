<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListItem extends Model
{
    protected $table = 'product_list';

    protected $fillable = [
        'stock_id',
        'purchase_id',
        'branch_id',
        'category_id',
        'model',
        'imei_number',
        'product_id',
        'sold_at',
        'agent_sale_id',
        'pending_sale_id',
        'agent_credit_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Filter rows whose effective branch matches $branchId (row branch or purchase branch).
     * When $branchId is null, no filter is applied.
     */
    public function scopeWhereEffectiveBranch($query, ?int $branchId)
    {
        if ($branchId === null) {
            return $query;
        }

        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->orWhere(function ($inner) use ($branchId) {
                    $inner->whereNull('branch_id')
                        ->whereHas('purchase', fn ($p) => $p->where('branch_id', $branchId));
                });
        });
    }

    /**
     * Location branch: explicit on row, else from linked purchase.
     */
    public function effectiveBranchId(): ?int
    {
        if ($this->branch_id !== null) {
            return (int) $this->branch_id;
        }
        $this->loadMissing('purchase');

        return $this->purchase?->branch_id !== null ? (int) $this->purchase->branch_id : null;
    }

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

    public function pendingSale()
    {
        return $this->belongsTo(PendingSale::class, 'pending_sale_id');
    }

    public function agentCredit()
    {
        return $this->belongsTo(AgentCredit::class, 'agent_credit_id');
    }

    public function agentProductListAssignment()
    {
        return $this->hasOne(AgentProductListAssignment::class, 'product_list_id');
    }

    /**
     * Unsold IMEIs for a product that are paid-for and not yet assigned to any agent.
     */
    public function scopeAssignableToAgent($query, int $productId)
    {
        return $query
            ->where('product_id', $productId)
            ->whereNull('sold_at')
            ->whereDoesntHave('agentProductListAssignment')
            ->where(function ($q) {
                $q->whereHas('purchase', fn ($p) => $p->where('payment_status', 'paid'))
                    ->orWhere(function ($q2) {
                        $q2->whereNull('purchase_id')
                            ->whereExists(function ($sub) {
                                $sub->selectRaw('1')
                                    ->from('purchases')
                                    ->whereColumn('purchases.stock_id', 'product_list.stock_id')
                                    ->whereColumn('purchases.product_id', 'product_list.product_id')
                                    ->where('purchases.payment_status', 'paid');
                            });
                    });
            });
    }

    /**
     * Whether the linked purchase (or stock+product purchase) is fully paid.
     */
    public function isPurchasePaid(): bool
    {
        if ($this->purchase_id) {
            $this->loadMissing('purchase');

            return $this->purchase && $this->purchase->payment_status === 'paid';
        }

        if ($this->stock_id && $this->product_id) {
            $p = Purchase::where('stock_id', $this->stock_id)
                ->where('product_id', $this->product_id)
                ->latest('date')
                ->first();

            return $p && $p->payment_status === 'paid';
        }

        return false;
    }

    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }
}
