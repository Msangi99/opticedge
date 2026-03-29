<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Users</p>
                <h1 class="admin-prod-title">Customers & accounts</h1>
                <p class="admin-prod-subtitle">Storefront customers, dealers, and other roles in one list.</p>
            </div>
            <div class="admin-prod-filter-row shrink-0" role="tablist" aria-label="Filter by role">
                <a href="{{ route('admin.customers.index') }}"
                    class="admin-prod-filter-tab {{ !request('role') ? 'admin-prod-filter-tab--active' : '' }}"
                    @if(!request('role')) aria-current="page" @endif>
                    All
                </a>
                <a href="{{ route('admin.customers.index', ['role' => 'dealer']) }}"
                    class="admin-prod-filter-tab {{ request('role') == 'dealer' ? 'admin-prod-filter-tab--active' : '' }}"
                    @if(request('role') == 'dealer') aria-current="page" @endif>
                    Dealers
                </a>
                <a href="{{ route('admin.customers.index', ['role' => 'customer']) }}"
                    class="admin-prod-filter-tab {{ request('role') == 'customer' ? 'admin-prod-filter-tab--active' : '' }}"
                    @if(request('role') == 'customer') aria-current="page" @endif>
                    Customers
                </a>
            </div>
        </div>

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Name</th>
                            <th scope="col" class="admin-prod-th">Email</th>
                            <th scope="col" class="admin-prod-th">Role</th>
                            <th scope="col" class="admin-prod-th">Status</th>
                            <th scope="col" class="admin-prod-th">Joined</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="admin-prod-avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        <span class="font-semibold text-[#232f3e]">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-slate-600">{{ $user->email }}</td>
                                <td>
                                    @php
                                        $role = $user->role ?? 'customer';
                                        $roleClass =
                                            $role === 'admin'
                                                ? 'admin-prod-role-pill--admin'
                                                : ($role === 'dealer'
                                                    ? 'admin-prod-role-pill--dealer'
                                                    : ($role === 'agent'
                                                        ? 'admin-prod-role-pill--agent'
                                                        : 'admin-prod-role-pill--customer'));
                                    @endphp
                                    <span class="admin-prod-role-pill {{ $roleClass }}">{{ $role }}</span>
                                </td>
                                <td>
                                    @php
                                        $isActive = ($user->status ?? 'active') === 'active';
                                    @endphp
                                    <span
                                        class="admin-prod-user-status {{ $isActive ? 'admin-prod-user-status--active' : 'admin-prod-user-status--inactive' }}">
                                        {{ ucfirst($user->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="font-variant-numeric text-slate-600 text-sm">
                                    {{ $user->created_at->format('M j, Y') }}
                                </td>
                                <td class="admin-prod-cell-actions">
                                    <span class="admin-prod-muted" title="User editing is not available from this screen">—</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-500 py-10">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
                <div class="admin-prod-pagination">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
