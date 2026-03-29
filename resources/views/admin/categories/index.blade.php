<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Catalog</p>
                <h1 class="admin-prod-title">Categories</h1>
                <p class="admin-prod-subtitle">Group products, cover images, and stock rollups.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="admin-prod-btn-primary shrink-0">
                Add category
            </a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th admin-prod-th--image">Image</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--index">ID</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--desc">Name</th>
                            <th scope="col" class="admin-prod-th">Products</th>
                            <th scope="col" class="admin-prod-th">Total stock</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->image)
                                        <div class="admin-prod-thumb admin-prod-thumb--tile">
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="">
                                        </div>
                                    @else
                                        <div class="admin-prod-thumb admin-prod-thumb--tile">
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-slate-500 font-mono text-xs">{{ $category->id }}</td>
                                <td>
                                    <div class="font-semibold text-[#232f3e]">{{ $category->name }}</div>
                                    @if($category->products->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs font-normal">
                                            @foreach($category->products as $product)
                                                <span class="inline-flex items-center gap-1">
                                                    <a href="{{ route('admin.products.imei', $product) }}"
                                                        class="admin-prod-link">{{ $product->name }}</a>
                                                    <span class="text-slate-500">({{ number_format($product->stock_quantity ?? 0) }})</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="admin-prod-count-pill admin-prod-count-pill--neutral">
                                        {{ $category->products_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-prod-count-pill admin-prod-count-pill--info">
                                        {{ number_format($category->products_sum_stock_quantity ?? 0) }}
                                    </span>
                                </td>
                                <td class="admin-prod-cell-actions">
                                    <div class="admin-prod-actions">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                            class="admin-prod-link">Edit</a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="admin-prod-link admin-prod-link--danger bg-transparent border-0 cursor-pointer p-0 font-inherit">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-500 py-10">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
