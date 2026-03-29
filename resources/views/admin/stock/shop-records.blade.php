<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar !mb-0">
            <div>
                <p class="admin-prod-eyebrow">Operations</p>
                <h1 class="admin-prod-title">Shop records</h1>
                <p class="admin-prod-subtitle">Opening stock, sales, and transfers.</p>
            </div>
        </div>

        <div class="mt-6 admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Date</th>
                            <th scope="col" class="admin-prod-th">Product</th>
                            <th scope="col" class="admin-prod-th">Opening stock</th>
                            <th scope="col" class="admin-prod-th">Sales</th>
                            <th scope="col" class="admin-prod-th">Transfer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shopRecords as $record)
                            <tr>
                                <td class="text-slate-600">{{ $record->date }}</td>
                                <td class="font-semibold text-[#232f3e]">{{ $record->product->name ?? 'N/A' }}</td>
                                <td class="font-variant-numeric text-slate-600">{{ $record->opening_stock }}</td>
                                <td class="font-variant-numeric text-slate-600">{{ $record->quantity_sold }}</td>
                                <td class="font-variant-numeric text-slate-600">{{ $record->transfer_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-500 py-10">No shop records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
