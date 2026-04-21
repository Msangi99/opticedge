<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Sales team</p>
                <h1 class="admin-prod-title">Agents</h1>
                <p class="admin-prod-subtitle">Manage agents and assign products for them to sell.</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('admin.agents.create') }}" class="admin-prod-btn-ghost">Add agent</a>
                <a href="{{ route('admin.agents.assign-products') }}" class="admin-prod-btn-primary">Assign products</a>
            </div>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Name</th>
                            <th scope="col" class="admin-prod-th">Email</th>
                            <th scope="col" class="admin-prod-th">Phone</th>
                            <th scope="col" class="admin-prod-th">Ability</th>
                            <th scope="col" class="admin-prod-th">Branch</th>
                            <th scope="col" class="admin-prod-th">Status</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="font-semibold text-[#232f3e]">{{ $agent->name }}</td>
                                <td class="text-slate-600">{{ $agent->email }}</td>
                                <td class="text-slate-600">{{ $agent->phone ?? '—' }}</td>
                                <td class="text-slate-600">{{ ($agent->ability ?? 'fullaccess') === 'view' ? 'View only' : 'Full access' }}</td>
                                <td class="text-slate-600">{{ $agent->branch?->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $active = ($agent->status ?? '') === 'active';
                                    @endphp
                                    <span
                                        class="admin-prod-user-status {{ $active ? 'admin-prod-user-status--active' : 'admin-prod-user-status--inactive' }}">
                                        {{ ucfirst($agent->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="admin-prod-cell-actions">
                                    <a href="{{ route('admin.agents.show', $agent) }}" class="admin-prod-link">View &amp;
                                        assign</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-500 py-10">
                                    No agents yet.
                                    <a href="{{ route('admin.agents.create') }}" class="admin-prod-link">Add an agent</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
