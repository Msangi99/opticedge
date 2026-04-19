<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\AgentProductListAssignment;
use App\Models\AgentSale;
use App\Models\PendingSale;
use App\Models\ProductListItem;
use App\Support\PdfDownload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AgentDashboardController extends Controller
{
    /**
     * Get agent dashboard data: assignments, stats, and recent sales.
     */
    public function index()
    {
        $agentId = Auth::id();
        $agentName = Auth::user()?->name ?? '';

        // Get assignments
        $assignments = AgentAssignment::where('agent_id', $agentId)
            ->with('product.category')
            ->get()
            ->map(function ($a) {
                $remaining = $a->quantity_assigned - $a->quantity_sold;
                return [
                    'id' => $a->id,
                    'product_id' => $a->product_id,
                    'product_name' => $a->product?->name ?? '–',
                    'category_name' => $a->product?->category?->name ?? '–',
                    'quantity_assigned' => (int) $a->quantity_assigned,
                    'quantity_sold' => (int) $a->quantity_sold,
                    'quantity_remaining' => $remaining,
                ];
            })
            ->values()
            ->all();

        // Calculate stats
        $totalAssigned = AgentAssignment::where('agent_id', $agentId)->sum('quantity_assigned');
        $totalSold = AgentAssignment::where('agent_id', $agentId)->sum('quantity_sold');
        $totalRemaining = $totalAssigned - $totalSold;

        // Recent finalized agent sales + in-app cash sales still on pending_sales (seller_id)
        $recentAgentSales = AgentSale::where('agent_id', $agentId)
            ->with(['product.category'])
            ->latest('date')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'record_type' => 'agent_sale',
                    'id' => $sale->id,
                    'customer_name' => $sale->customer_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'selling_price' => (float) ($sale->selling_price ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'date' => $sale->date ? (is_string($sale->date) ? Carbon::parse($sale->date)->toISOString() : $sale->date->toISOString()) : null,
                ];
            });

        $recentPendingQuery = PendingSale::query()->with(['product.category']);
        if (Schema::hasColumn('pending_sales', 'seller_id')) {
            $recentPendingQuery->where('seller_id', $agentId);
        } else {
            $recentPendingQuery->where('seller_name', $agentName);
        }
        $recentPending = $recentPendingQuery
            ->latest('date')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'record_type' => 'pending_sale',
                    'id' => $sale->id,
                    'customer_name' => $sale->customer_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'selling_price' => (float) ($sale->selling_price ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'date' => $sale->date ? (is_string($sale->date) ? Carbon::parse($sale->date)->toISOString() : $sale->date->toISOString()) : null,
                ];
            });

        $recentSales = $recentAgentSales
            ->concat($recentPending)
            ->sortByDesc(function ($row) {
                return $row['date'] ?? '';
            })
            ->take(10)
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'assignments' => $assignments,
                'stats' => [
                    'total_assigned' => (int) $totalAssigned,
                    'total_sold' => (int) $totalSold,
                    'total_remaining' => (int) $totalRemaining,
                ],
                'recent_sales' => $recentSales,
            ],
        ]);
    }

    /**
     * IMEI-level breakdown for dashboard stat cards (assigned = remaining ∪ sold for this agent).
     */
    public function inventory()
    {
        $agentId = Auth::id();
        $user = Auth::user();

        $remainingItems = AgentProductListAssignment::query()
            ->where('agent_id', $agentId)
            ->whereHas('productListItem', fn ($q) => $q->whereNull('sold_at'))
            ->with(['productListItem.product.category', 'productListItem.category'])
            ->get()
            ->map(fn (AgentProductListAssignment $row) => $this->mapInventoryItem($row->productListItem, [
                'state' => 'remaining',
            ]))
            ->filter()
            ->values()
            ->all();

        $soldQuery = ProductListItem::query()
            ->whereNotNull('sold_at')
            ->with(['product.category', 'category', 'pendingSale', 'agentCredit', 'agentSale']);

        $soldQuery->where(function ($q) use ($agentId, $user) {
            $q->where(function ($q2) use ($agentId) {
                $q2->whereNotNull('agent_credit_id')
                    ->whereHas('agentCredit', fn ($c) => $c->where('agent_id', $agentId));
            })->orWhere(function ($q2) use ($agentId) {
                $q2->whereNotNull('agent_sale_id')
                    ->whereHas('agentSale', fn ($s) => $s->where('agent_id', $agentId));
            })->orWhere(function ($q2) use ($agentId, $user) {
                $q2->whereNotNull('pending_sale_id')
                    ->whereHas('pendingSale', function ($p) use ($agentId, $user) {
                        if (Schema::hasColumn('pending_sales', 'seller_id')) {
                            $p->where('seller_id', $agentId)
                                ->orWhere(function ($p2) use ($user) {
                                    $p2->whereNull('seller_id')
                                        ->where('seller_name', $user->name);
                                });
                        } else {
                            $p->where('seller_name', $user->name);
                        }
                    });
            });
        });

        $soldItems = $soldQuery->orderByDesc('sold_at')
            ->get()
            ->unique('id')
            ->map(function (ProductListItem $item) {
                $agentCredit = $item->agentCredit;
                $agentSale = $item->agentSale;
                $creditPaid = $agentCredit && ($agentCredit->payment_status ?? '') === 'paid';
                $salePaid = $agentSale && max(0, (float) ($agentSale->balance ?? 0)) <= 0.0001;
                $hasPendingSale = (bool) $item->pending_sale_id;
                $invoiceType = $agentCredit
                    ? 'credit'
                    : (($agentSale || $hasPendingSale) ? 'sale' : null);

                return $this->mapInventoryItem($item, [
                    'state' => 'sold',
                    'sold_at' => $item->sold_at ? $item->sold_at->toIso8601String() : null,
                    'customer_name' => $item->pendingSale?->customer_name
                        ?? $agentCredit?->customer_name
                        ?? $agentSale?->customer_name,
                    'agent_credit_id' => $agentCredit?->id,
                    'agent_sale_id' => $agentSale?->id,
                    'pending_sale_id' => $hasPendingSale ? $item->pending_sale_id : null,
                    'invoice_type' => $invoiceType,
                    'invoice_available' => $creditPaid || $salePaid,
                    'invoice_endpoint' => $agentCredit
                        ? '/agent/credits/' . $agentCredit->id . '/invoice'
                        : ($agentSale ? '/agent/sales/' . $agentSale->id . '/invoice' : null),
                ]);
            })
            ->values()
            ->all();

        $assignedAll = collect($remainingItems)
            ->merge($soldItems)
            ->sortBy(fn ($row) => ($row['category_name'] ?? '') . ($row['product_name'] ?? '') . ($row['imei_number'] ?? ''))
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'remaining' => $remainingItems,
                'sold' => $soldItems,
                'assigned' => $assignedAll,
            ],
        ]);
    }

    public function downloadSaleInvoice(int $id)
    {
        $sale = AgentSale::query()
            ->where('agent_id', Auth::id())
            ->with(['product.category', 'productListItem'])
            ->findOrFail($id);

        $remaining = max(0, (float) ($sale->balance ?? 0));
        if ($remaining > 0.0001) {
            return response()->json([
                'message' => 'Invoice is available after this sale is fully paid.',
            ], 422);
        }

        $invoiceNo = 'AS-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
        $invoiceDate = $sale->date ? Carbon::parse($sale->date) : now();
        $filename = 'agent-sale-invoice-' . strtolower($invoiceNo) . '-' . $invoiceDate->format('Ymd') . '.pdf';
        $title = 'RECEIPT';

        return PdfDownload::fromView('admin.stock.receipt-invoice', compact('sale', 'invoiceNo', 'invoiceDate', 'title'), $filename);
    }

    private function mapInventoryItem(?ProductListItem $item, array $extra = []): ?array
    {
        if (! $item) {
            return null;
        }

        $item->loadMissing(['product.category', 'category']);
        $product = $item->product;

        $base = [
            'product_list_id' => $item->id,
            'imei_number' => $item->imei_number,
            'model' => $item->model,
            'product_name' => $product?->name ?? $item->model ?? '–',
            'category_name' => $product?->category?->name ?? $item->category?->name ?? '–',
        ];

        return array_merge($base, $extra);
    }
}
