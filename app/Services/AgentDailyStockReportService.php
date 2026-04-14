<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgentDailyStockReportService
{
    /**
     * @return array{
     *   report_date: string,
     *   branch_id: int|null,
     *   agents: \Illuminate\Support\Collection,
     *   rows: array<int, array<string, mixed>>,
     *   totals: array<string, mixed>,
     * }
     */
    public function build(Carbon $reportDate, ?int $branchId = null): array
    {
        if (! Schema::hasTable('product_list')) {
            return [
                'report_date' => $reportDate->toDateString(),
                'branch_id' => $branchId,
                'agents' => collect(),
                'rows' => [],
                'totals' => $this->emptyTotals([]),
            ];
        }

        $dayStart = $reportDate->copy()->startOfDay();
        $dayEnd = $reportDate->copy()->endOfDay();
        $prevEnd = $reportDate->copy()->subDay()->endOfDay();

        $agents = User::query()
            ->where('role', 'agent')
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $productIds = $this->distinctProductIdsInScope($branchId);
        if ($productIds->isEmpty()) {
            return [
                'report_date' => $reportDate->toDateString(),
                'branch_id' => $branchId,
                'agents' => $agents,
                'rows' => [],
                'totals' => $this->emptyTotals($agents),
            ];
        }

        $prevClosingShop = $this->closingShopByProduct($prevEnd, $branchId, $productIds);
        $prevClosingAgents = $this->closingByAgentProduct($prevEnd, $branchId, $productIds);

        $salesShop = $this->salesShopByProduct($dayStart, $dayEnd, $branchId, $productIds);
        $salesAgents = $this->salesByAgentProduct($dayStart, $dayEnd, $branchId, $productIds);

        $transferNetShop = $this->shopTransferNetByProduct($reportDate, $branchId, $productIds);
        $receivedToday = $this->receivedTodayByProduct($reportDate, $branchId, $productIds);

        $activeProductIds = [];
        foreach ($productIds as $pid) {
            $pid = (int) $pid;
            $openingShop = (int) ($prevClosingShop[$pid] ?? 0);
            $sShop = (int) ($salesShop[$pid] ?? 0);
            $tShop = (int) ($transferNetShop[$pid] ?? 0);
            $closingShop = max(0, $openingShop - $sShop + $tShop);
            $recv = (int) ($receivedToday[$pid] ?? 0);

            $rowHasActivity = $sShop > 0 || $tShop !== 0 || $recv > 0 || $openingShop > 0 || $closingShop > 0;
            foreach ($agents as $agent) {
                $aid = (int) $agent->id;
                $openingA = (int) ($prevClosingAgents[$aid][$pid] ?? 0);
                $sA = (int) ($salesAgents[$aid][$pid] ?? 0);
                $closingA = max(0, $openingA - $sA);
                if ($openingA > 0 || $sA > 0 || $closingA > 0) {
                    $rowHasActivity = true;
                    break;
                }
            }
            if ($rowHasActivity) {
                $activeProductIds[] = $pid;
            }
        }

        $products = $activeProductIds === []
            ? collect()
            : Product::query()->whereIn('id', $activeProductIds)->orderBy('name')->get(['id', 'name', 'price']);

        $rows = [];
        foreach ($products as $product) {
            $pid = (int) $product->id;
            $openingShop = (int) ($prevClosingShop[$pid] ?? 0);
            $sShop = (int) ($salesShop[$pid] ?? 0);
            $tShop = (int) ($transferNetShop[$pid] ?? 0);
            $closingShop = max(0, $openingShop - $sShop + $tShop);

            $agentCells = [];
            foreach ($agents as $agent) {
                $aid = (int) $agent->id;
                $openingA = (int) ($prevClosingAgents[$aid][$pid] ?? 0);
                $sA = (int) ($salesAgents[$aid][$pid] ?? 0);
                $closingA = max(0, $openingA - $sA);
                $agentCells[$aid] = [
                    'opening' => $openingA,
                    'sales' => $sA,
                    'closing' => $closingA,
                ];
            }

            $rows[] = [
                'product_id' => $pid,
                'name' => $product->name,
                'price' => (float) ($product->price ?? 0),
                'purchased_today' => (int) ($receivedToday[$pid] ?? 0),
                'shop' => [
                    'opening' => $openingShop,
                    'sales' => $sShop,
                    'transfer' => $tShop,
                    'closing' => $closingShop,
                ],
                'agents' => $agentCells,
            ];
        }

        return [
            'report_date' => $reportDate->toDateString(),
            'branch_id' => $branchId,
            'agents' => $agents,
            'rows' => $rows,
            'totals' => $this->sumTotals($rows, $agents),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $agents
     */
    public function rowsToCsvLines(array $payload): array
    {
        $agents = $payload['agents'];
        $rows = $payload['rows'];
        $lines = [];

        $header = ['Product', 'Price', 'Purchased today', 'Shop opening', 'Shop sales', 'Shop transfer', 'Shop closing'];
        foreach ($agents as $a) {
            $n = str_replace('"', '""', $a->name);
            $header[] = "{$n} opening";
            $header[] = "{$n} sales";
            $header[] = "{$n} closing";
        }
        $lines[] = $this->csvLine($header);

        foreach ($rows as $r) {
            $line = [
                $r['name'],
                (string) $r['price'],
                (string) $r['purchased_today'],
                (string) $r['shop']['opening'],
                (string) $r['shop']['sales'],
                (string) $r['shop']['transfer'],
                (string) $r['shop']['closing'],
            ];
            foreach ($agents as $a) {
                $c = $r['agents'][(int) $a->id] ?? ['opening' => 0, 'sales' => 0, 'closing' => 0];
                $line[] = (string) $c['opening'];
                $line[] = (string) $c['sales'];
                $line[] = (string) $c['closing'];
            }
            $lines[] = $this->csvLine($line);
        }

        $t = $payload['totals'];
        $tot = ['Total', '', (string) $t['purchased_today'], (string) $t['shop']['opening'], (string) $t['shop']['sales'], (string) $t['shop']['transfer'], (string) $t['shop']['closing']];
        foreach ($agents as $a) {
            $c = $t['agents'][(int) $a->id] ?? ['opening' => 0, 'sales' => 0, 'closing' => 0];
            $tot[] = (string) $c['opening'];
            $tot[] = (string) $c['sales'];
            $tot[] = (string) $c['closing'];
        }
        $lines[] = $this->csvLine($tot);

        return $lines;
    }

    private function csvLine(array $cells): string
    {
        $escaped = array_map(function ($v) {
            $s = (string) $v;
            if (str_contains($s, '"') || str_contains($s, ',') || str_contains($s, "\n") || str_contains($s, "\r")) {
                return '"'.str_replace('"', '""', $s).'"';
            }

            return $s;
        }, $cells);

        return implode(',', $escaped);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $agents
     */
    private function emptyTotals($agents): array
    {
        $a = [];
        foreach ($agents as $agent) {
            $a[(int) $agent->id] = ['opening' => 0, 'sales' => 0, 'closing' => 0];
        }

        return [
            'purchased_today' => 0,
            'shop' => ['opening' => 0, 'sales' => 0, 'transfer' => 0, 'closing' => 0],
            'agents' => $a,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  \Illuminate\Support\Collection<int, User>  $agents
     */
    private function sumTotals(array $rows, $agents): array
    {
        $tot = $this->emptyTotals($agents);
        foreach ($rows as $r) {
            $tot['purchased_today'] += $r['purchased_today'];
            foreach (['opening', 'sales', 'transfer', 'closing'] as $k) {
                $tot['shop'][$k] += $r['shop'][$k];
            }
            foreach ($agents as $agent) {
                $aid = (int) $agent->id;
                $c = $r['agents'][$aid] ?? ['opening' => 0, 'sales' => 0, 'closing' => 0];
                $tot['agents'][$aid]['opening'] += $c['opening'];
                $tot['agents'][$aid]['sales'] += $c['sales'];
                $tot['agents'][$aid]['closing'] += $c['closing'];
            }
        }

        return $tot;
    }

    private function baseListQuery(?int $branchId)
    {
        $q = ProductListItem::query()->whereNotNull('product_id');
        if ($branchId !== null) {
            $q->whereEffectiveBranch($branchId);
        }

        return $q;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function distinctProductIdsInScope(?int $branchId)
    {
        return $this->baseListQuery($branchId)->distinct()->pluck('product_id')->filter()->values();
    }

    /**
     * Closing inventory at end of $at: unsold OR sold after $at.
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, int> product_id => count
     */
    private function closingShopByProduct(Carbon $at, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $ids = $productIds->all();
        $q = $this->baseListQuery($branchId)
            ->whereIn('product_id', $ids)
            ->whereDoesntHave('agentProductListAssignment')
            ->where(function ($q2) use ($at) {
                $q2->whereNull('sold_at')
                    ->orWhere('sold_at', '>', $at);
            })
            ->selectRaw('product_id, COUNT(*) as c')
            ->groupBy('product_id');

        return $this->mapCounts($q->get());
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, array<int, int>> agent_id => [product_id => count]
     */
    private function closingByAgentProduct(Carbon $at, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $ids = $productIds->all();
        $rows = DB::table('product_list as pl')
            ->join('agent_product_list_assignments as a', 'a.product_list_id', '=', 'pl.id')
            ->whereIn('pl.product_id', $ids)
            ->where(function ($q2) use ($at) {
                $q2->whereNull('pl.sold_at')
                    ->orWhere('pl.sold_at', '>', $at);
            })
            ->when($branchId !== null, function ($q) use ($branchId) {
                $q->where(function ($w) use ($branchId) {
                    $w->where('pl.branch_id', $branchId)
                        ->orWhere(function ($inner) use ($branchId) {
                            $inner->whereNull('pl.branch_id')
                                ->whereExists(function ($sub) use ($branchId) {
                                    $sub->selectRaw('1')
                                        ->from('purchases as pu')
                                        ->whereColumn('pu.id', 'pl.purchase_id')
                                        ->where('pu.branch_id', $branchId);
                                });
                        });
                });
            })
            ->groupBy('a.agent_id', 'pl.product_id')
            ->selectRaw('a.agent_id as agent_id, pl.product_id as product_id, COUNT(*) as c')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $aid = (int) $r->agent_id;
            $pid = (int) $r->product_id;
            $out[$aid][$pid] = (int) $r->c;
        }

        return $out;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, int>
     */
    private function salesShopByProduct(Carbon $dayStart, Carbon $dayEnd, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $q = $this->baseListQuery($branchId)
            ->whereIn('product_id', $productIds->all())
            ->whereDoesntHave('agentProductListAssignment')
            ->whereBetween('sold_at', [$dayStart, $dayEnd])
            ->selectRaw('product_id, COUNT(*) as c')
            ->groupBy('product_id');

        return $this->mapCounts($q->get());
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, array<int, int>>
     */
    private function salesByAgentProduct(Carbon $dayStart, Carbon $dayEnd, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $rows = DB::table('product_list as pl')
            ->join('agent_product_list_assignments as a', 'a.product_list_id', '=', 'pl.id')
            ->whereIn('pl.product_id', $productIds->all())
            ->whereBetween('pl.sold_at', [$dayStart, $dayEnd])
            ->when($branchId !== null, function ($q) use ($branchId) {
                $q->where(function ($w) use ($branchId) {
                    $w->where('pl.branch_id', $branchId)
                        ->orWhere(function ($inner) use ($branchId) {
                            $inner->whereNull('pl.branch_id')
                                ->whereExists(function ($sub) use ($branchId) {
                                    $sub->selectRaw('1')
                                        ->from('purchases as pu')
                                        ->whereColumn('pu.id', 'pl.purchase_id')
                                        ->where('pu.branch_id', $branchId);
                                });
                        });
                });
            })
            ->groupBy('a.agent_id', 'pl.product_id')
            ->selectRaw('a.agent_id as agent_id, pl.product_id as product_id, COUNT(*) as c')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->agent_id][(int) $r->product_id] = (int) $r->c;
        }

        return $out;
    }

    /**
     * Net branch transfers for shop (unassigned) stock: transfers in − transfers out for the day.
     * When $branchId is set, in = to_branch_id, out = from_branch_id for that branch.
     * When null, net = all to_* minus all from_* counts per product (approximate global movement).
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, int>
     */
    private function shopTransferNetByProduct(Carbon $reportDate, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty() || ! Schema::hasTable('branch_transfer_logs')) {
            return [];
        }

        $day = $reportDate->toDateString();
        $pids = $productIds->all();

        $applyBranchOnPl = function ($q) use ($branchId) {
            if ($branchId === null) {
                return;
            }
            $q->where(function ($w) use ($branchId) {
                $w->where('pl.branch_id', $branchId)
                    ->orWhere(function ($inner) use ($branchId) {
                        $inner->whereNull('pl.branch_id')
                            ->whereExists(function ($sub) use ($branchId) {
                                $sub->selectRaw('1')
                                    ->from('purchases as pu')
                                    ->whereColumn('pu.id', 'pl.purchase_id')
                                    ->where('pu.branch_id', $branchId);
                            });
                    });
            });
        };

        $makeBase = function () use ($day, $pids, $applyBranchOnPl) {
            $q = DB::table('branch_transfer_logs as bt')
                ->join('product_list as pl', 'pl.id', '=', 'bt.product_list_id')
                ->whereDate('bt.created_at', $day)
                ->whereIn('pl.product_id', $pids)
                ->whereNotExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('agent_product_list_assignments as ap')
                        ->whereColumn('ap.product_list_id', 'pl.id');
                });
            $applyBranchOnPl($q);

            return $q;
        };

        $inRows = $makeBase()
            ->when($branchId !== null, fn ($q) => $q->where('bt.to_branch_id', $branchId))
            ->whereNotNull('bt.to_branch_id')
            ->groupBy('pl.product_id')
            ->selectRaw('pl.product_id as product_id, COUNT(*) as c')
            ->get();

        $out = [];
        foreach ($inRows as $r) {
            $out[(int) $r->product_id] = (int) $r->c;
        }

        $outRows = $makeBase()
            ->when($branchId !== null, fn ($q) => $q->where('bt.from_branch_id', $branchId))
            ->whereNotNull('bt.from_branch_id')
            ->groupBy('pl.product_id')
            ->selectRaw('pl.product_id as product_id, COUNT(*) as c')
            ->get();

        foreach ($outRows as $r) {
            $pid = (int) $r->product_id;
            $out[$pid] = ($out[$pid] ?? 0) - (int) $r->c;
        }

        return $out;
    }

    /**
     * New product_list rows created this calendar day (received / scanned in).
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $productIds
     * @return array<int, int>
     */
    private function receivedTodayByProduct(Carbon $reportDate, ?int $branchId, $productIds): array
    {
        if ($productIds->isEmpty()) {
            return [];
        }

        $q = $this->baseListQuery($branchId)
            ->whereIn('product_id', $productIds->all())
            ->whereDate('created_at', $reportDate->toDateString())
            ->selectRaw('product_id, COUNT(*) as c')
            ->groupBy('product_id');

        return $this->mapCounts($q->get());
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \stdClass>  $rows
     * @return array<int, int>
     */
    private function mapCounts($rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->product_id] = (int) $r->c;
        }

        return $out;
    }
}
