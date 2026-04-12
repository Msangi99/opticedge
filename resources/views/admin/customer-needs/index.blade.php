<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-form-wide" x-data="{ tab: 'sell' }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <p class="admin-prod-eyebrow">Management</p>
                <h1 class="admin-prod-title">Customer needs</h1>
                <p class="admin-prod-subtitle">Data from the agent app (Sell screen): instant sales pending payment, Watu (credit), and catalog needs.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-6">
            <button type="button" @click="tab = 'sell'"
                :class="tab === 'sell' ? 'admin-prod-btn-primary' : 'admin-prod-btn-ghost'"
                class="rounded-xl px-4 py-2 text-sm font-semibold">Sell</button>
            <button type="button" @click="tab = 'watu'"
                :class="tab === 'watu' ? 'admin-prod-btn-primary' : 'admin-prod-btn-ghost'"
                class="rounded-xl px-4 py-2 text-sm font-semibold">Watu</button>
            <button type="button" @click="tab = 'needed'"
                :class="tab === 'needed' ? 'admin-prod-btn-primary' : 'admin-prod-btn-ghost'"
                class="rounded-xl px-4 py-2 text-sm font-semibold">Needed</button>
        </div>

        <div x-show="tab === 'sell'" x-cloak class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Sell (instant)</h2>
                <p class="admin-prod-form-hint">Agent instant sales awaiting payment channel on pending sales.</p>
            </div>
            <div class="overflow-x-auto p-4 sm:p-6">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3 pr-4">Date</th>
                            <th class="py-3 pr-4">Agent</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4">Product</th>
                            <th class="py-3 pr-4 text-right">Amount</th>
                            <th class="py-3">Channel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingAgentSales as $row)
                            <tr>
                                <td class="py-3 pr-4 whitespace-nowrap">{{ $row->date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $row->seller?->name ?? $row->seller_name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $row->customer_name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $row->product?->name ?? '—' }}</td>
                                <td class="py-3 pr-4 text-right font-medium">{{ number_format((float) ($row->total_selling_value ?? 0), 2) }}</td>
                                <td class="py-3">{{ $row->paymentOption?->name ?? '— Pending —' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No agent instant sales in this list.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="tab === 'watu'" x-cloak class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Watu (credit)</h2>
                <p class="admin-prod-form-hint">Agent credit sales from the app.</p>
            </div>
            <div class="overflow-x-auto p-4 sm:p-6">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3 pr-4">Date</th>
                            <th class="py-3 pr-4">Agent</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4">Phone</th>
                            <th class="py-3 pr-4">Product</th>
                            <th class="py-3 pr-4 text-right">Total</th>
                            <th class="py-3 pr-4 text-right">Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($agentCredits as $c)
                            <tr>
                                <td class="py-3 pr-4 whitespace-nowrap">{{ $c->date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $c->agent?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $c->customer_name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $c->customer_phone ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $c->product?->name ?? '—' }}</td>
                                <td class="py-3 pr-4 text-right font-medium">{{ number_format((float) ($c->total_amount ?? 0), 2) }}</td>
                                <td class="py-3 pr-4 text-right">{{ number_format((float) ($c->paid_amount ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">No Watu (credit) records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="tab === 'needed'" x-cloak class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Needed</h2>
                <p class="admin-prod-form-hint">Category and model requests submitted by agents.</p>
            </div>
            <div class="overflow-x-auto p-4 sm:p-6">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3 pr-4">Submitted</th>
                            <th class="py-3 pr-4">Agent</th>
                            <th class="py-3 pr-4">Category</th>
                            <th class="py-3 pr-4">Model</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customerNeeds as $n)
                            <tr>
                                <td class="py-3 pr-4 whitespace-nowrap">{{ $n->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $n->agent?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $n->category?->name ?? '—' }}</td>
                                <td class="py-3 pr-4">{{ $n->product?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">No needs submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
