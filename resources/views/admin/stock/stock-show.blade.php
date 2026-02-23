<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('admin.stock.stocks') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Stocks
                </a>
                <h1 class="text-2xl font-bold text-slate-900">{{ $stock->name }}</h1>
                <p class="mt-1 text-slate-600">Devices in this stock (model and IMEI). Limit: {{ number_format($stock->stock_limit) }}.</p>
            </div>
            @if($atLimit)
                <a href="{{ route('admin.stock.create-purchase', ['from_stock' => $stock->id]) }}" class="px-4 py-2 bg-[#fa8900] text-white rounded-lg hover:bg-[#fa8900]/90 text-sm font-medium">
                    Add via Purchases
                </a>
            @endif
        </div>

        @if($atLimit)
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                Stock at limit. Add more inventory via <a href="{{ route('admin.stock.create-purchase', ['from_stock' => $stock->id]) }}" class="font-medium underline">Purchases</a>.
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Model</th>
                        <th class="px-6 py-3">IMEI</th>
                        <th class="px-6 py-3">Product / Category</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($stock->productListItems as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium">{{ $item->model ?? '–' }}</td>
                            <td class="px-6 py-3 font-mono">{{ $item->imei_number ?? '–' }}</td>
                            <td class="px-6 py-3">
                                {{ $item->product->name ?? '–' }}
                                @if($item->category)
                                    <span class="text-slate-400"> / {{ $item->category->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($item->sold_at)
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700">Sold</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No devices in this stock yet. Add products from the admin app.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
