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

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Opening Balance (TZS)</th>
                        <th class="px-6 py-3">Current Balance (TZS)</th>
                        <th class="px-6 py-3">Change</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($paymentOptions as $option)
                        @php
                            $currentBalance = $option->balance ?? 0;
                            $openingBalance = $option->opening_balance ?? 0;
                            $difference = $currentBalance - $openingBalance;
                            $percentageChange = $openingBalance > 0 ? (($difference / $openingBalance) * 100) : 0;
                            $isIncrease = $difference > 0;
                            $isDecrease = $difference < 0;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">{{ $option->name }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $option->type === 'mobile' ? 'bg-blue-100 text-blue-800' : ($option->type === 'bank' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($option->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ number_format($openingBalance, 0) }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($currentBalance, 0) }}</td>
                            <td class="px-6 py-3">
                                @if($difference > 0)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        <span class="text-xs font-semibold text-green-600">{{ number_format(abs($difference), 0) }} ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                    </div>
                                @elseif($difference < 0)
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                        <span class="text-xs font-semibold text-red-600">{{ number_format(abs($difference), 0) }} ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right flex gap-2 justify-end">
                                <a href="{{ route('admin.payment-options.edit', $option) }}"
                                    class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                <form action="{{ route('admin.payment-options.destroy', $option) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this payment option?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No channels yet. <a href="{{ route('admin.payment-options.create') }}" class="text-[#fa8900] hover:underline">Add your first channel</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
