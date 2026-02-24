<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Stocks</h1>
                <p class="mt-2 text-slate-600">Stock buckets used in the app for product list and agents. Data from purchases (pending/complete limits).</p>
            </div>
            <a href="{{ route('admin.stock.add-product') }}" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#e67d00] font-medium text-sm">Add Product (IMEI)</a>
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-md">
                {{ session('info') }}
            </div>
        @endif

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Limit</th>
                        <th class="px-6 py-3">Available</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($stocks as $stock)
                        @php
                            $available = $stock->quantity_available ?? 0;
                            $underLimit = $available < $stock->stock_limit;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">
                                <a href="{{ route('admin.stock.stocks.show', $stock) }}" class="text-[#fa8900] hover:underline">{{ $stock->name }}</a>
                            </td>
                            <td class="px-6 py-3">{{ number_format($stock->stock_limit) }}</td>
                            <td class="px-6 py-3">{{ number_format($available) }}</td>
                            <td class="px-6 py-3">
                                @if($underLimit)
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-800">Under limit</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No stocks yet. Create a stock from the admin app (Add Product → create stock).</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
