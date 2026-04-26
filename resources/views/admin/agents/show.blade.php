<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <a href="{{ route('admin.agents.index') }}" class="admin-prod-back inline-flex mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to agents
        </a>

        <div class="admin-prod-toolbar !mb-6">
            <div>
                <p class="admin-prod-eyebrow">Agent</p>
                <h1 class="admin-prod-title">{{ $agent->name }}</h1>
                <p class="admin-prod-subtitle">
                    {{ $agent->email }}
                    @if($agent->phone)
                        <span class="text-slate-400">·</span> {{ $agent->phone }}
                    @endif
                    @if($agent->branch)
                        <span class="text-slate-400">·</span> {{ $agent->branch->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.agents.assign-products') }}?agent_id={{ $agent->id }}"
                class="admin-prod-btn-primary shrink-0">Assign products</a>
        </div>

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Assigned products</h2>
                <p class="admin-prod-form-hint">Inventory allocated to this agent.</p>
            </div>
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Product</th>
                            <th scope="col" class="admin-prod-th">Assigned</th>
                            <th scope="col" class="admin-prod-th">Sold</th>
                            <th scope="col" class="admin-prod-th">Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td class="font-medium text-[#232f3e]">
                                    @if($a->product)
                                        {{ $a->product->category->name ?? '—' }} – {{ $a->product->name ?? 'Unknown model' }}
                                    @else
                                        <span class="text-amber-700">Unknown product (removed)</span>
                                    @endif
                                </td>
                                <td class="font-variant-numeric text-slate-600">{{ $a->quantity_assigned }}</td>
                                <td class="font-variant-numeric text-slate-600">{{ $a->quantity_sold }}</td>
                                <td class="font-variant-numeric text-slate-600">
                                    {{ $a->quantity_assigned - $a->quantity_sold }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-500 py-10">No products assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
