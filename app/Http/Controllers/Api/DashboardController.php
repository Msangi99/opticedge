<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardFinancialService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardFinancialService $financialService
    ) {}

    /**
     * Get dashboard metrics for admin.
     */
    public function index(Request $request)
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user->name ?? 'Guest',
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });

        $financialMetrics = $this->financialService->getMetrics();

        return response()->json([
            'data' => [
                'total_customers' => $totalCustomers,
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'recent_orders' => $recentOrders,
                'financial_metrics' => $financialMetrics,
            ],
        ]);
    }
}
