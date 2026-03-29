<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Analytics</p>
                <h1 class="admin-prod-title">Sales reports</h1>
                <p class="admin-prod-subtitle">Storefront totals, branch purchase mix, and recent sales trend.</p>
            </div>
            <button type="button"
                class="admin-prod-btn-ghost shrink-0 cursor-default opacity-80"
                title="Placeholder — wire export when ready">
                Export data
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-8">
            <div class="admin-clay-panel overflow-hidden">
                <div class="admin-prod-form-head !py-4">
                    <p class="admin-prod-eyebrow !mb-1">Revenue</p>
                    <p class="admin-prod-form-title !text-2xl font-variant-numeric">{{ number_format($totalSales, 0) }} TZS</p>
                    <p class="admin-prod-form-hint !mt-1 text-green-700">+12% vs last month (sample)</p>
                </div>
            </div>
            <div class="admin-clay-panel overflow-hidden">
                <div class="admin-prod-form-head !py-4">
                    <p class="admin-prod-eyebrow !mb-1">Orders</p>
                    <p class="admin-prod-form-title !text-2xl font-variant-numeric">{{ number_format($totalOrders) }}</p>
                    <p class="admin-prod-form-hint !mt-1 text-green-700">+5% vs last month (sample)</p>
                </div>
            </div>
            <div class="admin-clay-panel overflow-hidden">
                <div class="admin-prod-form-head !py-4">
                    <p class="admin-prod-eyebrow !mb-1">Customers</p>
                    <p class="admin-prod-form-title !text-2xl font-variant-numeric">{{ number_format($totalCustomers) }}</p>
                    <p class="admin-prod-form-hint !mt-1">Active accounts</p>
                </div>
            </div>
        </div>

        @if($branchesBusiness->isNotEmpty() || $unassignedPurchases > 0)
            <div class="admin-clay-panel overflow-hidden mb-8">
                <div class="admin-prod-form-head">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="admin-prod-form-title">Business by branch (purchases)</h2>
                            <p class="admin-prod-form-hint">Highlight a branch to see detail above the table.</p>
                        </div>
                        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-2">
                            <div>
                                <label for="branch_id" class="admin-prod-label !mb-1">Branch</label>
                                <select name="branch_id" id="branch_id" onchange="this.form.submit()" class="admin-prod-select text-sm min-w-[200px] py-2">
                                    <option value="">All branches</option>
                                    @foreach($branchesBusiness as $row)
                                        <option value="{{ $row->id }}" @selected(request('branch_id') == $row->id)>{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(request('branch_id'))
                                <a href="{{ route('admin.reports.index') }}" class="admin-prod-btn-ghost text-sm py-2">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="admin-prod-form-body !pt-4">
                    @if($selectedBranchDetail)
                        <div class="admin-prod-alert admin-prod-alert--warning mb-4">
                            <span class="font-semibold text-slate-800">{{ $selectedBranchDetail->branch->name }}</span>
                            <span class="block mt-1 text-sm">
                                Purchases: {{ $selectedBranchDetail->purchase_count }}
                                · Total:
                                <strong class="text-[#c2410c]">{{ number_format($selectedBranchDetail->purchase_total, 2) }} TZS</strong>
                            </span>
                        </div>
                    @endif

                    <div class="admin-prod-table-wrap overflow-x-auto rounded-xl">
                        <table class="min-w-[480px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="admin-prod-th">Branch</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Purchases</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Value (TZS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchesBusiness as $row)
                                    <tr class="@if(request('branch_id') == $row->id) bg-orange-50/40 @endif">
                                        <td class="font-medium text-[#232f3e]">{{ $row->name }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row->purchase_count) }}</td>
                                        <td class="text-right font-semibold font-variant-numeric">{{ number_format($row->purchase_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if($unassignedPurchases > 0)
                                    <tr class="text-slate-600">
                                        <td class="italic">No branch assigned</td>
                                        <td class="text-right">{{ number_format($unassignedPurchases) }}</td>
                                        <td class="text-right">{{ number_format($unassignedPurchaseTotal, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Totals use each purchase amount (or quantity × unit price).</p>
                </div>
            </div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Sales overview (last 7 days)</h2>
            </div>
            <div class="admin-prod-form-body !pt-6">
                <div class="h-64 flex items-end justify-between gap-2 px-1">
                    @foreach($salesData as $date => $amount)
                        @php
                            $max = max($salesData) > 0 ? max($salesData) : 1;
                            $height = ($amount / $max) * 100;
                        @endphp
                        <div class="flex-1 flex flex-col items-center group min-w-0">
                            <div class="w-full rounded-t-md bg-gradient-to-t from-[#e07800] to-[#fa8900] opacity-85 group-hover:opacity-100 transition-opacity relative shadow-inner"
                                style="height: {{ $height > 0 ? $height : 1 }}%; min-height: 4px;">
                                <div
                                    class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#232f3e] text-white text-xs px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg">
                                    {{ number_format($amount) }} TZS
                                </div>
                            </div>
                            <span class="text-[10px] sm:text-xs text-slate-500 mt-2 text-center leading-tight">
                                {{ \Carbon\Carbon::parse($date)->format('M j') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
