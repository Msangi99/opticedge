<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Agents</p>
                <h1 class="admin-prod-title">Agent credit</h1>
                <p class="admin-prod-subtitle">Repayments and installment reference.</p>
            </div>
            <a href="{{ route('admin.stock.agent-credits') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Credit #{{ $credit->id }}</h2>
            </div>
            <form action="{{ route('admin.stock.update-agent-credit', $credit->id) }}" method="POST" class="admin-prod-form-body">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-2">Loan</h3>
                        </div>
                        <div>
                            <label class="admin-prod-label">Date</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->date instanceof \Carbon\Carbon ? $credit->date->format('Y-m-d') : $credit->date }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">Agent</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->agent?->name ?? '—' }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">Customer</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->customer_name }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">IMEI</label>
                            <input type="text" readonly class="admin-prod-input font-mono text-sm cursor-not-allowed" value="{{ $credit->productListItem?->imei_number ?? '—' }}">
                        </div>
                        <div class="col-span-2">
                            <label class="admin-prod-label">Product</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->product ? (($credit->product->category?->name ?? '—') . ' – ' . $credit->product->name) : 'N/A' }}">
                        </div>

                        @php
                            $creditTotal = (float) $credit->total_amount;
                            $alreadyPaid = (float) ($credit->paid_amount ?? 0);
                            $pendingNow = max(0, $creditTotal - $alreadyPaid);
                        @endphp
                        <div class="col-span-2 border-t border-slate-100 pt-4">
                            <label class="admin-prod-label">Total credit</label>
                            <input type="text" readonly class="admin-prod-input font-bold cursor-not-allowed" value="{{ number_format($creditTotal, 2) }} TZS">
                        </div>

                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Installment (reference)</h3>
                        </div>
                        <div>
                            <label for="installment_count" class="admin-prod-label">Installment count</label>
                            <input type="number" min="0" name="installment_count" id="installment_count" value="{{ old('installment_count', $credit->installment_count) }}" class="admin-prod-input">
                        </div>
                        <div>
                            <label for="installment_amount" class="admin-prod-label">Installment amount (per interval)</label>
                            <input type="number" step="0.01" min="0" name="installment_amount" id="installment_amount" value="{{ old('installment_amount', $credit->installment_amount) }}" class="admin-prod-input">
                        </div>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('agent_credits', 'installment_interval_days'))
                        <div>
                            <label for="installment_interval_days" class="admin-prod-label">Payment interval (days)</label>
                            <input type="number" min="1" max="3650" name="installment_interval_days" id="installment_interval_days" value="{{ old('installment_interval_days', $credit->installment_interval_days) }}" placeholder="e.g. 7 weekly, 30 monthly" class="admin-prod-input">
                            <p class="text-xs text-slate-500 mt-1">How often the customer should pay (for your reference and next-due estimate).</p>
                        </div>
                        @endif
                        <div>
                            <label for="first_due_date" class="admin-prod-label">First due date</label>
                            <input type="date" name="first_due_date" id="first_due_date" value="{{ old('first_due_date', $credit->first_due_date?->format('Y-m-d')) }}" class="admin-prod-input">
                        </div>
                        @php
                            $intervalDays = (int) ($credit->installment_interval_days ?? 0);
                            $paymentRows = $credit->payments ?? collect();
                            $installmentsRecorded = $paymentRows->count();
                            $nextDueEstimate = null;
                            if ($credit->first_due_date && $intervalDays > 0) {
                                try {
                                    $nextDueEstimate = $credit->first_due_date->copy()->addDays($intervalDays * $installmentsRecorded);
                                } catch (\Throwable $e) {
                                    $nextDueEstimate = null;
                                }
                            }
                        @endphp
                        @if($nextDueEstimate)
                        <div class="col-span-2 rounded-md bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-700">
                            <span class="font-medium">Next due (estimate):</span>
                            {{ $nextDueEstimate->format('Y-m-d') }}
                            <span class="text-slate-500">— based on first due + {{ $intervalDays }} day(s) × {{ $installmentsRecorded }} recorded payment row(s).</span>
                        </div>
                        @endif
                        <div class="col-span-2">
                            <label for="installment_notes" class="admin-prod-label">Notes</label>
                            <textarea name="installment_notes" id="installment_notes" rows="2" class="admin-prod-textarea">{{ old('installment_notes', $credit->installment_notes) }}</textarea>
                        </div>

                        <div id="repayment" class="col-span-2 border-t border-slate-100 pt-4 mt-2 scroll-mt-24">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Repayment</h3>
                        </div>
                        <div>
                            <label for="paid_date" class="admin-prod-label">Paid date</label>
                            <input type="date" name="paid_date" id="paid_date" value="{{ old('paid_date', $credit->paid_date?->format('Y-m-d')) }}" class="admin-prod-input">
                        </div>
                        <div>
                            <label for="paid_amount" class="admin-prod-label">Pay (this time)</label>
                            <p class="text-xs text-slate-600 mb-1">Already paid: <span class="font-medium text-slate-900">{{ number_format($alreadyPaid, 2) }}</span></p>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" min="0" max="{{ $pendingNow }}" class="admin-prod-input" oninput="updatePendingAmount()">
                            @error('paid_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            <p class="text-xs text-slate-500 mt-1">Remaining: {{ number_format($pendingNow, 2) }} TZS</p>
                            <button type="button" id="btn_fill_installment" class="mt-2 text-sm text-[#fa8900] font-medium hover:underline">Fill with installment amount</button>
                        </div>
                        <div>
                            <label for="payment_option_id" class="admin-prod-label">Payment channel</label>
                            <select name="payment_option_id" id="payment_option_id" class="admin-prod-select">
                                <option value="">Optional</option>
                                @foreach($paymentOptions as $option)
                                    <option value="{{ $option->id }}" data-balance="{{ $option->balance }}"
                                        {{ old('payment_option_id', $credit->payment_option_id) == $option->id ? 'selected' : '' }}>
                                        {{ $option->name }} ({{ number_format($option->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_option_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="admin-prod-label">Pending</label>
                            <input type="text" id="pending_amount" readonly class="admin-prod-input cursor-not-allowed" value="{{ number_format($pendingNow, 2) }}">
                        </div>

                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Payment history</h3>
                            @if($credit->payments && $credit->payments->count() > 0)
                                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Date</th>
                                                <th class="px-4 py-2 text-left">Channel</th>
                                                <th class="px-4 py-2 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($credit->payments as $pay)
                                                <tr class="border-t border-slate-100">
                                                    <td class="px-4 py-2">{{ $pay->paid_date?->format('Y-m-d') ?? ($pay->created_at?->format('Y-m-d') ?? '—') }}</td>
                                                    <td class="px-4 py-2">{{ $pay->paymentOption?->name ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-right font-medium">{{ number_format((float) $pay->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No payment rows yet.</p>
                            @endif
                        </div>

                        <div class="col-span-2 admin-prod-form-footer !mt-4">
                            <a href="{{ route('admin.stock.agent-credits') }}" class="admin-prod-btn-ghost">Cancel</a>
                            <button type="submit" class="admin-prod-btn-primary px-8">Update</button>
                        </div>
                    </div>
                </form>
        </div>
    </div>
    <script>
        (function() {
            var pendingBase = {{ $pendingNow }};
            var paidInput = document.getElementById('paid_amount');
            var pendingEl = document.getElementById('pending_amount');
            function updatePendingAmount() {
                if (!paidInput || !pendingEl) return;
                var payNow = parseFloat(paidInput.value) || 0;
                if (payNow > pendingBase) {
                    payNow = pendingBase;
                    paidInput.value = pendingBase;
                }
                var pending = Math.max(0, pendingBase - payNow);
                pendingEl.value = pending.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            if (paidInput) paidInput.addEventListener('input', updatePendingAmount);
            var fillBtn = document.getElementById('btn_fill_installment');
            var instInput = document.getElementById('installment_amount');
            if (fillBtn && paidInput && instInput) {
                fillBtn.addEventListener('click', function() {
                    var inst = parseFloat(instInput.value) || 0;
                    var pay = Math.min(inst, pendingBase);
                    if (pay > 0) paidInput.value = pay.toFixed(2);
                    updatePendingAmount();
                });
            }
        })();
    </script>
</x-admin-layout>
