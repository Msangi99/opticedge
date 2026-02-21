<?php

namespace App\Services;

use App\Models\AgentAssignment;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;

class DashboardFinancialService
{
    public function __construct(
        protected DistributionSaleService $distributionSaleService
    ) {}

    /**
     * Total pending (not paid) from purchases.
     */
    public function payables(): float
    {
        $total = 0;
        foreach (Purchase::all() as $purchase) {
            $totalAmount = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price);
            $total += max(0, $totalAmount - ($purchase->paid_amount ?? 0));
        }
        return (float) $total;
    }

    /**
     * Total pending (not collected) from Distribution Sales (balance).
     */
    public function receivables(): float
    {
        return (float) DistributionSale::get()->sum(fn ($s) => (float) ($s->balance ?? max(0, ($s->total_selling_value ?? 0) - ($s->paid_amount ?? 0))));
    }

    /**
     * Total value of our stock (products.stock_quantity * cost per unit).
     */
    public function stockInHandValue(): float
    {
        $total = 0;
        foreach (Product::all() as $product) {
            $buyPrice = $this->distributionSaleService->getBuyPriceForProduct($product->id);
            $qty = (int) ($product->stock_quantity ?? 0);
            $total += $buyPrice * $qty;
        }
        return (float) $total;
    }

    /**
     * Total value of stocks given to agents (with agents, not yet sold).
     */
    public function cashInHand(): float
    {
        $total = 0;
        $assignments = AgentAssignment::with('product')->get();
        foreach ($assignments as $assignment) {
            $remaining = max(0, ($assignment->quantity_assigned ?? 0) - ($assignment->quantity_sold ?? 0));
            if ($remaining > 0 && $assignment->product_id) {
                $buyPrice = $this->distributionSaleService->getBuyPriceForProduct($assignment->product_id);
                $total += $buyPrice * $remaining;
            }
        }
        return (float) $total;
    }

    /**
     * Sum of receivables, stock in hand value, and cash in hand.
     */
    public function totalValue(): float
    {
        return $this->receivables() + $this->stockInHandValue() + $this->cashInHand();
    }

    /**
     * Profit from Distribution Sales + Agent Sales profit.
     */
    public function grossProfit(): float
    {
        $distProfit = (float) DistributionSale::sum('profit');
        $agentProfit = (float) AgentSale::sum('profit');
        return $distProfit + $agentProfit;
    }

    /**
     * Total from Expenses section (admin expenses).
     */
    public function totalExpenses(): float
    {
        return (float) Expense::sum('amount');
    }

    /**
     * Gross profit - Total expenses.
     */
    public function netProfit(): float
    {
        return $this->grossProfit() - $this->totalExpenses();
    }

    /**
     * Get all financial metrics as an array.
     */
    public function getMetrics(): array
    {
        return [
            'payables' => $this->payables(),
            'receivables' => $this->receivables(),
            'stock_in_hand_value' => $this->stockInHandValue(),
            'cash_in_hand' => $this->cashInHand(),
            'total_value' => $this->totalValue(),
            'gross_profit' => $this->grossProfit(),
            'total_expenses' => $this->totalExpenses(),
            'net_profit' => $this->netProfit(),
        ];
    }
}
