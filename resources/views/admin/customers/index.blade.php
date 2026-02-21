<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">Customers & Users</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.customers.index') }}"
                class="px-4 py-2 rounded text-sm font-medium {{ !request('role') ? 'bg-[#fa8900] text-white' : 'bg-white text-slate-600 border border-slate-300' }}">All</a>
            <a href="{{ route('admin.customers.index', ['role' => 'dealer']) }}"
                class="px-4 py-2 rounded text-sm font-medium {{ request('role') == 'dealer' ? 'bg-[#fa8900] text-white' : 'bg-white text-slate-600 border border-slate-300' }}">Dealers</a>
            <a href="{{ route('admin.customers.index', ['role' => 'customer']) }}"
                class="px-4 py-2 rounded text-sm font-medium {{ request('role') == 'customer' ? 'bg-[#fa8900] text-white' : 'bg-white text-slate-600 border border-slate-300' }}">Customers</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold">
                    <th class="p-4">Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Joined At</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($customers as $user)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-medium text-slate-900 flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    {{ $user->name }}
                                </td>
                                <td class="p-4 text-slate-600">{{ $user->email }}</td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase
                                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' :
                    ($user->role === 'dealer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($user->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="p-4 text-right">
                                    <a href="#" class="text-slate-400 hover:text-slate-600">Edit</a>
                                </td>
                            </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>