<x-admin-layout>
    <div class="py-12 px-8">
        <div class="mb-6">
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Category Management
            </a>
            <h1 class="text-2xl font-bold text-slate-900">{{ $product->name }}</h1>
            <p class="mt-1 text-slate-600">Products (IMEI numbers) for this model.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 font-medium text-slate-900">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">IMEI</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($product->productListItems as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-mono">{{ $item->imei_number ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $item->category?->name ?? '–' }}</td>
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
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No products (IMEI) for this model yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
