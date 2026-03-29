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
                <p class="mt-1 text-slate-600">Model, category and IMEI for this purchase. Click a row to expand full details per IMEI (assignment, credit, customer, agent).</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-2 py-3 w-10" aria-label="Expand"></th>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Model</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">IMEI</th>
                    </tr>
                </thead>
                @forelse($items as $index => $item)
                    <tbody x-data="{ open: false }" class="border-b border-slate-100 last:border-0">
                        <tr
                            class="hover:bg-slate-50 cursor-pointer"
                            @click="open = !open"
                            role="button"
                            tabindex="0"
                            @keydown.enter.prevent="open = !open"
                            @keydown.space.prevent="open = !open"
                        >
                            <td class="px-2 py-3 text-slate-400 select-none w-10">
                                <span x-text="open ? '▼' : '▶'" class="inline-block w-5 text-center text-xs"></span>
                            </td>
                            <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium">{{ $item->model ?? '–' }}</td>
                            <td class="px-6 py-3">{{ $item->category?->name ?? '–' }}</td>
                            <td class="px-6 py-3 font-mono">{{ $item->imei_number ?? '–' }}</td>
                        </tr>
                        <tr x-show="open" x-cloak class="!border-b border-slate-200">
                            <td colspan="5" class="p-0">
                                @include('admin.stock.partials.imei-full-info', ['item' => $item])
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No items (model / category / IMEI) for this purchase yet.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
    </div>
</x-admin-layout>
