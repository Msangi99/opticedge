<x-admin-layout>
    @include('admin.products.partials.styles')

    <div class="admin-prod-page">
        <a href="{{ route('admin.categories.index') }}" class="admin-prod-back mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to category management
        </a>

        <div class="admin-prod-toolbar mb-6">
            <div>
                <p class="admin-prod-eyebrow">Serials</p>
                <h1 class="admin-prod-title">{{ $product->name }}</h1>
                <p class="admin-prod-subtitle">IMEI lines linked to this model.</p>
            </div>
        </div>

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th admin-prod-th--index">#</th>
                            <th scope="col" class="admin-prod-th">IMEI</th>
                            <th scope="col" class="admin-prod-th">Category</th>
                            <th scope="col" class="admin-prod-th">Status</th>
                            <th scope="col" class="admin-prod-th"><span class="sr-only">Details</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->productListItems as $index => $item)
                            <tr>
                                <td class="text-slate-400 font-medium">{{ $index + 1 }}</td>
                                <td class="font-mono text-[#232f3e] font-medium">
                                    <a href="{{ route('admin.stock.imei-item', $item) }}" class="hover:underline">{{ $item->imei_number ?? '–' }}</a>
                                </td>
                                <td>{{ $item->category?->name ?? '–' }}</td>
                                <td>
                                    @if($item->sold_at)
                                        <span class="admin-prod-status admin-prod-status--sold">Sold</span>
                                    @else
                                        <span class="admin-prod-status admin-prod-status--ok">Available</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.stock.imei-item', $item) }}" class="admin-prod-link text-sm font-medium">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-500 py-10">
                                    No products (IMEI) for this model yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
