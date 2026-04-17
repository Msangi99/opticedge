<?php

namespace App\Services;

use App\Models\AgentAssignment;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Per-dealer (distributor) totals: billed, collected, outstanding — for dashboard receivables detail.
     *
     * @return list<array{dealer_name: string, dealer_id: int|null, total_billed: float, total_paid: float, outstanding: float}>
     */
    public function getDistributorReceivableBreakdown(): array
    {
        $sales = DistributionSale::query()
            ->with(['dealer:id,name'])
            ->get(['id', 'dealer_id', 'dealer_name', 'total_selling_value', 'paid_amount', 'balance']);

        return $sales
            ->groupBy(function (DistributionSale $s) {
                if ($s->dealer_id) {
                    return 'id:' . $s->dealer_id;
                }

                return 'name:' . md5(strtolower(trim((string) ($s->dealer_name ?? ''))));
            })
            ->map(function ($group) {
                /** @var \Illuminate\Support\Collection<int, DistributionSale> $group */
                $first = $group->first();
                $name = $first->dealer?->name
                    ?? (trim((string) ($first->dealer_name ?? '')) !== '' ? $first->dealer_name : 'Unknown dealer');

                $totalBilled = (float) $group->sum(fn (DistributionSale $s) => (float) ($s->total_selling_value ?? 0));
                $totalPaid = (float) $group->sum(fn (DistributionSale $s) => (float) ($s->paid_amount ?? 0));
                $outstanding = (float) $group->sum(function (DistributionSale $s) {
                    if ($s->balance !== null) {
                        return (float) $s->balance;
                    }
                    $t = (float) ($s->total_selling_value ?? 0);
                    $p = (float) ($s->paid_amount ?? 0);

                    return max(0, $t - $p);
                });

                return [
                    'dealer_name' => $name,
                    'dealer_id' => $first->dealer_id,
                    'total_billed' => $totalBilled,
                    'total_paid' => $totalPaid,
                    'outstanding' => $outstanding,
                ];
            })
            ->values()
            ->sortByDesc('outstanding')
            ->values()
            ->all();
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
            'total_purchase_buy_price' => $this->totalPurchaseBuyPrice(),
            'total_products_in_purchases' => $this->totalProductsInPurchases(),
        ];
    }

    /**
     * Calculate total sales from Orders, DistributionSales, and AgentSales for a date range.
     */
    private function calculateSalesForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // Orders: use created_at
        $ordersSales = Order::whereBetween('created_at', [$start, $end])
            ->sum('total_price');

        // DistributionSales: use date field
        $distributionSales = DistributionSale::whereBetween('date', [$start, $end])
            ->sum('total_selling_value');

        // AgentSales: use date field
        $agentSales = AgentSale::whereBetween('date', [$start, $end])
            ->sum('total_selling_value');

        return (float) ($ordersSales + $distributionSales + $agentSales);
    }

    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange(float $current, float $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Get today's sales vs yesterday.
     */
    public function getTodaySales(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todaySales = $this->calculateSalesForPeriod($today, $today);
        $yesterdaySales = $this->calculateSalesForPeriod($yesterday, $yesterday);
        $percentageChange = $this->calculatePercentageChange($todaySales, $yesterdaySales);

        return [
            'sales' => $todaySales,
            'previous_sales' => $yesterdaySales,
            'percentage_change' => $percentageChange,
            'is_increase' => $percentageChange !== null && $percentageChange >= 0,
        ];
    }

    /**
     * Get Weekly To Date sales vs previous week same period.
     */
    public function getWTDSales(): array
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy();

        // Previous week same period: from start of previous week to same day of previous week
        $previousWeekStart = $now->copy()->subWeek()->startOfWeek();
        $previousWeekEnd = $now->copy()->subWeek();

        $wtdSales = $this->calculateSalesForPeriod($startOfWeek, $endOfWeek);
        $previousWeekSales = $this->calculateSalesForPeriod($previousWeekStart, $previousWeekEnd);
        $percentageChange = $this->calculatePercentageChange($wtdSales, $previousWeekSales);

        return [
            'sales' => $wtdSales,
            'previous_sales' => $previousWeekSales,
            'percentage_change' => $percentageChange,
            'is_increase' => $percentageChange !== null && $percentageChange >= 0,
        ];
    }

    /**
     * Get Monthly To Date sales vs previous month same period.
     */
    public function getMTDSales(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy();

        // Previous month same period
        $previousMonthStart = $startOfMonth->copy()->subMonth();
        $previousMonthEnd = $endOfMonth->copy()->subMonth();

        $mtdSales = $this->calculateSalesForPeriod($startOfMonth, $endOfMonth);
        $previousMonthSales = $this->calculateSalesForPeriod($previousMonthStart, $previousMonthEnd);
        $percentageChange = $this->calculatePercentageChange($mtdSales, $previousMonthSales);

        return [
            'sales' => $mtdSales,
            'previous_sales' => $previousMonthSales,
            'percentage_change' => $percentageChange,
            'is_increase' => $percentageChange !== null && $percentageChange >= 0,
        ];
    }

    /**
     * Get Yearly To Date sales vs previous year same period.
     */
    public function getYTDSales(): array
    {
        $now = Carbon::now();
        $startOfYear = $now->copy()->startOfYear();
        $endOfYear = $now->copy();

        // Previous year same period
        $previousYearStart = $startOfYear->copy()->subYear();
        $previousYearEnd = $endOfYear->copy()->subYear();

        $ytdSales = $this->calculateSalesForPeriod($startOfYear, $endOfYear);
        $previousYearSales = $this->calculateSalesForPeriod($previousYearStart, $previousYearEnd);
        $percentageChange = $this->calculatePercentageChange($ytdSales, $previousYearSales);

        return [
            'sales' => $ytdSales,
            'previous_sales' => $previousYearSales,
            'percentage_change' => $percentageChange,
            'is_increase' => $percentageChange !== null && $percentageChange >= 0,
        ];
    }

    /**
     * Get all sales metrics.
     */
    public function getSalesMetrics(): array
    {
        return [
            'today' => $this->getTodaySales(),
            'wtd' => $this->getWTDSales(),
            'mtd' => $this->getMTDSales(),
            'ytd' => $this->getYTDSales(),
        ];
    }

    /**
     * Get top selling products (models) by quantity sold within a date range.
     */
    public function getTopSellingProducts(?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): array
    {
        $start = $startDate ? $startDate->copy()->startOfDay() : Carbon::now()->subMonths(1)->startOfDay();
        $end = $endDate ? $endDate->copy()->endOfDay() : Carbon::now()->endOfDay();

        // Get sales from Orders (via OrderItems)
        $orderSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'products.id',
                'products.name as model',
                DB::raw('CAST(SUM(order_items.quantity) AS UNSIGNED) as total_quantity')
            )
            ->groupBy('products.id', 'products.name');

        // Get sales from DistributionSales
        $distributionSales = DB::table('distribution_sales')
            ->join('products', 'distribution_sales.product_id', '=', 'products.id')
            ->whereBetween('distribution_sales.date', [$start, $end])
            ->select(
                'products.id',
                'products.name as model',
                DB::raw('CAST(SUM(distribution_sales.quantity_sold) AS UNSIGNED) as total_quantity')
            )
            ->groupBy('products.id', 'products.name');

        // Get sales from AgentSales
        $agentSales = DB::table('agent_sales')
            ->join('products', 'agent_sales.product_id', '=', 'products.id')
            ->whereBetween('agent_sales.date', [$start, $end])
            ->select(
                'products.id',
                'products.name as model',
                DB::raw('CAST(SUM(agent_sales.quantity_sold) AS UNSIGNED) as total_quantity')
            )
            ->groupBy('products.id', 'products.name');

        // Combine all sales by product
        $allSales = collect();
        
        // Add order sales
        foreach ($orderSales->get() as $sale) {
            $allSales->push($sale);
        }
        
        // Add distribution sales
        foreach ($distributionSales->get() as $sale) {
            $allSales->push($sale);
        }
        
        // Add agent sales
        foreach ($agentSales->get() as $sale) {
            $allSales->push($sale);
        }

        // Group by product ID and sum quantities
        $combined = $allSales
            ->groupBy('id')
            ->map(function ($group) {
                return [
                    'id' => $group->first()->id,
                    'model' => $group->first()->model,
                    'total_quantity' => (int) $group->sum('total_quantity'),
                ];
            })
            ->sortByDesc('total_quantity')
            ->take($limit)
            ->values()
            ->all();

        return $combined;
    }

    /**
     * Get total buy price of all purchases (regardless of status).
     */
    public function totalPurchaseBuyPrice(): float
    {
        return (float) Purchase::sum(DB::raw('quantity * unit_price'));
    }

    /**
     * Get total products count in all purchases.
     */
    public function totalProductsInPurchases(): int
    {
        return (int) Purchase::sum('quantity');
    }
}
