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

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Seller</th>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Qty</th>
                        <th class="px-6 py-3">Buy Price</th>
                        <th class="px-6 py-3">Sell Price</th>
                        <th class="px-6 py-3">Total Buy</th>
                        <th class="px-6 py-3">Total Sell</th>
                        <th class="px-6 py-3">Profit</th>
                        <th class="px-6 py-3">Comm.</th>
                        <th class="px-6 py-3">Collection</th>
                        <th class="px-6 py-3">Edit Comm.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($agentSales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $sale->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $sale->customer_name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->seller_name ?? $sale->agent?->name ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $sale->product ? (($sale->product->category->name ?? '—') . ' – ' . $sale->product->name) : 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->purchase_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->selling_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->total_purchase_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($sale->total_selling_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 text-green-600">{{ number_format($sale->profit ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->commission_paid ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format(($sale->total_selling_value ?? 0) - ($sale->balance ?? 0), 0) }}</td>
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
