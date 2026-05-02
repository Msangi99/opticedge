<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductListItem;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch_id');

        $totalSales = (float) Order::sum('total_price');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $salesData[$date] = $this->dailySalesAmount($date);
        }

        $branchesBusiness = Branch::orderBy('name')
            ->get()
            ->map(function ($b) {
                $purchases = Purchase::where('branch_id', $b->id)->get();
                $purchaseTotal = (float) $purchases->sum(function ($p) {
                    return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
                });
                $salesCount = $this->branchAttributedSalesCount($b->id);
                $closingStock = ProductListItem::query()
                    ->whereEffectiveBranch($b->id)
                    ->whereNull('sold_at')
                    ->count();
                $openingStock = $closingStock + $salesCount;

                return [
                    'branch_id' => $b->id,
                    'name' => $b->name,
                    'purchase_total' => $purchaseTotal,
                    'purchase_count' => $purchases->count(),
                    'opening_stock' => $openingStock,
                    'sales_count' => $salesCount,
                    'closing_stock' => $closingStock,
                ];
            })
            ->values()
            ->all();

        $unassignedPurchases = Purchase::whereNull('branch_id')->count();
        $unassignedSales = $this->unassignedAttributedSalesCount();
        $unassignedClosingStock = ProductListItem::query()
            ->whereNull('sold_at')
            ->whereNull('branch_id')
            ->where(function ($outer) {
                $outer->whereNull('purchase_id')
                    ->orWhereHas('purchase', fn ($p) => $p->whereNull('branch_id'));
            })
            ->count();
        $unassignedOpeningStock = $unassignedClosingStock + $unassignedSales;

        $payload = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'sales_by_day' => $salesData,
            'branches_business' => $branchesBusiness,
            'unassigned_stock' => [
                'opening_stock' => $unassignedOpeningStock,
                'purchase_count' => $unassignedPurchases,
                'sales_count' => $unassignedSales,
                'closing_stock' => $unassignedClosingStock,
            ],
        ];

        if ($branchId !== null && $branchId !== '') {
            $bid = (int) $branchId;
            $purchaseQuery = Purchase::where('branch_id', $bid);
            $purchaseTotal = (float) (clone $purchaseQuery)->get()->sum(function ($p) {
                return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
            });
            $purchaseCount = (clone $purchaseQuery)->count();
            $salesCount = ProductListItem::query()
                ->whereNotNull('sold_at')
                ->where(function ($outer) use ($bid) {
                    $outer->whereHas('agentSale.agent', fn ($q) => $q->where('branch_id', $bid))
                        ->orWhereHas('pendingSale.seller', fn ($q) => $q->where('branch_id', $bid))
                        ->orWhereHas('agentCredit.agent', fn ($q) => $q->where('branch_id', $bid))
                        ->orWhere(function ($shop) use ($bid) {
                            $shop->whereDoesntHave('agentSale', fn ($q) => $q->whereNotNull('agent_id'))
                                ->whereDoesntHave('pendingSale', fn ($q) => $q->whereNotNull('seller_id'))
                                ->whereDoesntHave('agentCredit')
                                ->whereEffectiveBranch($bid);
                        });
                })
                ->count();
            $closingStock = ProductListItem::query()
                ->whereEffectiveBranch($bid)
                ->whereNull('sold_at')
                ->count();
            $openingStock = $closingStock + $salesCount;
            $payload['branch_id'] = $bid;
            $payload['branch_purchase_total'] = $purchaseTotal;
            $payload['branch_purchase_count'] = $purchaseCount;
            $payload['branch_opening_stock'] = $openingStock;
            $payload['branch_sales_count'] = $salesCount;
            $payload['branch_closing_stock'] = $closingStock;
        }

        return response()->json([
            'data' => $payload,
        ]);
    }

    public function branchDetail(int $branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $purchases = Purchase::with('product.category')
            ->where('branch_id', $branchId)
            ->latest('date')
            ->take(100)
            ->get()
            ->map(function ($p) {
                $total = (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
                return [
                    'id' => $p->id,
                    'name' => $p->name ?? 'Purchase #'.$p->id,
                    'date' => $p->date?->format('Y-m-d'),
                    'product_name' => $p->product?->name ?? '–',
                    'category_name' => $p->product?->category?->name ?? '–',
                    'quantity' => (int) ($p->quantity ?? 0),
                    'total_amount' => $total,
                    'paid_amount' => (float) ($p->paid_amount ?? 0),
                    'payment_status' => $p->payment_status ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'purchases' => $purchases,
            ],
        ]);
    }

    private function dailySalesAmount(string $date): float
    {
        $total = (float) Order::whereDate('created_at', $date)->sum('total_price');

        if (Schema::hasTable('distribution_sales')) {
            $total += (float) DB::table('distribution_sales')
                ->whereDate('date', $date)
                ->sum('total_selling_value');
        }

        if (Schema::hasTable('agent_sales')) {
            $total += (float) DB::table('agent_sales')
                ->whereDate('date', $date)
                ->sum('total_selling_value');
        }

        if (Schema::hasTable('agent_credits')) {
            $total += (float) DB::table('agent_credits')
                ->whereDate('date', $date)
                ->sum('total_amount');
        }

        return $total;
    }

    private function branchAttributedSalesCount(int $branchId): int
    {
        return ProductListItem::query()
            ->whereNotNull('sold_at')
            ->where(function ($outer) use ($branchId) {
                $outer->whereHas('agentSale.agent', fn ($q) => $q->where('branch_id', $branchId))
                    ->orWhereHas('pendingSale.seller', fn ($q) => $q->where('branch_id', $branchId))
                    ->orWhereHas('agentCredit.agent', fn ($q) => $q->where('branch_id', $branchId))
                    ->orWhere(function ($shop) use ($branchId) {
                        $shop->whereDoesntHave('agentSale', fn ($q) => $q->whereNotNull('agent_id'))
                            ->whereDoesntHave('pendingSale', fn ($q) => $q->whereNotNull('seller_id'))
                            ->whereDoesntHave('agentCredit')
                            ->whereEffectiveBranch($branchId);
                    });
            })
            ->count();
    }

    private function unassignedAttributedSalesCount(): int
    {
        return ProductListItem::query()
            ->whereNotNull('sold_at')
            ->where(function ($outer) {
                $outer->whereHas('agentSale', function ($q) {
                    $q->whereNull('agent_id')
                        ->orWhereHas('agent', fn ($u) => $u->whereNull('branch_id'));
                })->orWhereHas('pendingSale', function ($q) {
                    $q->whereNull('seller_id')
                        ->orWhereHas('seller', fn ($u) => $u->whereNull('branch_id'));
                })->orWhereHas('agentCredit', function ($q) {
                    $q->whereNull('agent_id')
                        ->orWhereHas('agent', fn ($u) => $u->whereNull('branch_id'));
                })->orWhere(function ($shop) {
                    $shop->whereDoesntHave('agentSale', fn ($q) => $q->whereNotNull('agent_id'))
                        ->whereDoesntHave('pendingSale', fn ($q) => $q->whereNotNull('seller_id'))
                        ->whereDoesntHave('agentCredit')
                        ->whereNull('branch_id')
                        ->where(function ($w) {
                            $w->whereNull('purchase_id')
                                ->orWhereHas('purchase', fn ($p) => $p->whereNull('branch_id'));
                        });
                });
            })
            ->count();
    }
}
