<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Purchases</h1>
                <p class="mt-2 text-slate-600">Manage stock purchases.</p>
            </div>
            <a href="{{ route('admin.stock.create-purchase') }}" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Purchase
            </a>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Distributor</th>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Quantity</th>
                        <th class="px-6 py-3">Unit Price</th>
                        <th class="px-6 py-3">Total Value</th>
                        <th class="px-6 py-3">Paid Date</th>
                        <th class="px-6 py-3">Paid Amount</th>
                        <th class="px-6 py-3">Pending Amount</th>
                        <th class="px-6 py-3">Sell Price</th>
                        <th class="px-6 py-3">Limit Status</th>
                        <th class="px-6 py-3">Limit Left</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $purchase->name ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $purchase->date }}</td>
                            <td class="px-6 py-3">{{ $purchase->stock?->name ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $purchase->distributor_name ?? '-' }}</td>
                            <td class="px-6 py-3 font-medium">{{ $purchase->product->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $purchase->quantity }}</td>
                            <td class="px-6 py-3">{{ number_format($purchase->unit_price, 2) }}</td>
                            @php $totalVal = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price); $pendingVal = max(0, $totalVal - $purchase->paid_amount); @endphp
                            <td class="px-6 py-3 font-bold">{{ number_format($totalVal, 2) }}</td>
                            <td class="px-6 py-3">{{ $purchase->paid_date ?? '-' }}</td>
                            <td class="px-6 py-3">{{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="px-6 py-3 font-medium">{{ number_format($pendingVal, 2) }}</td>
                            <td class="px-6 py-3">{{ $purchase->sell_price !== null ? number_format($purchase->sell_price, 2) : '–' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $purchase->limit_status === 'complete' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $purchase->limit_status ?? 'pending' }}</span>
                            </td>
                            <td class="px-6 py-3">{{ $purchase->limit_remaining ?? '–' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase 
                                    {{ $purchase->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($purchase->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $purchase->payment_status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 flex gap-2">
                                <a href="{{ route('admin.stock.edit-purchase', $purchase->id) }}" class="text-blue-600 hover:text-blue-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.stock.destroy-purchase', $purchase->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-4 text-center text-slate-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
