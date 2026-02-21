<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Distribution Sales</h1>
                <p class="mt-2 text-slate-600">Sales to dealers. Data from dealer orders (buy price from purchases, sell price from order).</p>
            </div>
            <a href="{{ route('admin.stock.create-distribution') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Add manual sale</a>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Dealer</th>
                        <th class="px-6 py-3">Seller</th>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Qty</th>
                        <th class="px-6 py-3">Buy Price</th>
                        <th class="px-6 py-3">Sell Price</th>
                        <th class="px-6 py-3">Total Buy</th>
                        <th class="px-6 py-3">Total Sell</th>
                        <th class="px-6 py-3">Paid</th>
                        <th class="px-6 py-3">Pending (Balance)</th>
                        <th class="px-6 py-3">Commission</th>
                        <th class="px-6 py-3">Profit</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($distributionSales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $sale->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->seller_name ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $sale->product ? ($sale->product->category?->name . ' - ' . $sale->product->name) : 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->purchase_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->selling_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->total_purchase_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($sale->total_selling_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->paid_amount ?? 0, 0) }}</td>
                            <td class="px-6 py-3 font-medium">{{ number_format($sale->balance ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->commission ?? 0, 0) }}</td>
                            <td class="px-6 py-3 text-green-600">{{ number_format($sale->profit ?? 0, 0) }}</td>
                            <td class="px-6 py-3">
                                @php $st = $sale->status ?? 'pending'; @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $st === 'complete' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="px-6 py-3 flex gap-2 items-center">
                                <a href="{{ route('admin.stock.edit-distribution', $sale->id) }}" class="text-blue-600 hover:text-blue-900 text-sm">Edit</a>
                                @if($st === 'pending')
                                    <form action="{{ route('admin.stock.distribution-update-status', $sale) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-slate-600 hover:text-slate-900 underline text-xs">Mark complete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-4 text-center text-slate-500">No distribution sales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
