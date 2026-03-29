<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCredit;
use App\Models\AgentCreditPayment;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AgentCreditController extends Controller
{
    public function index(Request $request)
    {
        $base = AgentCredit::query();

        if ($request->filled('date_from')) {
            $base->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $base->whereDate('date', '<=', $request->date_to);
        }

        $statsQuery = clone $base;
        $sumTotal = (float) (clone $statsQuery)->sum('total_amount');
        $sumPaid = (float) (clone $statsQuery)->sum('paid_amount');
        $agentCreditsDashboard = [
            'count' => (clone $statsQuery)->count(),
            'total_credit' => $sumTotal,
            'total_paid' => $sumPaid,
            'total_pending' => max(0, $sumTotal - $sumPaid),
        ];

        $credits = $base->with(['agent', 'product.category', 'productListItem', 'paymentOption'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $paymentOptions = Schema::hasTable('payment_options')
            ? PaymentOption::visible()->orderBy('name')->get()
            : collect();

        return view('admin.stock.agent-credits', compact('credits', 'paymentOptions', 'agentCreditsDashboard'));
    }

    public function edit(int $id)
    {
        $credit = AgentCredit::with(['agent', 'product.category', 'productListItem', 'payments.paymentOption'])
            ->findOrFail($id);
        $paymentOptions = Schema::hasTable('payment_options')
            ? PaymentOption::visible()->orderBy('name')->get()
            : collect();

        return view('admin.stock.edit-agent-credit', compact('credit', 'paymentOptions'));
    }

    /**
     * Update only the payment channel from the agent credits list (balances adjusted like full update).
     */
    public function updatePaymentChannel(Request $request, int $id)
    {
        $credit = AgentCredit::findOrFail($id);

        $rules = [
            'payment_option_id' => Schema::hasTable('payment_options')
                ? 'nullable|exists:payment_options,id'
                : 'nullable',
        ];
        $validated = $request->validate($rules);

        $totalAmount = (float) $credit->total_amount;
        $oldPaidAmount = (float) ($credit->paid_amount ?? 0);
        $eps = 0.0001;

        $newPaidAmount = min($totalAmount, $oldPaidAmount);
        $paymentDifference = 0.0;

        $paymentStatus = $newPaidAmount >= $totalAmount - $eps ? 'paid' : ($newPaidAmount > $eps ? 'partial' : 'pending');

        $oldPaymentOption = $credit->payment_option_id;
        $newPaymentOptionId = $validated['payment_option_id'] ?? null;
        if ($newPaymentOptionId === '' || $newPaymentOptionId === false) {
            $newPaymentOptionId = null;
        } else {
            $newPaymentOptionId = (int) $newPaymentOptionId;
        }

        $oldOptId = $oldPaymentOption !== null ? (int) $oldPaymentOption : null;
        $newOptId = $newPaymentOptionId;

        if ($newOptId === null && $oldOptId !== null && $oldPaidAmount > $eps) {
            $oldOption = PaymentOption::find($oldOptId);
            if ($oldOption) {
                $oldOption->increment('balance', $oldPaidAmount);
            }
        } elseif ($oldOptId !== null && $newOptId !== null && $oldOptId !== $newOptId) {
            if ($oldPaidAmount > $eps) {
                $oldOption = PaymentOption::find($oldOptId);
                if ($oldOption) {
                    $oldOption->increment('balance', $oldPaidAmount);
                }
            }
            if ($newPaidAmount > $eps) {
                $paymentOption = PaymentOption::find($newOptId);
                if ($paymentOption) {
                    if ($paymentOption->balance + $eps >= $newPaidAmount) {
                        $paymentOption->decrement('balance', $newPaidAmount);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                    }
                }
            }
        } elseif ($newOptId !== null) {
            $paymentOption = PaymentOption::find($newOptId);
            if ($paymentOption) {
                $deltaToApply = $paymentDifference;
                if ($oldOptId === null && $paymentDifference <= $eps && $oldPaidAmount > $eps) {
                    $deltaToApply = $oldPaidAmount;
                }
                if ($deltaToApply > $eps) {
                    if ($paymentOption->balance + $eps >= $deltaToApply) {
                        $paymentOption->decrement('balance', $deltaToApply);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel for this credit.']);
                    }
                } elseif ($deltaToApply < -$eps) {
                    $paymentOption->increment('balance', abs($deltaToApply));
                }
            }
        }

        $updateData = [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
            'paid_date' => $credit->paid_date,
        ];

        try {
            $columns = Schema::getColumnListing('agent_credits');
            if (in_array('payment_option_id', $columns)) {
                $updateData['payment_option_id'] = $newOptId;
            }
        } catch (\Exception $e) {
            Log::warning('agent_credits payment_option_id: ' . $e->getMessage());
        }

        $credit->update($updateData);

        return redirect()
            ->back()
            ->with('success', 'Payment channel updated.');
    }

    /**
     * From agent credits list: pay remaining balance in one step (channel + Pay).
     * Credits the selected payment channel and marks the credit paid — same idea as agent sale channel + amount.
     */
    public function payRemaining(Request $request, int $id)
    {
        $credit = AgentCredit::findOrFail($id);

        if (! Schema::hasTable('payment_options')) {
            return redirect()
                ->route('admin.stock.agent-credits')
                ->withErrors(['error' => 'Payment channels are not configured.']);
        }

        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $totalAmount = (float) $credit->total_amount;
        $oldPaid = (float) ($credit->paid_amount ?? 0);
        $eps = 0.0001;
        $remaining = max(0, $totalAmount - $oldPaid);

        if ($remaining <= $eps) {
            return redirect()
                ->route('admin.stock.agent-credits')
                ->with('info', 'This credit is already fully paid.');
        }

        $paymentOptionId = (int) $validated['payment_option_id'];
        $opt = PaymentOption::visible()->whereKey($paymentOptionId)->first();
        if (! $opt) {
            return redirect()
                ->back()
                ->withErrors(['payment_option_id' => 'Invalid or hidden payment channel.']);
        }

        $paidDate = now()->toDateString();

        DB::transaction(function () use ($credit, $opt, $remaining, $paymentOptionId, $totalAmount, $paidDate) {
            $opt->increment('balance', $remaining);

            $update = [
                'paid_amount' => $totalAmount,
                'payment_status' => 'paid',
                'paid_date' => $paidDate,
            ];
            if (Schema::hasColumn('agent_credits', 'payment_option_id')) {
                $update['payment_option_id'] = $paymentOptionId;
            }
            $credit->update($update);

            AgentCreditPayment::create([
                'agent_credit_id' => $credit->id,
                'payment_option_id' => $paymentOptionId,
                'amount' => $remaining,
                'paid_date' => $paidDate,
            ]);
        });

        return redirect()
            ->route('admin.stock.agent-credits')
            ->with('success', 'Payment recorded. Amount added to channel; status set to paid.');
    }

    public function update(Request $request, int $id)
    {
        $credit = AgentCredit::findOrFail($id);

        $rules = [
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'installment_count' => 'nullable|integer|min:0',
            'installment_amount' => 'nullable|numeric|min:0',
            'first_due_date' => 'nullable|date',
            'installment_notes' => 'nullable|string|max:2000',
        ];
        if (Schema::hasColumn('agent_credits', 'installment_interval_days')) {
            $rules['installment_interval_days'] = 'nullable|integer|min:1|max:3650';
        }
        $rules['payment_option_id'] = Schema::hasTable('payment_options')
            ? 'nullable|exists:payment_options,id'
            : 'nullable';

        $validated = $request->validate($rules);

        $totalAmount = (float) $credit->total_amount;
        $oldPaidAmount = (float) ($credit->paid_amount ?? 0);
        $increment = max(0, (float) ($validated['paid_amount'] ?? 0));
        $remaining = max(0, $totalAmount - $oldPaidAmount);
        $eps = 0.0001;

        if ($increment > $remaining + $eps) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['paid_amount' => 'Pay amount cannot exceed the remaining balance.']);
        }

        $newPaidAmount = min($totalAmount, $oldPaidAmount + $increment);
        $paymentDifference = $newPaidAmount - $oldPaidAmount;

        $paymentStatus = $newPaidAmount >= $totalAmount - $eps ? 'paid' : ($newPaidAmount > $eps ? 'partial' : 'pending');

        $oldPaymentOption = $credit->payment_option_id;
        $newPaymentOptionId = $validated['payment_option_id'] ?? null;
        if ($newPaymentOptionId === '' || $newPaymentOptionId === false) {
            $newPaymentOptionId = null;
        } else {
            $newPaymentOptionId = (int) $newPaymentOptionId;
        }
        $newPaidDate = $validated['paid_date'] ?? null;

        $oldOptId = $oldPaymentOption !== null ? (int) $oldPaymentOption : null;
        $newOptId = $newPaymentOptionId;

        if ($newOptId === null && $oldOptId !== null && $oldPaidAmount > $eps) {
            $oldOption = PaymentOption::find($oldOptId);
            if ($oldOption) {
                $oldOption->increment('balance', $oldPaidAmount);
            }
        } elseif ($oldOptId !== null && $newOptId !== null && $oldOptId !== $newOptId) {
            if ($oldPaidAmount > $eps) {
                $oldOption = PaymentOption::find($oldOptId);
                if ($oldOption) {
                    $oldOption->increment('balance', $oldPaidAmount);
                }
            }
            if ($newPaidAmount > $eps) {
                $paymentOption = PaymentOption::find($newOptId);
                if ($paymentOption) {
                    if ($paymentOption->balance + $eps >= $newPaidAmount) {
                        $paymentOption->decrement('balance', $newPaidAmount);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                    }
                }
            }
        } elseif ($newOptId !== null) {
            $paymentOption = PaymentOption::find($newOptId);
            if ($paymentOption) {
                $deltaToApply = $paymentDifference;
                if ($oldOptId === null && $paymentDifference <= $eps && $oldPaidAmount > $eps) {
                    $deltaToApply = $oldPaidAmount;
                }
                if ($deltaToApply > $eps) {
                    if ($paymentOption->balance + $eps >= $deltaToApply) {
                        $paymentOption->decrement('balance', $deltaToApply);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['paid_amount' => 'Insufficient balance in selected payment channel for this payment.']);
                    }
                } elseif ($deltaToApply < -$eps) {
                    $paymentOption->increment('balance', abs($deltaToApply));
                }
            }
        }

        $updateData = [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
            'paid_date' => $newPaidDate ?? $credit->paid_date,
            'installment_count' => $validated['installment_count'] ?? $credit->installment_count,
            'installment_amount' => $validated['installment_amount'] ?? $credit->installment_amount,
            'first_due_date' => $validated['first_due_date'] ?? $credit->first_due_date,
            'installment_notes' => $validated['installment_notes'] ?? $credit->installment_notes,
        ];
        if (Schema::hasColumn('agent_credits', 'installment_interval_days')) {
            $updateData['installment_interval_days'] = array_key_exists('installment_interval_days', $validated)
                ? $validated['installment_interval_days']
                : $credit->installment_interval_days;
        }

        try {
            $columns = Schema::getColumnListing('agent_credits');
            if (in_array('payment_option_id', $columns)) {
                $updateData['payment_option_id'] = $newPaymentOptionId;
            }
        } catch (\Exception $e) {
            Log::warning('agent_credits payment_option_id: ' . $e->getMessage());
        }

        $credit->update($updateData);

        if ($paymentDifference > $eps) {
            try {
                AgentCreditPayment::create([
                    'agent_credit_id' => $credit->id,
                    'payment_option_id' => $newOptId,
                    'amount' => $paymentDifference,
                    'paid_date' => $newPaidDate ?? now()->toDateString(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create agent credit payment record: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.stock.edit-agent-credit', $credit->id)
            ->with('success', 'Agent credit updated successfully.');
    }
}
