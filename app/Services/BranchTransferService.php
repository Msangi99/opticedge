<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchTransferLog;
use App\Models\ProductListItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BranchTransferService
{
    /**
     * @param  array<int>  $productListIds
     * @param  int|null  $expectedFromBranchId  Must match each item's effective branch (null = unassigned only)
     */
    public function transferItems(array $productListIds, ?int $expectedFromBranchId, int $toBranchId, User $admin): void
    {
        $toBranch = Branch::findOrFail($toBranchId);

        $ids = array_values(array_unique(array_map('intval', $productListIds)));
        if ($ids === []) {
            throw new \InvalidArgumentException('Select at least one device.');
        }

        DB::transaction(function () use ($ids, $expectedFromBranchId, $toBranch, $admin) {
            foreach ($ids as $listId) {
                $item = ProductListItem::with(['purchase', 'branch'])->lockForUpdate()->find($listId);
                if (! $item || $item->isSold()) {
                    throw new \InvalidArgumentException('One or more devices are invalid or already sold.');
                }

                $fromId = $item->effectiveBranchId();
                if ($expectedFromBranchId === null) {
                    if ($fromId !== null) {
                        throw new \InvalidArgumentException('One or more devices are not unassigned.');
                    }
                } elseif ($fromId !== $expectedFromBranchId) {
                    throw new \InvalidArgumentException('One or more devices are not in the selected source branch.');
                }

                if ($fromId === (int) $toBranch->id) {
                    throw new \InvalidArgumentException('Source and destination branch are the same for one or more devices.');
                }

                $item->update(['branch_id' => $toBranch->id]);

                BranchTransferLog::create([
                    'product_list_id' => $item->id,
                    'from_branch_id' => $fromId,
                    'to_branch_id' => $toBranch->id,
                    'admin_id' => $admin->id,
                ]);
            }
        });
    }

    /**
     * IMEIs at branch (effective), unsold, optionally filter product.
     */
    public function queryItemsForBranch(int $branchId, ?int $productId = null)
    {
        $q = ProductListItem::query()
            ->with(['product.category', 'purchase', 'stock', 'branch'])
            ->whereNull('sold_at');

        $q->where(function ($outer) use ($branchId) {
            $outer->where('branch_id', $branchId)
                ->orWhere(function ($inner) use ($branchId) {
                    $inner->whereNull('branch_id')
                        ->whereHas('purchase', fn ($p) => $p->where('branch_id', $branchId));
                });
        });

        if ($productId !== null) {
            $q->where('product_id', $productId);
        }

        return $q->orderBy('imei_number');
    }

    /**
     * Unsold items with no effective branch (no row override and no purchase branch).
     */
    public function queryUnassignedItems(?int $productId = null)
    {
        $q = ProductListItem::query()
            ->with(['product.category', 'purchase', 'stock', 'branch'])
            ->whereNull('sold_at')
            ->whereNull('branch_id')
            ->where(function ($outer) {
                $outer->whereNull('purchase_id')
                    ->orWhereHas('purchase', fn ($p) => $p->whereNull('branch_id'));
            });

        if ($productId !== null) {
            $q->where('product_id', $productId);
        }

        return $q->orderBy('imei_number');
    }
}
