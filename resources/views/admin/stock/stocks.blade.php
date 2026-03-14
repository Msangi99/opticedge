<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Stocks</h1>
                <p class="mt-2 text-slate-600">Stock buckets used in the app for product list and agents. Shows stock quantity, added quantity from purchases, and status.</p>
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
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3 bg-gray-100">Name</th>
                        <th class="px-6 py-3 bg-gray-100">Stock Quantity</th>
                        <th class="px-6 py-3 bg-gray-100">Added</th>
                        <th class="px-6 py-3 bg-gray-100">Status</th>
                        <th class="px-6 py-3 bg-gray-100">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($stocks as $stock)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">
                                @if($hasPurchases)
                                    <a href="{{ route('admin.stock.purchase.show', $stock->id) }}" class="text-[#fa8900] hover:underline">
                                        {{ $stock->name }}
                                    </a>
                                @else
                                    <a href="{{ route('admin.stock.stocks.show', $stock->id) }}" class="text-[#fa8900] hover:underline">{{ $stock->name }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-3">{{ number_format($stock->stock_quantity) }}</td>
                            <td class="px-6 py-3">{{ number_format($stock->added) }}</td>
                            <td class="px-6 py-3">
                                @if($stock->status === 'complete')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Complete</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($hasPurchases)
                                    <form action="{{ route('admin.stock.destroy-purchase', $stock->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.stock.stock-receipts', $stock->id) }}" 
                                    class="px-3 py-1 text-xs bg-[#fa8900] text-white rounded hover:bg-[#e67d00] transition-colors flex items-center gap-1 inline-flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        Receipts
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                @if(isset($hasPurchases) && $hasPurchases)
                                    <p class="font-medium text-slate-700 mb-2">No stocks found, but you have {{ $purchasesCount }} purchase(s) in the system.</p>
                                    <p class="text-sm mt-2 text-slate-600">To view your purchases, go to the <a href="{{ route('admin.stock.purchases') }}" class="text-[#fa8900] hover:underline font-medium">Purchases page</a>.</p>
                                    @if(isset($distributors) && $distributors->count() > 0)
                                        <p class="text-xs mt-2 text-slate-500">Your purchases are from: {{ $distributors->implode(', ') }}</p>
                                    @endif
                                @else
                                    <p>No stocks found in the database.</p>
                                    <p class="text-xs mt-2 text-slate-400">Note: This page shows Stock records, not Purchases. To view purchases, go to the <a href="{{ route('admin.stock.purchases') }}" class="text-[#fa8900] hover:underline">Purchases page</a>.</p>
                                    <p class="text-xs mt-1 text-slate-400">If you have purchases but no stocks, you may need to create stock records first.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
