<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use Illuminate\Http\Request;

class PaymentOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentOptions = PaymentOption::latest()->get();
        return view('admin.payment-options.index', compact('paymentOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payment-options.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:mobile,bank',
            'name' => 'required|string|max:255',
        ]);

        PaymentOption::create($validated);

        return redirect()->route('admin.payment-options.index')
            ->with('success', 'Payment option created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentOption $paymentOption)
    {
        return view('admin.payment-options.edit', compact('paymentOption'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentOption $paymentOption)
    {
        $validated = $request->validate([
            'type' => 'required|in:mobile,bank',
            'name' => 'required|string|max:255',
        ]);

        $paymentOption->update($validated);

        return redirect()->route('admin.payment-options.index')
            ->with('success', 'Payment option updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentOption $paymentOption)
    {
        $paymentOption->delete();
        return redirect()->route('admin.payment-options.index')
            ->with('success', 'Payment option deleted successfully.');
    }
}
