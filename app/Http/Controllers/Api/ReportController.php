<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
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

                return [
                    'branch_id' => $b->id,
                    'name' => $b->name,
                    'purchase_total' => $purchaseTotal,
                    'purchase_count' => $purchases->count(),
                ];
            })
            ->values()
            ->all();

        $payload = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'sales_by_day' => $salesData,
            'branches_business' => $branchesBusiness,
        ];

        if ($branchId !== null && $branchId !== '') {
            $bid = (int) $branchId;
            $purchaseQuery = Purchase::where('branch_id', $bid);
            $purchaseTotal = (float) (clone $purchaseQuery)->get()->sum(function ($p) {
                return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
            });
            $purchaseCount = (clone $purchaseQuery)->count();
            $payload['branch_id'] = $bid;
            $payload['branch_purchase_total'] = $purchaseTotal;
            $payload['branch_purchase_count'] = $purchaseCount;
        }

        return response()->json([
            'data' => $payload,
        ]);
    }
}
