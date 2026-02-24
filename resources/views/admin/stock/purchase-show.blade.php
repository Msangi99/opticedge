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
                <h1 class="text-2xl font-bold text-slate-900">{{ $purchase->name ?? 'Purchase #' . $purchase->id }}</h1>
                <p class="mt-1 text-slate-600">Model, category and IMEI for this purchase.</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Model</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">IMEI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($items as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium">{{ $item->model ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $item->category?->name ?? '–' }}</td>
                            <td class="px-6 py-3 font-mono">{{ $item->imei_number ?? '–' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No items (model / category / IMEI) for this purchase yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
