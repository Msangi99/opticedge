<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentCredit;
use App\Models\AgentCreditPayment;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgentCreditApiController extends Controller
{
    /**
     * Credits sold by the authenticated agent (newest first).
     */
    public function index(Request $request)
    {
        $agentId = Auth::id();

        $query = AgentCredit::query()
            ->where('agent_id', $agentId)
            ->with(['product.category', 'productListItem'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        $credits = $query->get()->map(function (AgentCredit $credit) {
            $total = (float) $credit->total_amount;
            $paid = (float) ($credit->paid_amount ?? 0);
            $remaining = max(0, $total - $paid);
            $product = $credit->product;
            $label = $product
                ? (($product->category?->name ?? '—').' – '.$product->name)
                : '—';

            return [
                'id' => $credit->id,
                'customer_name' => $credit->customer_name,
                'customer_phone' => Schema::hasColumn('agent_credits', 'customer_phone')
                    ? $credit->customer_phone
                    : null,
                'description' => $credit->installment_notes,
                'date' => $credit->date instanceof \Carbon\Carbon
                    ? $credit->date->format('Y-m-d')
                    : (string) $credit->date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'remaining' => $remaining,
                'payment_status' => $credit->payment_status,
                'product_label' => $label,
                'imei_number' => $credit->productListItem?->imei_number,
                'installment_count' => $credit->installment_count,
                'installment_amount' => $credit->installment_amount !== null
                    ? (float) $credit->installment_amount
                    : null,
                'first_due_date' => $credit->first_due_date instanceof \Carbon\Carbon
                    ? $credit->first_due_date->format('Y-m-d')
                    : ($credit->first_due_date ? (string) $credit->first_due_date : null),
            ];
        });

        return response()->json([
            'data' => $credits,
        ]);
    }

    /**
     * Record an installment / repayment on a credit owned by this agent.
     */
    public function payInstallment(Request $request, int $id)
    {
        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'paid_date' => 'nullable|date',
        ];
        if (Schema::hasTable('payment_options')) {
            $rules['payment_option_id'] = 'required|exists:payment_options,id';
        } else {
            $rules['payment_option_id'] = 'nullable';
        }

        $validated = $request->validate($rules);

        $credit = AgentCredit::where('agent_id', Auth::id())->findOrFail($id);

        $totalAmount = (float) $credit->total_amount;
        $oldPaid = (float) ($credit->paid_amount ?? 0);
        $remaining = max(0, $totalAmount - $oldPaid);
        $eps = 0.0001;
        $increment = (float) $validated['amount'];

        if ($increment > $remaining + $eps) {
            return response()->json([
                'message' => 'Amount cannot exceed the remaining balance ('.number_format($remaining, 2).').',
            ], 422);
        }

        $paymentOptionId = isset($validated['payment_option_id'])
            ? (int) $validated['payment_option_id']
            : null;

        if ($paymentOptionId === null) {
            return response()->json([
                'message' => 'Select a payment channel.',
            ], 422);
        }

        $opt = PaymentOption::visible()->whereKey($paymentOptionId)->first();
        if (! $opt) {
            return response()->json([
                'message' => 'Invalid or hidden payment channel.',
            ], 422);
        }

        $newPaid = min($totalAmount, $oldPaid + $increment);
        $paymentStatus = $newPaid >= $totalAmount - $eps ? 'paid' : ($newPaid > $eps ? 'partial' : 'pending');
        $paidDate = $validated['paid_date'] ?? now()->toDateString();

        DB::transaction(function () use ($credit, $opt, $increment, $newPaid, $paymentStatus, $paidDate, $paymentOptionId, $eps) {
            // Same as admin agent sale channel: repayment is credited to the selected channel.
            if ($increment > $eps) {
                $opt->increment('balance', $increment);
            }

            $update = [
                'paid_amount' => $newPaid,
                'payment_status' => $paymentStatus,
                'paid_date' => $paidDate,
            ];
            if (Schema::hasColumn('agent_credits', 'payment_option_id')) {
                $update['payment_option_id'] = $paymentOptionId;
            }
            $credit->update($update);

            AgentCreditPayment::create([
                'agent_credit_id' => $credit->id,
                'payment_option_id' => $paymentOptionId,
                'amount' => $increment,
                'paid_date' => $paidDate,
            ]);
        });

        $credit->refresh();

        return response()->json([
            'message' => 'Payment recorded.',
            'data' => [
                'agent_credit_id' => $credit->id,
                'paid_amount' => (float) $credit->paid_amount,
                'remaining' => max(0, (float) $credit->total_amount - (float) $credit->paid_amount),
                'payment_status' => $credit->payment_status,
            ],
        ], 200);
    }
}
