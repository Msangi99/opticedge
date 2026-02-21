<?php

namespace App\Services;

use App\Models\DistributionSale;
use App\Models\Order;
use App\Models\Purchase;

class DistributionSaleService
{
    public const REFERRER_COMMISSION = 500000;

    /**
     * Create distribution sale records from a dealer order.
     * Buy price from purchase data; sell price from order item; commission 500k on first purchase if dealer has referrer.
     */
    public function createFromOrder(Order $order, string $status = 'pending'): void
    {
        $user = $order->user;
        if ($user->role !== 'dealer') {
            return;
        }

        $order->load(['items.product.category']);
        $dealerName = $user->name;
        $sellerName = $user->referrer?->name;

        $isFirstPurchase = !DistributionSale::where('dealer_id', $user->id)->exists();
        $hasReferrer = (bool) $user->referred_by;
        $giveCommission = $isFirstPurchase && $hasReferrer;

        $first = true;
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $buyPrice = $this->getBuyPriceForProduct($product->id);
            $sellPrice = (float) $item->price;
            $qty = (int) $item->quantity;

            $totalBuy = $buyPrice * $qty;
            $totalSell = $sellPrice * $qty;
            $commission = ($giveCommission && $first) ? self::REFERRER_COMMISSION : 0;
            $profit = $totalSell - $totalBuy - $commission;

            DistributionSale::create([
                'dealer_id' => $user->id,
                'order_id' => $order->id,
                'dealer_name' => $dealerName,
                'seller_name' => $sellerName,
                'product_id' => $product->id,
                'quantity_sold' => $qty,
                'purchase_price' => $buyPrice,
                'selling_price' => $sellPrice,
                'total_purchase_value' => $totalBuy,
                'total_selling_value' => $totalSell,
                'profit' => $profit,
                'commission' => $commission,
                'status' => $status,
                'to_be_paid' => $totalSell,
                'paid_amount' => 0,
                'balance' => $totalSell,
                'date' => $order->created_at->toDateString(),
            ]);

            $first = false;
        }
    }

    /**
     * Get buy price (unit) for a product from purchase data (latest purchase unit_price, else 0).
     */
    public function getBuyPriceForProduct(int $productId): float
    {
        $purchase = Purchase::where('product_id', $productId)
            ->latest('date')
            ->first();

        return $purchase ? (float) $purchase->unit_price : 0;
    }
}
