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
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $salesData[$date] = (float) Order::whereDate('created_at', $date)->sum('total_price');
        }

        $branchesBusiness = Branch::orderBy('name')
            ->get()
            ->map(function ($b) {
                $purchases = Purchase::where('branch_id', $b->id)->get();
                $purchaseTotal = (float) $purchases->sum(function ($p) {
                    return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
                });
                $salesCount = ProductListItem::query()
                    ->whereNotNull('sold_at')
                    ->where(function ($outer) use ($b) {
                        $outer->where('branch_id', $b->id)
                            ->orWhere(function ($inner) use ($b) {
                                $inner->whereNull('branch_id')
                                    ->whereHas('purchase', fn ($p) => $p->where('branch_id', $b->id));
                            });
                    })
                    ->count();
                $closingStock = ProductListItem::query()
                    ->whereNull('sold_at')
                    ->where(function ($outer) use ($b) {
                        $outer->where('branch_id', $b->id)
                            ->orWhere(function ($inner) use ($b) {
                                $inner->whereNull('branch_id')
                                    ->whereHas('purchase', fn ($p) => $p->where('branch_id', $b->id));
                            });
                    })
                    ->count();
                $openingStock = max(0, $closingStock - $purchases->count() + $salesCount);

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
        $unassignedSales = ProductListItem::query()
            ->whereNotNull('sold_at')
            ->whereNull('branch_id')
            ->where(function ($outer) {
                $outer->whereNull('purchase_id')
                    ->orWhereHas('purchase', fn ($p) => $p->whereNull('branch_id'));
            })
            ->count();
        $unassignedClosingStock = ProductListItem::query()
            ->whereNull('sold_at')
            ->whereNull('branch_id')
            ->where(function ($outer) {
                $outer->whereNull('purchase_id')
                    ->orWhereHas('purchase', fn ($p) => $p->whereNull('branch_id'));
            })
            ->count();
        $unassignedOpeningStock = max(0, $unassignedClosingStock - $unassignedPurchases + $unassignedSales);

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
                    $outer->where('branch_id', $bid)
                        ->orWhere(function ($inner) use ($bid) {
                            $inner->whereNull('branch_id')
                                ->whereHas('purchase', fn ($p) => $p->where('branch_id', $bid));
                        });
                })
                ->count();
            $closingStock = ProductListItem::query()
                ->whereNull('sold_at')
                ->where(function ($outer) use ($bid) {
                    $outer->where('branch_id', $bid)
                        ->orWhere(function ($inner) use ($bid) {
                            $inner->whereNull('branch_id')
                                ->whereHas('purchase', fn ($p) => $p->where('branch_id', $bid));
                        });
                })
                ->count();
            $openingStock = max(0, $closingStock - $purchaseCount + $salesCount);
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
}
