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
        @if(session('error'))
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3 bg-blue-100">Date</th>
                        <th class="px-6 py-3 bg-green-100">Dealer</th>
                        <th class="px-6 py-3 bg-yellow-100">Seller</th>
                        <th class="px-6 py-3 bg-purple-100">Product</th>
                        <th class="px-6 py-3 bg-pink-100">Qty</th>
                        <th class="px-6 py-3 bg-indigo-100">Buy Price</th>
                        <th class="px-6 py-3 bg-red-100">Sell Price</th>
                        <th class="px-6 py-3 bg-orange-100">Total Buy</th>
                        <th class="px-6 py-3 bg-teal-100">Total Sell</th>
                        <th class="px-6 py-3 bg-cyan-100">Paid</th>
                        <th class="px-6 py-3 bg-amber-100">Pending (Balance)</th>
                        <th class="px-6 py-3 bg-lime-100">Commission</th>
                        <th class="px-6 py-3 bg-rose-100">Profit</th>
                        <th class="px-6 py-3 bg-sky-100">Status</th>
                        <th class="px-6 py-3 bg-violet-100">Channel (Bank)</th>
                        <th class="px-6 py-3 bg-fuchsia-100">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($distributionSales as $sale)
                        @php $st = $sale->status ?? 'pending'; @endphp
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
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $st === 'complete' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="px-6 py-3">
                                @if($st === 'pending')
                                    @if($sale->payment_option_id)
                                        <span class="text-slate-600">{{ $sale->paymentOption?->name ?? '—' }}</span>
                                    @else
                                        <form action="{{ route('admin.stock.distribution-save-channel', $sale->id) }}" method="POST" class="inline">
                                            @csrf
                                            <select name="payment_option_id" required onchange="this.form.submit()"
                                                class="text-sm rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                                                <option value="">Chagua channel...</option>
                                                @foreach($bankPaymentOptions as $option)
                                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-slate-400">{{ $sale->paymentOption?->name ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 flex gap-2 items-center">
                                <a href="{{ route('admin.stock.edit-distribution', $sale->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.stock.destroy-distribution', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this distribution sale?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
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
                            <td colspan="16" class="px-6 py-4 text-center text-slate-500">No distribution sales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
