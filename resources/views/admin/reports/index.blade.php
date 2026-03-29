<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">Business Reports</h2>
        <button
            class="admin-clay-panel px-4 py-2 text-slate-700 text-sm font-medium border border-slate-200/80 hover:shadow-md transition-shadow">
            Export Data
        </button>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="admin-clay-panel p-6">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Sales</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSales, 0) }} TZS</p>
            <div class="mt-2 text-sm text-green-600 font-medium">
                +12% from last month
            </div>
        </div>
        <div class="admin-clay-panel p-6">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Orders</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalOrders) }}</p>
            <div class="mt-2 text-sm text-green-600 font-medium">
                +5% from last month
            </div>
        </div>
        <div class="admin-clay-panel p-6">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Customers</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalCustomers) }}</p>
            <div class="mt-2 text-sm text-slate-500 font-medium">
                Active users
            </div>
        </div>
    </div>

    @if($branchesBusiness->isNotEmpty() || $unassignedPurchases > 0)
        <!-- Purchases by branch -->
        <div class="admin-clay-panel p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h3 class="font-bold text-lg text-slate-800">Business by branch (purchases)</h3>
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
                    <label for="branch_id" class="text-sm text-slate-600">Highlight branch</label>
                    <select name="branch_id" id="branch_id" onchange="this.form.submit()"
                        class="rounded-md border-slate-300 shadow-sm text-sm focus:border-[#fa8900] focus:ring-[#fa8900] min-w-[200px]">
                        <option value="">All branches</option>
                        @foreach($branchesBusiness as $row)
                            <option value="{{ $row->id }}" @selected(request('branch_id') == $row->id)>{{ $row->name }}</option>
                        @endforeach
                    </select>
                    @if(request('branch_id'))
                        <a href="{{ route('admin.reports.index') }}" class="text-sm text-slate-600 hover:text-slate-900 underline">Clear</a>
                    @endif
                </form>
            </div>

            @if($selectedBranchDetail)
                <div class="mb-6 p-4 rounded-lg bg-orange-50 border border-orange-200">
                    <p class="text-sm font-semibold text-slate-800">{{ $selectedBranchDetail->branch->name }}</p>
                    <p class="text-sm text-slate-600 mt-1">
                        Purchases: <span class="font-medium">{{ $selectedBranchDetail->purchase_count }}</span>
                        · Total value:
                        <span class="font-bold text-[#fa8900]">{{ number_format($selectedBranchDetail->purchase_total, 2) }} TZS</span>
                    </p>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
                            <th class="py-3 pr-4">Branch</th>
                            <th class="py-3 pr-4 text-right">Purchases</th>
                            <th class="py-3 text-right">Purchase value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($branchesBusiness as $row)
                            <tr class="hover:bg-slate-50 @if(request('branch_id') == $row->id) bg-orange-50/50 @endif">
                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $row->name }}</td>
                                <td class="py-3 pr-4 text-right text-slate-700">{{ number_format($row->purchase_count) }}</td>
                                <td class="py-3 text-right font-semibold text-slate-900">{{ number_format($row->purchase_total, 2) }}</td>
                            </tr>
                        @endforeach
                        @if($unassignedPurchases > 0)
                            <tr class="hover:bg-slate-50 text-slate-600">
                                <td class="py-3 pr-4 italic">No branch assigned</td>
                                <td class="py-3 pr-4 text-right">{{ number_format($unassignedPurchases) }}</td>
                                <td class="py-3 text-right">{{ number_format($unassignedPurchaseTotal, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-xs text-slate-500">Values use each purchase’s total (or quantity × unit price if total is missing).</p>
        </div>
    @endif

    <!-- Recent Sales Chart (Placeholder) -->
    <div class="admin-clay-panel p-6 mb-8">
        <h3 class="font-bold text-lg text-slate-800 mb-4">Sales Overview (Last 7 Days)</h3>
        <div class="h-64 flex items-end justify-between gap-2">
            @foreach($salesData as $date => $amount)
                @php
                    $max = max($salesData) > 0 ? max($salesData) : 1;
                    $height = ($amount / $max) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-[#fa8900] rounded-t opacity-80 group-hover:opacity-100 transition-opacity relative"
                        style="height: {{ $height > 0 ? $height : 1 }}%">
                        <div
                            class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                            {{ number_format($amount) }} TZS
                        </div>
                    </div>
                    <span
                        class="text-xs text-slate-500 mt-2 rotate-45 origin-left sm:rotate-0">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                </div>
            @endforeach
        </div>
    </div>

</x-admin-layout>