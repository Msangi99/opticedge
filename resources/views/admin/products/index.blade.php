<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Stock Management (Samsung Phones)</h1>
            <a href="{{ route('admin.products.create') }}"
                class="px-4 py-2 bg-[#fa8900] text-white rounded-md hover:bg-orange-600 transition-colors shadow-sm font-medium">
                Add Stock
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 font-medium text-slate-900">
                    <tr>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Brand</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Stock Quantity</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden">
                                    @if(!empty($product->images) && count($product->images) > 0)
                                        <img src="{{ Storage::url($product->images[0]) }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                            <td class="px-6 py-3">{{ $product->brand }}</td>
                            <td class="px-6 py-3">${{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $stockClass = 'bg-red-100 text-red-700';
                                    if ($product->stock_quantity > 20) {
                                        $stockClass = 'bg-green-100 text-green-700';
                                    } elseif ($product->stock_quantity > 5) {
                                        $stockClass = 'bg-orange-100 text-orange-700';
                                    }
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $stockClass }}">
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="text-brand-orange hover:text-orange-700 font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No stock items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>