<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user:id,name,email')
            ->latest()
            ->take(100)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'customer_name' => $order->user?->name ?? 'Guest',
                    'email' => $order->user?->email,
                    'status' => $order->status,
                    'total_price' => (float) $order->total_price,
                    'created_at' => $order->created_at?->toISOString(),
                ];
            });

        return response()->json(['data' => $orders]);
    }
}
