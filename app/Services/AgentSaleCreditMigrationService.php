<?php

namespace App\Services;

use App\Models\AgentCredit;
use App\Models\AgentCreditPayment;
use App\Models\AgentSale;
use App\Models\Expense;
use App\Models\PaymentOption;
use App\Models\ProductListItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgentSaleCreditMigrationService
{
    public function __construct(
        protected DistributionSaleService $distributionSaleService
    ) {}

    /**
     * Default Watu channel for agent credits (same rules as app sell-on-credit).
     */
    public function resolveDefaultWatuPaymentOption(): PaymentOption
    {
        $watuDefaultRaw = Setting::query()->where('key', 'default_watu_channel_id')->value('value');
        if (is_numeric($watuDefaultRaw)) {
            $candidate = PaymentOption::visible()->find((int) $watuDefaultRaw);
            if ($candidate && $candidate->isWatuAgentCreditChannel()) {
                return $candidate;
            }
        }

        $fallback = PaymentOption::visible()
            ->orderBy('name')
            ->get()
            ->first(fn (PaymentOption $opt) => $opt->isWatuAgentCreditChannel());

        if (! $fallback) {
            throw new \InvalidArgumentException('No Watu payment channel is configured. Set default Watu channel in Store settings or add a visible channel whose name contains “Watu”.');
        }

        return $fallback;
    }

    /**
     * Convert a finalized agent sale into an agent credit: undo sale channel + commission,
     * move the same amount onto the default Watu channel, create credit (unpaid), relink IMEIs.
     */
    public function convertAgentSaleToAgentCredit(AgentSale $sale): AgentCredit
    {
        $eps = 0.0001;
        $sale->loadMissing(['agent', 'product']);

        if (! $sale->agent_id) {
            throw new \InvalidArgumentException('This sale has no agent; it cannot be converted to agent credit.');
        }
        if (! $sale->product_id) {
            throw new \InvalidArgumentException('This sale has no product.');
        }
        if (! $sale->payment_option_id) {
            throw new \InvalidArgumentException('Set a payment channel on this sale before converting to credit (or the channel balance cannot be adjusted).');
        }

        $total = (float) ($sale->total_selling_value ?? 0);
        if ($total <= $eps) {
            throw new \InvalidArgumentException('Sale total must be greater than zero.');
        }

        $linkedItems = ProductListItem::query()->where('agent_sale_id', $sale->id)->get();
        if ($linkedItems->count() > 1) {
            throw new \InvalidArgumentException('This sale is linked to more than one IMEI row. Split or correct links before converting.');
        }

        $watu = $this->resolveDefaultWatuPaymentOption();

        return DB::transaction(function () use ($sale, $total, $eps, $linkedItems, $watu) {
            $sale = AgentSale::lockForUpdate()->findOrFail($sale->id);

            $this->reverseAgentSalePaymentAndCommissionOnly($sale);

            $watuFresh = PaymentOption::lockForUpdate()->find($watu->id);
            if ($watuFresh && $total > $eps) {
                $watuFresh->increment('balance', $total);
            }

            $credit = AgentCredit::create([
                'agent_id' => $sale->agent_id,
                'customer_name' => $sale->customer_name ?: 'Customer',
                'customer_phone' => null,
                'kin_name' => null,
                'kin_phone' => null,
                'product_list_id' => $linkedItems->first()?->id,
                'product_id' => (int) $sale->product_id,
                'total_amount' => $total,
                'paid_amount' => 0,
                'commission_paid' => 0,
                'payment_status' => 'pending',
                'payment_option_id' => $watu->id,
                'installment_count' => null,
                'installment_amount' => null,
                'installment_interval_days' => null,
                'first_due_date' => null,
                'installment_notes' => trim('[Converted from agent sale #'.$sale->id.']'
                    .($sale->seller_name ? ' Seller: '.$sale->seller_name : '')),
                'date' => $sale->date ? Carbon::parse($sale->date)->toDateString() : now()->toDateString(),
                'paid_date' => null,
            ]);

            foreach ($linkedItems as $item) {
                $item->update([
                    'agent_credit_id' => $credit->id,
                    'agent_sale_id' => null,
                ]);
            }

            DB::table('agent_sales')->where('id', $sale->id)->delete();

            return $credit->fresh();
        });
    }

    /**
     * Convert agent credit to an agent sale: reverse credit payment ledger + commission,
     * book full amount on the chosen channel, relink IMEIs.
     */
    public function convertAgentCreditToAgentSale(AgentCredit $credit, int $paymentOptionId): AgentSale
    {
        $eps = 0.0001;
        $credit->loadMissing(['agent', 'product']);

        if (! $credit->agent_id) {
            throw new \InvalidArgumentException('This credit has no agent.');
        }
        if (! $credit->product_id) {
            throw new \InvalidArgumentException('This credit has no product.');
        }

        $total = (float) ($credit->total_amount ?? 0);
        if ($total <= $eps) {
            throw new \InvalidArgumentException('Credit total must be greater than zero.');
        }

        $paid = (float) ($credit->paid_amount ?? 0);
        $paymentRows = AgentCreditPayment::query()->where('agent_credit_id', $credit->id)->orderBy('id')->get();
        if ($paid > $eps && $paymentRows->isEmpty()) {
            throw new \InvalidArgumentException('This credit has paid amount but no payment rows; fix the record before converting.');
        }

        $linkedItems = ProductListItem::query()->where('agent_credit_id', $credit->id)->get();
        if ($linkedItems->isEmpty() && $credit->product_list_id) {
            $row = ProductListItem::query()->find($credit->product_list_id);
            $linkedItems = $row ? collect([$row]) : collect();
        }
        if ($linkedItems->count() > 1) {
            throw new \InvalidArgumentException('More than one IMEI row points to this credit; fix links before converting.');
        }

        return DB::transaction(function () use ($credit, $paymentOptionId, $total, $linkedItems) {
            $option = PaymentOption::visible()->whereKey($paymentOptionId)->lockForUpdate()->first();
            if (! $option) {
                throw new \InvalidArgumentException('Selected payment channel is invalid or hidden.');
            }

            $credit = AgentCredit::lockForUpdate()->findOrFail($credit->id);
            $paymentRows = AgentCreditPayment::query()->where('agent_credit_id', $credit->id)->orderBy('id')->get();

            $this->reverseAgentCreditCommissionExpense($credit);
            $this->reverseAgentCreditPaymentLedger($credit, $paymentRows);

            AgentCreditPayment::query()->where('agent_credit_id', $credit->id)->delete();

            $buyPrice = $this->distributionSaleService->getBuyPriceForProduct((int) $credit->product_id);
            $qty = 1;
            $sellingPrice = $total;
            $profit = $sellingPrice - ($buyPrice * $qty);

            $attrs = [
                'agent_id' => $credit->agent_id,
                'customer_name' => $credit->customer_name,
                'seller_name' => $credit->agent?->name,
                'product_id' => (int) $credit->product_id,
                'quantity_sold' => $qty,
                'purchase_price' => $buyPrice,
                'selling_price' => $sellingPrice,
                'total_purchase_value' => $buyPrice * $qty,
                'total_selling_value' => $total,
                'profit' => $profit,
                'commission_paid' => 0,
                'balance' => 0,
                'date' => $credit->date ? Carbon::parse($credit->date)->toDateString() : now()->toDateString(),
                'payment_option_id' => $option->id,
            ];
            if (Schema::hasColumn('agent_sales', 'commission_expense_id')) {
                $attrs['commission_expense_id'] = null;
            }

            $sale = AgentSale::create($attrs);

            $option->increment('balance', $total);

            foreach ($linkedItems as $item) {
                $item->update([
                    'agent_sale_id' => $sale->id,
                    'agent_credit_id' => null,
                ]);
            }

            DB::table('agent_credits')->where('id', $credit->id)->delete();

            return $sale->fresh(['product.category', 'agent', 'paymentOption']);
        });
    }

    /**
     * Undo channel top-up and commission expense for a sale (does not touch product_list).
     */
    public function reverseAgentSalePaymentAndCommissionOnly(AgentSale $sale): void
    {
        if ($sale->payment_option_id) {
            $po = PaymentOption::find($sale->payment_option_id);
            $amount = (float) ($sale->total_selling_value ?? 0);
            if ($po && $amount > 0) {
                if ((float) $po->balance + 0.0001 < $amount) {
                    throw new \InvalidArgumentException('The sale’s payment channel balance is already lower than this sale amount.');
                }
                $po->decrement('balance', $amount);
            }
        }

        if (Schema::hasColumn('agent_sales', 'commission_expense_id') && ! empty($sale->commission_expense_id)) {
            $expense = Expense::find($sale->commission_expense_id);
            if ($expense) {
                if ($expense->payment_option_id) {
                    $expOpt = PaymentOption::find($expense->payment_option_id);
                    if ($expOpt) {
                        $expOpt->increment('balance', (float) $expense->amount);
                    }
                }
                DB::table('expenses')->where('id', $expense->id)->delete();
            }
        }
    }

    private function reverseAgentCreditCommissionExpense(AgentCredit $credit): void
    {
        if (! Schema::hasColumn('agent_credits', 'commission_expense_id') || empty($credit->commission_expense_id)) {
            return;
        }

        $expense = Expense::find($credit->commission_expense_id);
        if (! $expense) {
            return;
        }

        if ($expense->payment_option_id) {
            $expOpt = PaymentOption::find($expense->payment_option_id);
            if ($expOpt) {
                $expOpt->increment('balance', (float) $expense->amount);
            }
        }
        DB::table('expenses')->where('id', $expense->id)->delete();
    }

    /**
     * Reverse recorded credit payments against channel balances.
     * Initial down payment (sellCredit) decremented the channel; repayments incremented it.
     */
    private function reverseAgentCreditPaymentLedger(AgentCredit $credit, $paymentRows): void
    {
        $eps = 0.0001;

        foreach ($paymentRows as $idx => $p) {
            $opt = PaymentOption::lockForUpdate()->find($p->payment_option_id);
            if (! $opt) {
                continue;
            }
            $amt = (float) $p->amount;
            if ($amt <= $eps) {
                continue;
            }

            // sellCredit writes the down payment row in the same request as the credit (channel decrement).
            $createdDiffOk = $credit->created_at && $p->created_at
                && abs($credit->created_at->diffInSeconds($p->created_at)) <= 3;
            $isLikelyInitialDown = $idx === 0 && $createdDiffOk;

            if ($isLikelyInitialDown) {
                $opt->increment('balance', $amt);
            } else {
                if ((float) $opt->balance + $eps < $amt) {
                    throw new \InvalidArgumentException('Cannot reverse a repayment: channel “'.$opt->name.'” balance is too low.');
                }
                $opt->decrement('balance', $amt);
            }
        }
    }
}
