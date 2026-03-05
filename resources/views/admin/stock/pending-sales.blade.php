<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pending Sales</h1>
                <p class="mt-2 text-slate-600">Sales waiting for payment option selection. Select payment option and save to complete.</p>
            </div>
            <a href="{{ route('admin.stock.create-agent-sale') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Record new sale</a>
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
                        <th class="px-6 py-3">Total Sell</th>
                        <th class="px-6 py-3">Profit</th>
                        <th class="px-6 py-3">Payment Option</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($pendingSales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $sale->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $sale->customer_name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->seller_name ?? '-' }}</td>
                            <td class="px-6 py-3">{{ $sale->product ? (($sale->product->category->name ?? '—') . ' – ' . $sale->product->name) : 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $sale->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->purchase_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3">{{ number_format($sale->selling_price ?? 0, 0) }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($sale->total_selling_value ?? 0, 0) }}</td>
                            <td class="px-6 py-3 text-green-600">{{ number_format($sale->profit ?? 0, 0) }}</td>
                            <td class="px-6 py-3">
                                <form action="{{ route('admin.stock.save-pending-sale', $sale->id) }}" method="POST" class="inline">
                                    @csrf
                                    <select name="payment_option_id" required onchange="this.form.submit()"
                                        class="text-sm rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                                        <option value="">Select Payment Option...</option>
                                        @foreach($paymentOptions as $option)
                                            <option value="{{ $option->id }}" {{ $sale->payment_option_id == $option->id ? 'selected' : '' }}>
                                                {{ $option->name }} ({{ ucfirst($option->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-3">
                                @if($sale->payment_option_id)
                                    <form action="{{ route('admin.stock.save-pending-sale', $sale->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="payment_option_id" value="{{ $sale->payment_option_id }}">
                                        <button type="submit" class="text-blue-600 hover:text-blue-900 font-medium">Save</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-xs">Select payment option</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-slate-500">
                                No pending sales. <a href="{{ route('admin.stock.create-agent-sale') }}" class="text-[#fa8900] hover:underline">Record a new sale</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
