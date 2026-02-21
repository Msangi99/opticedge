<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Agents</h1>
                <p class="mt-2 text-slate-600">Manage agents and assign products for them to sell.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.agents.create') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Add agent</a>
                <a href="{{ route('admin.agents.assign-products') }}"
                    class="rounded-lg bg-[#fa8900] px-4 py-2 text-sm font-medium text-white hover:bg-[#e87b00]">Assign products</a>
            </div>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <div class="mt-8 rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($agents as $agent)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">{{ $agent->name }}</td>
                            <td class="px-6 py-3">{{ $agent->email }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $agent->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800' }}">{{ ucfirst($agent->status ?? 'N/A') }}</span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.agents.show', $agent) }}" class="text-[#fa8900] hover:underline">View & assign</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No agents yet. <a href="{{ route('admin.agents.create') }}" class="text-[#fa8900] hover:underline">Add an agent</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
