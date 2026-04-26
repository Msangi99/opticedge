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

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table class="min-w-[720px]">
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
                                    <div class="flex flex-col items-end gap-2 min-w-[260px]">
                                        <details class="w-full">
                                            <summary class="cursor-pointer text-xs font-semibold text-slate-600 hover:text-[#fa8900] list-none text-right">
                                                Reset password
                                            </summary>
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                                                class="mt-2 flex flex-wrap items-center justify-end gap-2">
                                                @csrf
                                                <input type="password" name="password" required minlength="8"
                                                    placeholder="New password" class="admin-prod-input w-36 py-1.5 text-sm">
                                                <input type="password" name="password_confirmation" required minlength="8"
                                                    placeholder="Confirm" class="admin-prod-input w-32 py-1.5 text-sm">
                                                <button type="submit" class="admin-prod-link whitespace-nowrap text-sm">Save</button>
                                            </form>
                                        </details>
                                    </div>
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
