<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * List expenses for admin (JSON).
     */
    public function index()
    {
        $expenses = Expense::with('paymentOption:id,name,type')
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'activity' => $expense->activity,
                    'amount' => (float) $expense->amount,
                    'payment_option_id' => $expense->payment_option_id,
                    'payment_option_name' => $expense->paymentOption?->name,
                    'date' => $expense->date?->format('Y-m-d'),
                    'created_at' => $expense->created_at?->toISOString(),
                ];
            });

        return response()->json(['data' => $expenses]);
    }
}
