<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
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

        return view('admin.reports.index', compact('totalSales', 'totalOrders', 'totalCustomers', 'salesData'));
    }
}
