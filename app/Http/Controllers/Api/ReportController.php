<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $totalSales = (float) Order::sum('total_price');
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $salesData[$date] = (float) Order::whereDate('created_at', $date)->sum('total_price');
        }

        return response()->json([
            'data' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
                'sales_by_day' => $salesData,
            ],
        ]);
    }
}
