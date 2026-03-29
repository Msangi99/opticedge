<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Organization</p>
                <h1 class="admin-prod-title">Branches</h1>
                <p class="admin-prod-subtitle">Store or office locations for purchases and reporting.</p>
            </div>
            <a href="{{ route('admin.branches.create') }}" class="admin-prod-btn-primary shrink-0">Add branch</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <x-admin-page-dashboard label="Summary" class="mt-2">
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Branches</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($branchDashboard['branches']) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Purchases linked</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($branchDashboard['linked_purchases']) }}</dd>
                </div>
            </dl>
        </x-admin-page-dashboard>

        <div class="mt-6 admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Name</th>
                            <th scope="col" class="admin-prod-th">Purchases</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td class="font-semibold text-[#232f3e]">{{ $branch->name }}</td>
                                <td class="font-variant-numeric text-slate-600">{{ $branch->purchases_count }}</td>
                                <td class="admin-prod-cell-actions">
                                    <div class="admin-prod-actions flex-wrap gap-x-3 gap-y-1">
                                        <a href="{{ route('admin.branches.edit', $branch) }}" class="admin-prod-link">Edit</a>
                                        <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-prod-btn-inline admin-prod-link--danger"
                                                onclick="return confirm('Delete this branch?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-slate-500 py-10">No branches yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
