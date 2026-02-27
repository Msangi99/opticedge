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
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">
                                <a href="{{ route('admin.stock.purchase.show', $purchase->id) }}" class="text-[#fa8900] hover:underline">{{ $purchase->name }}</a>
                            </td>
                            <td class="px-6 py-3">{{ number_format($purchase->limit) }}</td>
                            <td class="px-6 py-3">{{ $purchase->available }}</td>
                            <td class="px-6 py-3">
                                @if($purchase->status === 'paid')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                                @elseif($purchase->status === 'partial')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-800">Partial</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700">{{ $purchase->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($purchase->stock_id)
                                    <a href="{{ route('admin.stock.stock-receipts', $purchase->stock_id) }}" 
                                       class="px-3 py-1 text-xs bg-[#fa8900] text-white rounded hover:bg-[#e67d00] transition-colors flex items-center gap-1 inline-flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        Receipts
                                    </a>
                                @else
                                    <span class="px-3 py-1 text-xs bg-slate-100 text-slate-700 rounded cursor-not-allowed flex items-center gap-1 inline-flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        No Receipts
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No purchases yet. Add a purchase to see it here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
