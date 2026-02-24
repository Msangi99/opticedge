<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Category Management</h1>
            <a href="{{ route('admin.categories.create') }}"
                class="px-4 py-2 bg-[#fa8900] text-white rounded-md hover:bg-orange-600 transition-colors shadow-sm font-medium">
                Add Category
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
                        <th class="px-6 py-3 w-12"></th>
                        <th class="px-6 py-3 w-20">Image</th>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Products Count</th>
                        <th class="px-6 py-3">Total Stock</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50 transition-colors" x-data="{ open: false }">
                            <td class="px-6 py-3 w-12">
                                @if($category->products->isNotEmpty())
                                    <button type="button" @click="open = !open" class="p-1 rounded hover:bg-slate-200 text-slate-600">
                                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                        class="w-12 h-12 object-cover rounded-md border border-slate-200">
                                @else
                                    <div
                                        class="w-12 h-12 bg-slate-100 rounded-md border border-slate-200 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 font-mono">{{ $category->id }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold">
                                    {{ $category->products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold">
                                    {{ number_format($category->products_sum_stock_quantity ?? 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right flex justify-end gap-3">
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                    class="text-brand-orange hover:text-orange-700 font-medium">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @if($category->products->isNotEmpty())
                            <tr x-show="open" x-cloak class="bg-slate-50/80">
                                <td colspan="7" class="px-6 py-3">
                                    <div class="pl-8 border-l-2 border-slate-200 ml-2">
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Model & Idadi</p>
                                        <table class="w-full max-w-xl text-sm">
                                            <thead>
                                                <tr class="text-slate-500 border-b border-slate-200">
                                                    <th class="text-left py-1">Model</th>
                                                    <th class="text-right py-1">Idadi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($category->products as $product)
                                                    <tr>
                                                        <td class="py-1.5 text-slate-700">
                                                            <a href="{{ route('admin.products.imei', $product) }}" class="text-[#fa8900] hover:underline font-medium">{{ $product->name }}</a>
                                                        </td>
                                                        <td class="py-1.5 text-right font-medium">{{ number_format($product->stock_quantity ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>