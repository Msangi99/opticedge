<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Basic Stats
        $totalSales = Order::sum('total_price');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Sales chart data (Last 7 days)
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $salesData[$date] = Order::whereDate('created_at', $date)->sum('total_price');
        }

        $branchesBusiness = collect();
        $unassignedPurchases = 0;
        $unassignedPurchaseTotal = 0.0;

        if (Schema::hasTable('branches') && Schema::hasColumn('purchases', 'branch_id')) {
            $branchesBusiness = Branch::orderBy('name')
                ->get()
                ->map(function (Branch $b) {
                    $rows = Purchase::where('branch_id', $b->id)->get();
                    $total = (float) $rows->sum(function ($p) {
                        return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
                    });

                    return (object) [
                        'id' => $b->id,
                        'name' => $b->name,
                        'purchase_count' => $rows->count(),
                        'purchase_total' => $total,
                    ];
                });

            $noBranch = Purchase::whereNull('branch_id')->get();
            $unassignedPurchases = $noBranch->count();
            $unassignedPurchaseTotal = (float) $noBranch->sum(function ($p) {
                return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
            });
        }

        $selectedBranchId = $request->query('branch_id');
        $selectedBranchDetail = null;
        if ($selectedBranchId !== null && $selectedBranchId !== '' && Schema::hasColumn('purchases', 'branch_id')) {
            $bid = (int) $selectedBranchId;
            $branch = Branch::find($bid);
            if ($branch) {
                $rows = Purchase::where('branch_id', $bid)->get();
                $selectedBranchDetail = (object) [
                    'branch' => $branch,
                    'purchase_count' => $rows->count(),
                    'purchase_total' => (float) $rows->sum(function ($p) {
                        return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
                    }),
                ];
            }
        }

        return view('admin.reports.index', compact(
            'totalSales',
            'totalOrders',
            'totalCustomers',
            'salesData',
            'branchesBusiness',
            'unassignedPurchases',
            'unassignedPurchaseTotal',
            'selectedBranchId',
            'selectedBranchDetail',
        ));
    }
}
