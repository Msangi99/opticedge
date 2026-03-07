<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('paymentOption')->latest('date')->latest('id')->get();
        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $paymentOptions = \App\Models\PaymentOption::visible()->orderBy('name')->get();
        return view('admin.expenses.create', compact('paymentOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_option_id' => 'required|exists:payment_options,id',
            'date' => 'required|date',
        ]);

        // Set cash_used to null since we're using payment_option_id now
        $validated['cash_used'] = null;

        $expense = Expense::create($validated);

        // Deduct amount from payment option balance
        if ($expense->paymentOption) {
            $expense->paymentOption->decrement('balance', $validated['amount']);
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        $paymentOptions = \App\Models\PaymentOption::visible()->orderBy('name')->get();
        return view('admin.expenses.edit', compact('expense', 'paymentOptions'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_option_id' => 'required|exists:payment_options,id',
            'date' => 'required|date',
        ]);

        // Set cash_used to null since we're using payment_option_id now
        $validated['cash_used'] = null;

        $oldAmount = $expense->amount;
        $oldPaymentOptionId = $expense->payment_option_id;

        $expense->update($validated);

        // Adjust payment option balances
        if ($oldPaymentOptionId && $oldPaymentOptionId != $validated['payment_option_id']) {
            // Return old amount to old payment option
            $oldPaymentOption = \App\Models\PaymentOption::find($oldPaymentOptionId);
            if ($oldPaymentOption) {
                $oldPaymentOption->increment('balance', $oldAmount);
            }
        } elseif ($oldPaymentOptionId == $validated['payment_option_id']) {
            // Same payment option, adjust difference
            $difference = $validated['amount'] - $oldAmount;
            if ($difference != 0 && $expense->paymentOption) {
                if ($difference > 0) {
                    $expense->paymentOption->decrement('balance', $difference);
                } else {
                    $expense->paymentOption->increment('balance', abs($difference));
                }
            }
        }

        // Deduct new amount from new payment option
        if ($oldPaymentOptionId != $validated['payment_option_id'] && $expense->paymentOption) {
            $expense->paymentOption->decrement('balance', $validated['amount']);
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        // Return amount to payment option balance
        if ($expense->paymentOption) {
            $expense->paymentOption->increment('balance', $expense->amount);
        }
        
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
