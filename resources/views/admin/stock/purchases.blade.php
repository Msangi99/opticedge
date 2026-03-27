<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Purchases</h1>
                <p class="mt-2 text-slate-600">Manage stock purchases.</p>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('admin.stock.update-product-prices') }}" method="POST" onsubmit="return confirm('This will update all existing product prices to use sell_price from their latest purchase. Continue?');" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors font-medium flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Update Product Prices
                    </button>
                </form>
                <a href="{{ route('admin.stock.purchases.receipts') }}" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    View All Receipts
                </a>
                <a href="{{ route('admin.stock.create-purchase') }}" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Purchase
                </a>
            </div>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif

        <x-admin-page-dashboard label="Summary (current filter)" class="mt-8">
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Purchases</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($purchaseDashboard['count']) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total purchase value</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($purchaseDashboard['total_value'], 2) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Pending to pay</dt>
                    <dd class="text-lg font-semibold text-amber-700">{{ number_format($purchaseDashboard['pending_amount'], 2) }} TZS</dd>
                </div>
            </dl>
        </x-admin-page-dashboard>

        <!-- Date Range Filter -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 p-4 space-y-4">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.stock.purchases', ['preset' => 'this_week']) }}" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50 {{ ($preset ?? '') === 'this_week' ? 'bg-[#fa8900] text-white border-[#fa8900]' : 'text-slate-700' }}">This week</a>
                <a href="{{ route('admin.stock.purchases', ['preset' => 'last_week']) }}" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50 {{ ($preset ?? '') === 'last_week' ? 'bg-[#fa8900] text-white border-[#fa8900]' : 'text-slate-700' }}">Last week</a>
                <a href="{{ route('admin.stock.purchases', ['preset' => 'last_30_days']) }}" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50 {{ ($preset ?? '') === 'last_30_days' ? 'bg-[#fa8900] text-white border-[#fa8900]' : 'text-slate-700' }}">Last 30 days</a>
            </div>
            <form method="GET" action="{{ route('admin.stock.purchases') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $dateFrom ?? request('date_from')) }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $dateTo ?? request('date_to')) }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">Filter</button>
                    @if(($dateFrom ?? null) || ($dateTo ?? null) || request('date_from') || request('date_to') || ($preset ?? null))
                        <a href="{{ route('admin.stock.purchases') }}" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[1200px]">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3 bg-gray-100">Invoice Number</th>
                        <th class="px-6 py-3 bg-gray-100">Date</th>
                        <th class="px-6 py-3 bg-gray-100">Branch</th>
                        <th class="px-6 py-3 bg-gray-100">Distributor</th>
                        <th class="px-6 py-3 bg-gray-100">Product</th>
                        <th class="px-6 py-3 bg-gray-100">Quantity</th>
                        <th class="px-6 py-3 bg-gray-100">Unit Price</th>
                        <th class="px-6 py-3 bg-gray-100">Total Value</th>
                        <th class="px-6 py-3 bg-gray-100">Paid Date</th>
                        <th class="px-6 py-3 bg-gray-100">Paid Amount</th>
                        <th class="px-6 py-3 bg-gray-100">Pending Amount</th>
                        <th class="px-6 py-3 bg-gray-100">Sell Price</th>
                        <th class="px-6 py-3 bg-gray-100">Status</th>
                        <th class="px-6 py-3 bg-gray-100">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $purchase->name ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $purchase->date }}</td>
                            <td class="px-6 py-3">{{ $purchase->branch?->name ?? '–' }}</td>
                            <td class="px-6 py-3">
                                <span>{{ $purchase->distributor_name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium">{{ $purchase->product?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $purchase->quantity }}</td>
                            <td class="px-6 py-3">{{ number_format($purchase->unit_price, 2) }}</td>
                            @php $totalVal = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price); $paidVal = (float) ($purchase->paid_amount ?? 0); $pendingVal = max(0, $totalVal - $paidVal); @endphp
                            <td class="px-6 py-3 font-bold">{{ number_format($totalVal, 2) }}</td>
                            <td class="px-6 py-3">{{ $purchase->paid_date ?? '-' }}</td>
                            <td class="px-6 py-3">{{ number_format($paidVal, 2) }}</td>
                            <td class="px-6 py-3 font-medium">{{ number_format($pendingVal, 2) }}</td>
                            <td class="px-6 py-3">{{ $purchase->sell_price !== null ? number_format($purchase->sell_price, 2) : '–' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase 
                                    {{ $purchase->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($purchase->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $purchase->payment_status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 flex gap-2">
                                <a href="{{ route('admin.stock.edit-purchase', $purchase->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.stock.destroy-purchase', $purchase->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-6 py-4 text-center text-slate-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</x-admin-layout>
