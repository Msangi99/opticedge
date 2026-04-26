<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar !mb-4">
            <div>
                <p class="admin-prod-eyebrow">Agents</p>
                <h1 class="admin-prod-title">Agent credit</h1>
                <p class="admin-prod-subtitle">Loans from agents to customers; record repayments per credit.</p>
            </div>
            <a href="{{ route('admin.stock.agent-credits.export-csv', request()->query()) }}" class="admin-prod-btn-ghost inline-flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 16.5V4.5m0 12 3.75-3.75M12 16.5l-3.75-3.75M3.75 19.5h16.5" />
                </svg>
                Export CSV
            </a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="admin-prod-alert admin-prod-alert--warning mb-4" role="status">{{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ $errors->first() }}</div>
        @endif

        <x-admin-page-dashboard label="Summary (current filter)" class="mb-6">
            <dl class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Credits</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentCreditsDashboard['count']) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total credit</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentCreditsDashboard['total_credit'], 2) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total paid</dt>
                    <dd class="text-lg font-semibold text-green-700">{{ number_format($agentCreditsDashboard['total_paid'], 2) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Pending</dt>
                    <dd class="text-lg font-semibold text-amber-700">{{ number_format($agentCreditsDashboard['total_pending'], 2) }} TZS</dd>
                </div>
            </dl>
        </x-admin-page-dashboard>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden mb-6">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Date filter</h2>
            </div>
            <div class="admin-prod-form-body">
                <form method="GET" action="{{ route('admin.stock.agent-credits') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label for="date_from" class="admin-prod-label">From date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="admin-prod-input w-auto min-w-[10rem]">
                    </div>
                    <div>
                        <label for="date_to" class="admin-prod-label">To date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="admin-prod-input w-auto min-w-[10rem]">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="admin-prod-btn-primary">Filter</button>
                        @if(request('date_from') || request('date_to'))
                            <a href="{{ route('admin.stock.agent-credits') }}" class="admin-prod-btn-ghost">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-clay-panel overflow-x-auto">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush min-w-0">
                <table class="min-w-[1080px]">
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Date</th>
                            <th scope="col" class="admin-prod-th">Agent</th>
                            <th scope="col" class="admin-prod-th">Customer</th>
                            <th scope="col" class="admin-prod-th">Product</th>
                            <th scope="col" class="admin-prod-th">IMEI</th>
                            <th scope="col" class="admin-prod-th">Total</th>
                            <th scope="col" class="admin-prod-th min-w-[200px]">Channel</th>
                            <th scope="col" class="admin-prod-th min-w-[190px]">Comm.</th>
                            <th scope="col" class="admin-prod-th">Pending</th>
                            <th scope="col" class="admin-prod-th">Status</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $credit)
                            @php
                                $t = (float) $credit->total_amount;
                                $pend = max(0, $t - (float) ($credit->paid_amount ?? 0));
                            @endphp
                            <tr>
                                <td class="text-slate-600 text-sm">
                                    {{ $credit->date instanceof \Carbon\Carbon ? $credit->date->format('Y-m-d') : $credit->date }}</td>
                                <td class="text-slate-700">{{ $credit->agent?->name ?? '—' }}</td>
                                <td class="font-medium text-[#232f3e]">{{ $credit->customer_name }}</td>
                                <td class="text-slate-600 text-sm">
                                    {{ $credit->product ? (($credit->product->category?->name ?? '—') . ' – ' . $credit->product->name) : 'N/A' }}</td>
                                <td class="font-mono text-xs text-slate-600">{{ $credit->productListItem?->imei_number ?? '—' }}</td>
                                <td class="font-variant-numeric">{{ number_format($t, 2) }}</td>
                                <td class="align-middle">
                                    @if($paymentOptions->isEmpty())
                                        <span class="text-slate-500 text-sm">{{ $credit->paymentOption?->name ?? '—' }}</span>
                                    @elseif($pend > 0.0001)
                                        <form method="POST" action="{{ route('admin.stock.agent-credit-pay-remaining', $credit->id) }}"
                                            class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <select name="payment_option_id" required
                                                class="admin-prod-select text-sm min-w-[150px] max-w-[220px] py-1.5"
                                                title="Bank / payment channel">
                                                <option value="">Choose channel…</option>
                                                @foreach($paymentOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((int) ($credit->payment_option_id ?? 0) === (int) $option->id)>
                                                        {{ $option->name }} ({{ number_format((float) $option->balance, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="admin-prod-btn-primary text-xs py-1.5 px-3 shrink-0">Pay</button>
                                        </form>
                                    @else
                                        <span class="text-slate-600 text-sm">{{ $credit->paymentOption?->name ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="admin-prod-cell-actions min-w-[190px]">
                                    <form action="{{ route('admin.stock.agent-credits-update-commission', ['id' => $credit->id] + request()->query()) }}" method="POST"
                                        class="inline-flex items-center gap-2 flex-wrap justify-end">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="commission_paid" value="{{ $credit->commission_paid ?? 0 }}" step="0.01" min="0"
                                            class="admin-prod-input w-32 py-1.5 text-sm">
                                        <button type="submit" class="admin-prod-link text-sm whitespace-nowrap">Save</button>
                                    </form>
                                </td>
                                <td class="font-variant-numeric font-medium text-amber-800">{{ number_format($pend, 2) }}</td>
                                <td>
                                    <span
                                        class="admin-prod-dealer-status {{ $credit->payment_status === 'paid' ? 'admin-prod-dealer-status--active' : ($credit->payment_status === 'partial' ? 'admin-prod-dealer-status--pending' : 'admin-prod-dealer-status--suspended') }}">
                                        {{ $credit->payment_status }}
                                    </span>
                                </td>
                                <td class="admin-prod-cell-actions whitespace-nowrap">
                                    <a href="{{ route('admin.stock.edit-agent-credit', $credit->id) }}" class="admin-prod-link">Edit</a>
                                    @if(($credit->payment_status ?? '') === 'paid')
                                        <span class="text-slate-300 mx-1">|</span>
                                        <a href="{{ route('admin.stock.agent-credit-invoice', $credit->id) }}" class="admin-prod-link">Download invoice</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-slate-500 py-10">No agent credits yet. Credits appear when an agent sells on credit from the app.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($credits->hasPages())
                <div class="admin-prod-pagination">{{ $credits->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
