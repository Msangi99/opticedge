<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Shop Records</h1>
                <p class="mt-2 text-slate-600">Track shop opening stock, sales, and transfers.</p>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Opening Stock</th>
                        <th class="px-6 py-3">Sales</th>
                        <th class="px-6 py-3">Transfer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($shopRecords as $record)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $record->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $record->product->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $record->opening_stock }}</td>
                            <td class="px-6 py-3">{{ $record->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ $record->transfer_quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">No shop records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
