<?php

namespace App\Services;

use App\Models\AgentAssignment;
use App\Models\AgentProductListAssignment;
use App\Models\ProductListItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AgentProductAssignmentService
{
    /**
     * @param  array<int, int>  $productListIds
     * @return int number of devices assigned
     *
     * @throws \InvalidArgumentException
     */
    public function assignToAgent(User $agent, int $productId, array $productListIds): int
    {
        if ($agent->role !== 'agent') {
            throw new \InvalidArgumentException('Selected user is not an agent.');
        }

        $ids = array_values(array_unique(array_map('intval', $productListIds)));

        return DB::transaction(function () use ($agent, $productId, $ids) {
            $added = 0;

            foreach ($ids as $listId) {
                $item = ProductListItem::lockForUpdate()->find($listId);

                if (! $item || ! $item->isCatalogProduct($productId)) {
                    throw new \InvalidArgumentException('One or more IMEIs do not belong to the selected product.');
                }

                if ($item->isSold()) {
                    throw new \InvalidArgumentException('One or more devices are already sold.');
                }

                if (! $item->isPurchasePaid()) {
                    throw new \InvalidArgumentException('One or more devices are not from an eligible purchase (paid, partial, unpaid, or purchase still has IMEI limit remaining).');
                }

                if ($item->agentProductListAssignment) {
                    throw new \InvalidArgumentException('One or more devices are already assigned to an agent.');
                }

                AgentProductListAssignment::create([
                    'agent_id' => $agent->id,
                    'product_list_id' => $item->id,
                ]);
                $added++;
            }

            if ($added === 0) {
                throw new \InvalidArgumentException('No devices were assigned.');
            }

            $assignment = AgentAssignment::firstOrNew([
                'agent_id' => $agent->id,
                'product_id' => $productId,
            ]);
            $assignment->quantity_assigned = (int) ($assignment->quantity_assigned ?? 0) + $added;
            $assignment->save();

            return $added;
        });
    }
}
