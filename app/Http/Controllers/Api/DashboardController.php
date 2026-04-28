<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardFinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardFinancialService $financialService
    ) {}

    /**
     * Get dashboard metrics for admin (same as web: stats, sales metrics, financial, payment options, top products, recent orders).
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
                    'created_at' => $order->created_at ? (is_string($order->created_at) ? Carbon::parse($order->created_at)->toISOString() : $order->created_at->toISOString()) : null,
                ];
            });

        $financialMetrics = $this->financialService->getMetrics();
        $salesMetrics = $this->financialService->getSalesMetrics();
        $distributionReceivables = $this->financialService->distributionReceivables();
        $agentCreditReceivables = $this->financialService->getAgentCreditReceivableSummary();

        $startDate = $request->has('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();
        $topProducts = $this->financialService->getTopSellingProducts($startDate, $endDate, 10);

        $paymentOptions = PaymentOption::visible()->orderBy('name')->get()->map(function ($opt) {
            return [
                'id' => $opt->id,
                'name' => $opt->name,
                'type' => $opt->type,
                'balance' => (float) $opt->balance,
                'opening_balance' => (float) $opt->opening_balance,
            ];
        });

        return response()->json([
            'data' => [
                'total_customers' => $totalCustomers,
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'recent_orders' => $recentOrders,
                'financial_metrics' => $financialMetrics,
                'receivables_breakdown' => [
                    'distribution' => $distributionReceivables,
                    'agent_credit' => $agentCreditReceivables,
                ],
                'sales_metrics' => $salesMetrics,
                'payment_options' => $paymentOptions,
                'top_products' => $topProducts,
            ],
        ]);
    }
}
