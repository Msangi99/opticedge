<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Agent Sales</h1>
                <p class="mt-2 text-slate-600">Sales made by agents. Buy price from purchases; sell price from agent. Admin can edit commission.</p>
            </div>
            <a href="{{ route('admin.stock.create-agent-sale') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Record manual sale</a>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('info'))
            <p class="mt-4 rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-800">{{ session('info') }}</p>
        @endif

        <x-admin-page-dashboard label="Summary (current filter)" class="mt-8">
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Sales</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentSalesDashboard['count']) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total selling</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentSalesDashboard['total_sell'], 0) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total profit</dt>
                    <dd class="text-lg font-semibold text-green-700">{{ number_format($agentSalesDashboard['total_profit'], 0) }} TZS</dd>
                </div>
            </dl>
        </x-admin-page-dashboard>

        <!-- Date Range Filter -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('admin.stock.agent-sales') }}" class="flex gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">Filter</button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('admin.stock.agent-sales') }}" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3 bg-gray-100">Date</th>
                        <th class="px-6 py-3 bg-gray-100">Customer</th>
                        <th class="px-6 py-3 bg-gray-100">Seller</th>
                        <th class="px-6 py-3 bg-gray-100">Product</th>
                        <th class="px-6 py-3 bg-gray-100">Qty</th>
                        <th class="px-6 py-3 bg-gray-100">Buy Price</th>
                        <th class="px-6 py-3 bg-gray-100">Sell Price</th>
                        <th class="px-6 py-3 bg-gray-100">Total Buy</th>
                        <th class="px-6 py-3 bg-gray-100">Total Sell</th>
                        <th class="px-6 py-3 bg-gray-100">Profit</th>
                        <th class="px-6 py-3 bg-gray-100">Comm.</th>
                        <th class="px-6 py-3 bg-gray-100">Channel</th>
                        <th class="px-6 py-3 bg-gray-100">Edit Comm.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($agentSales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $sale->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $sale->customer_name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->seller_name ?? $sale->agent?->name ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $sale->product ? (($sale->product->category?->name ?? '—') . ' – ' . $sale->product->name) : 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->purchase_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->selling_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->total_purchase_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($sale->total_selling_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 text-green-600">{{ number_format($sale->profit ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->commission_paid ?? 0, 0) }}</td>
                            <td class="px-6 py-3">
                                @if($sale->payment_option_id)
                                    <span class="text-slate-600">{{ $sale->paymentOption?->name ?? '—' }}</span>
                                @else
                                    <form action="{{ route('admin.stock.agent-sales-save-channel', $sale->id) }}" method="POST" class="inline">
                                        @csrf
                                        <select name="payment_option_id" required onchange="this.form.submit()"
                                            class="text-sm rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                                            <option value="">Choose channel...</option>
                                            @foreach($paymentOptions as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <form action="{{ route('admin.stock.agent-sales-update-commission', $sale->id) }}" method="POST" class="inline flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="commission_paid" value="{{ $sale->commission_paid ?? 0 }}" step="0.01" min="0" class="w-24 rounded border-slate-300 text-sm py-0.5">
                                    <button type="submit" class="text-xs text-[#fa8900] hover:underline">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-4 text-center text-slate-500">No agent sales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
