<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page w-full max-w-none">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <p class="admin-prod-eyebrow">Management</p>
                <h1 class="admin-prod-title">Customer needs</h1>
                <p class="admin-prod-subtitle">Category and model requests submitted by agents from the app (Sell → Needed).</p>
            </div>
        </div>

        <div class="admin-clay-panel overflow-hidden w-full">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Needed</h2>
                <p class="admin-prod-form-hint">Full list of submitted needs.</p>
            </div>
            <div class="w-full overflow-x-auto p-4 sm:p-6">
                <table class="w-full min-w-[640px] text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3 pr-4">Submitted</th>
                            <th class="py-3 pr-4">Agent</th>
                            <th class="py-3 pr-4">Category</th>
                            <th class="py-3">Model</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customerNeeds as $n)
                            <tr>
                                <td class="py-3 pr-4 whitespace-nowrap align-top">{{ $n->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="py-3 pr-4 align-top break-words">{{ $n->agent?->name ?? '—' }}</td>
                                <td class="py-3 pr-4 align-top break-words">{{ $n->category?->name ?? '—' }}</td>
                                <td class="py-3 align-top break-words">{{ $n->product?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-500">No needs submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
