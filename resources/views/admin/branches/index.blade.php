<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Branches</h1>
                <p class="mt-2 text-slate-600">Store or office locations for purchases and reporting.</p>
            </div>
            <a href="{{ route('admin.branches.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#fa8900] text-white font-medium rounded-md hover:bg-[#e67d00] transition-colors">
                Add Branch
            </a>
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 p-4 bg-red-50 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        <x-admin-page-dashboard label="Summary" class="mt-8">
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

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Purchases</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($branches as $branch)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">{{ $branch->name }}</td>
                            <td class="px-6 py-3">{{ $branch->purchases_count }}</td>
                            <td class="px-6 py-3 text-right flex gap-2 justify-end">
                                <a href="{{ route('admin.branches.edit', $branch) }}" class="text-[#fa8900] hover:underline">Edit</a>
                                <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this branch?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">No branches yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
