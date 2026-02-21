<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Expenses</h1>
                <p class="mt-2 text-slate-600">Track and manage business expenses.</p>
            </div>
            <a href="{{ route('admin.expenses.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#fa8900] text-white font-medium rounded-md hover:bg-[#e67d00] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Expense
            </a>
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Activity</th>
                        <th class="px-6 py-3">Amount (TZS)</th>
                        <th class="px-6 py-3">Cash Used</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $expense->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $expense->activity }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($expense->amount, 0) }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $expense->cash_used === 'system' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $expense->cash_used === 'system' ? 'System' : 'Cash' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right flex gap-2 justify-end">
                                <a href="{{ route('admin.expenses.edit', $expense) }}"
                                    class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No expenses yet. <a href="{{ route('admin.expenses.create') }}" class="text-[#fa8900] hover:underline">Add your first expense</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
