<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Channels</h1>
                <p class="mt-2 text-slate-600">Manage payment channels (Mobile, Bank, and Cash).</p>
            </div>
            <a href="{{ route('admin.payment-options.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#fa8900] text-white font-medium rounded-md hover:bg-[#e67d00] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Channel
            </a>
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mt-8 admin-clay-panel overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Balance (TZS)</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($paymentOptions as $option)
                        <tr class="hover:bg-slate-50 {{ $option->is_hidden ? 'bg-slate-100/70' : '' }}">
                            <td class="px-6 py-3 font-medium">{{ $option->name }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $option->type === 'mobile' ? 'bg-blue-100 text-blue-800' : ($option->type === 'bank' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($option->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($option->is_hidden)
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-600">Hidden</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Visible</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-bold">{{ number_format($option->balance ?? 0, 0) }}</td>
                            <td class="px-6 py-3 text-right">
                                <form action="{{ route('admin.payment-options.toggle-visibility', $option) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-medium {{ $option->is_hidden ? 'text-emerald-600 hover:text-emerald-900' : 'text-slate-600 hover:text-slate-900' }}">
                                        {{ $option->is_hidden ? 'Show' : 'Hide' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No channels yet. <a href="{{ route('admin.payment-options.create') }}" class="text-[#fa8900] hover:underline">Add your first channel</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
