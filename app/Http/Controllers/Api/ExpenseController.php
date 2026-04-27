<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PaymentOption;
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

    public function show(int $id)
    {
        $expense = Expense::with('paymentOption:id,name,type')->findOrFail($id);
        return response()->json([
            'data' => [
                'id' => $expense->id,
                'activity' => $expense->activity,
                'amount' => (float) $expense->amount,
                'payment_option_id' => $expense->payment_option_id,
                'payment_option_name' => $expense->paymentOption?->name,
                'date' => $expense->date?->format('Y-m-d'),
                'created_at' => $expense->created_at?->toISOString(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_option_id' => 'required|exists:payment_options,id',
            'date' => 'required|date',
        ]);
        $validated['cash_used'] = null;
        $expense = Expense::create($validated);
        if ($expense->paymentOption) {
            $expense->paymentOption->decrement('balance', (float) $validated['amount']);
        }
        return response()->json(['message' => 'Expense added successfully.'], 201);
    }

    public function update(Request $request, int $id)
    {
        $expense = Expense::findOrFail($id);
        $validated = $request->validate([
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_option_id' => 'required|exists:payment_options,id',
            'date' => 'required|date',
        ]);
        $validated['cash_used'] = null;

        $oldAmount = (float) $expense->amount;
        $oldOptionId = $expense->payment_option_id;
        $expense->update($validated);

        if ($oldOptionId && $oldOptionId != $validated['payment_option_id']) {
            $oldOption = PaymentOption::find($oldOptionId);
            if ($oldOption) $oldOption->increment('balance', $oldAmount);
        } elseif ($oldOptionId == $validated['payment_option_id']) {
            $difference = (float) $validated['amount'] - $oldAmount;
            if ($difference != 0 && $expense->paymentOption) {
                if ($difference > 0) {
                    $expense->paymentOption->decrement('balance', $difference);
                } else {
                    $expense->paymentOption->increment('balance', abs($difference));
                }
            }
        }
        if ($oldOptionId != $validated['payment_option_id'] && $expense->paymentOption) {
            $expense->paymentOption->decrement('balance', (float) $validated['amount']);
        }
        return response()->json(['message' => 'Expense updated successfully.']);
    }

    public function destroy(int $id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->paymentOption) {
            $expense->paymentOption->increment('balance', (float) $expense->amount);
        }
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully.']);
    }
}
