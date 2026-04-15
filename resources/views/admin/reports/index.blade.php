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

        @php
            $asr = $agentStockReport;
            $agentColorBands = [
                'bg-orange-100/80',
                'bg-sky-100/80',
                'bg-emerald-100/80',
                'bg-violet-100/80',
                'bg-amber-100/80',
                'bg-rose-100/80',
            ];
            $agentCellBands = [
                'bg-orange-100/40',
                'bg-sky-100/40',
                'bg-emerald-100/40',
                'bg-violet-100/40',
                'bg-amber-100/40',
                'bg-rose-100/40',
            ];
        @endphp
        <div class="admin-clay-panel overflow-hidden mb-8">
            <div class="admin-prod-form-head">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="admin-prod-form-title">Agent opening stock &amp; sales (by product)</h2>
                        <p class="admin-prod-form-hint max-w-3xl">
                            <strong>Opening</strong> is end-of-previous-day <strong>closing</strong> (rollover).
                            <strong>Shop</strong> counts devices not assigned to an agent; each <strong>agent</strong> counts assigned IMEIs.
                            <strong>Sales</strong> are units with <code class="text-xs bg-slate-100 px-1 rounded">sold_at</code> on the report date.
                            <strong>Transfer</strong> (shop) is net branch moves that day for unassigned items.
                        </p>
                    </div>
                    <a href="{{ route('admin.reports.agent-stock-export', ['report_date' => $asr['report_date'], 'branch_id' => request('branch_id')]) }}"
                        class="admin-prod-btn-primary text-sm py-2 px-4 shrink-0 whitespace-nowrap">
                        Export Excel (CSV)
                    </a>
                </div>
            </div>
            <div class="admin-prod-form-body !pt-4 border-t border-white/60">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-3 mb-4">
                    <div>
                        <label for="report_date" class="admin-prod-label !mb-1">Report date</label>
                        <input type="date" id="report_date" name="report_date" value="{{ $asr['report_date'] }}"
                            class="admin-prod-input py-2 text-sm min-w-[11rem]">
                    </div>
                    <div>
                        <label for="agent_report_branch_id" class="admin-prod-label !mb-1">Branch</label>
                        <select name="branch_id" id="agent_report_branch_id" class="admin-prod-select text-sm min-w-[200px] py-2">
                            <option value="">All branches</option>
                            @foreach($reportBranchOptions as $b)
                                <option value="{{ $b->id }}" @selected((string) request('branch_id') === (string) $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="admin-prod-btn-primary text-sm py-2 px-4">Apply</button>
                </form>

                @if($asr['agents']->isEmpty())
                    <p class="text-sm text-amber-800 bg-amber-50/80 border border-amber-200/70 rounded-lg px-3 py-2 mb-4">No agents yet — only <strong>Shop</strong> columns apply. Add agents under Sales team to see per-agent stock.</p>
                @endif
                @if(count($asr['rows']) === 0)
                    <p class="text-sm text-slate-500 py-6">No stock movement for this date and branch filter.</p>
                @else
                    <div class="admin-prod-table-wrap overflow-x-auto rounded-xl">
                        <table class="min-w-[720px] text-sm">
                            <thead>
                                <tr>
                                    <th scope="col" class="admin-prod-th align-bottom" rowspan="2">Product</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end align-bottom" rowspan="2">Price (TZS)</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end align-bottom" rowspan="2">Purchased<br><span class="font-normal text-slate-500">today</span></th>
                                    <th scope="col" class="admin-prod-th text-center bg-slate-100/80" colspan="4">Shop</th>
                                    @foreach($asr['agents'] as $agent)
                                        @php $agentBand = $agentColorBands[$loop->index % count($agentColorBands)]; @endphp
                                        <th scope="col" class="admin-prod-th text-center {{ $agentBand }}" colspan="3">{{ $agent->name }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end text-xs bg-slate-100/80">Opening</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end text-xs bg-slate-100/80">Sales</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end text-xs bg-slate-100/80">Transfer</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end text-xs bg-slate-100/80">Closing</th>
                                    @foreach($asr['agents'] as $agent)
                                        @php $agentBand = $agentColorBands[$loop->index % count($agentColorBands)]; @endphp
                                        <th scope="col" class="admin-prod-th admin-prod-th--end text-xs {{ $agentBand }}">Opening</th>
                                        <th scope="col" class="admin-prod-th admin-prod-th--end text-xs {{ $agentBand }}">Sales</th>
                                        <th scope="col" class="admin-prod-th admin-prod-th--end text-xs {{ $agentBand }}">Closing</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asr['rows'] as $row)
                                    <tr>
                                        <td class="font-medium text-[#232f3e]">{{ $row['name'] }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row['price'], 0) }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row['purchased_today']) }}</td>
                                        <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($row['shop']['opening']) }}</td>
                                        <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($row['shop']['sales']) }}</td>
                                        <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($row['shop']['transfer']) }}</td>
                                        <td class="text-right font-variant-numeric bg-slate-50/50 font-semibold">{{ number_format($row['shop']['closing']) }}</td>
                                        @foreach($asr['agents'] as $agent)
                                            @php $ac = $row['agents'][(int) $agent->id] ?? ['opening' => 0, 'sales' => 0, 'closing' => 0]; @endphp
                                            @php $agentCellBand = $agentCellBands[$loop->index % count($agentCellBands)]; @endphp
                                            <td class="text-right font-variant-numeric {{ $agentCellBand }}">{{ number_format($ac['opening']) }}</td>
                                            <td class="text-right font-variant-numeric {{ $agentCellBand }}">{{ number_format($ac['sales']) }}</td>
                                            <td class="text-right font-variant-numeric {{ $agentCellBand }} font-semibold">{{ number_format($ac['closing']) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                @php $tot = $asr['totals']; @endphp
                                <tr class="border-t-2 border-slate-300 font-semibold text-[#232f3e]">
                                    <td>Totals</td>
                                    <td class="text-right">—</td>
                                    <td class="text-right font-variant-numeric">{{ number_format($tot['purchased_today']) }}</td>
                                    <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($tot['shop']['opening']) }}</td>
                                    <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($tot['shop']['sales']) }}</td>
                                    <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($tot['shop']['transfer']) }}</td>
                                    <td class="text-right font-variant-numeric bg-slate-50/50">{{ number_format($tot['shop']['closing']) }}</td>
                                    @foreach($asr['agents'] as $agent)
                                        @php $tc = $tot['agents'][(int) $agent->id] ?? ['opening' => 0, 'sales' => 0, 'closing' => 0]; @endphp
                                        @php $agentCellBand = $agentCellBands[$loop->index % count($agentCellBands)]; @endphp
                                        <td class="text-right font-variant-numeric {{ $agentCellBand }}">{{ number_format($tc['opening']) }}</td>
                                        <td class="text-right font-variant-numeric {{ $agentCellBand }}">{{ number_format($tc['sales']) }}</td>
                                        <td class="text-right font-variant-numeric {{ $agentCellBand }}">{{ number_format($tc['closing']) }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Closing = opening − sales (+ shop transfer net). Purchased today = new device rows added this date.</p>
                @endif
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
                            @if(request('report_date'))
                                <input type="hidden" name="report_date" value="{{ request('report_date') }}">
                            @endif
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
                                <a href="{{ route('admin.reports.index', request()->only('report_date')) }}" class="admin-prod-btn-ghost text-sm py-2">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="admin-prod-form-body !pt-4">
                    @if($selectedBranchDetail)
                        <div class="admin-prod-alert admin-prod-alert--warning mb-4">
                            <span class="font-semibold text-slate-800">{{ $selectedBranchDetail->branch->name }}</span>
                            <span class="block mt-1 text-sm">
                                Opening: {{ number_format($selectedBranchDetail->opening_stock) }}
                                ·
                                Purchases: {{ $selectedBranchDetail->purchase_count }}
                                · Sales: {{ number_format($selectedBranchDetail->sales_count) }}
                                · Closing: {{ number_format($selectedBranchDetail->closing_stock) }}
                                · Total:
                                <strong class="text-[#c2410c]">{{ number_format($selectedBranchDetail->purchase_total, 2) }} TZS</strong>
                            </span>
                        </div>
                    @endif

                    <div class="admin-prod-table-wrap overflow-x-auto rounded-xl">
                        <table class="min-w-[760px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="admin-prod-th">Branch</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Opening Stock</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Purchases</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Sales</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Closing Stock</th>
                                    <th scope="col" class="admin-prod-th admin-prod-th--end">Value (TZS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchesBusiness as $row)
                                    <tr class="@if(request('branch_id') == $row->id) bg-orange-50/40 @endif">
                                        <td class="font-medium text-[#232f3e]">{{ $row->name }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row->opening_stock) }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row->purchase_count) }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row->sales_count) }}</td>
                                        <td class="text-right font-variant-numeric text-slate-700">{{ number_format($row->closing_stock) }}</td>
                                        <td class="text-right font-semibold font-variant-numeric">{{ number_format($row->purchase_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if($unassignedPurchases > 0)
                                    <tr class="text-slate-600">
                                        <td class="italic">No branch assigned</td>
                                        <td class="text-right">{{ number_format($unassignedOpeningStock) }}</td>
                                        <td class="text-right">{{ number_format($unassignedPurchases) }}</td>
                                        <td class="text-right">{{ number_format($unassignedSales) }}</td>
                                        <td class="text-right">{{ number_format($unassignedClosingStock) }}</td>
                                        <td class="text-right">{{ number_format($unassignedPurchaseTotal, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Closing stock is calculated as Opening stock + Purchases - Sales. Totals use each purchase amount (or quantity × unit price).</p>
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
