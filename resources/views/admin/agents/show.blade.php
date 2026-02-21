<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.agents.index') }}" class="text-slate-600 hover:text-slate-900">&larr; Agents</a>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $agent->name }}</h1>
                <p class="mt-1 text-slate-600">{{ $agent->email }}</p>
            </div>
            <a href="{{ route('admin.agents.assign-products') }}?agent_id={{ $agent->id }}"
                class="rounded-lg bg-[#fa8900] px-4 py-2 text-sm font-medium text-white hover:bg-[#e87b00]">Assign products</a>
        </div>

        <div class="mt-8 rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <h3 class="border-b border-slate-100 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-900">Assigned products</h3>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Assigned</th>
                        <th class="px-6 py-3">Sold</th>
                        <th class="px-6 py-3">Remaining</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($assignments as $a)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $a->product->category->name ?? '—' }} – {{ $a->product->name }}</td>
                            <td class="px-6 py-3">{{ $a->quantity_assigned }}</td>
                            <td class="px-6 py-3">{{ $a->quantity_sold }}</td>
                            <td class="px-6 py-3">{{ $a->quantity_assigned - $a->quantity_sold }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No products assigned yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
