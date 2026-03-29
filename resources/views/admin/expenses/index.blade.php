<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Finance</p>
                <h1 class="admin-prod-title">Expenses</h1>
                <p class="admin-prod-subtitle">Business spend by channel.</p>
            </div>
            <a href="{{ route('admin.expenses.create') }}" class="admin-prod-btn-primary inline-flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add expense
            </a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Date</th>
                            <th scope="col" class="admin-prod-th">Activity</th>
                            <th scope="col" class="admin-prod-th">Amount (TZS)</th>
                            <th scope="col" class="admin-prod-th">Channel</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="text-slate-600">{{ $expense->date }}</td>
                                <td class="font-semibold text-[#232f3e]">{{ $expense->activity }}</td>
                                <td class="font-bold font-variant-numeric">{{ number_format($expense->amount, 0) }}</td>
                                <td>
                                    @if($expense->paymentOption)
                                        <span class="admin-prod-tag {{ $expense->paymentOption->type === 'mobile' ? 'border-blue-200 text-blue-800 bg-blue-50/80' : 'admin-prod-tag--accent' }}">
                                            {{ $expense->paymentOption->name }} ({{ ucfirst($expense->paymentOption->type) }})
                                        </span>
                                    @else
                                        <span class="admin-prod-tag">N/A</span>
                                    @endif
                                </td>
                                <td class="admin-prod-cell-actions">
                                    <div class="admin-prod-actions flex-wrap gap-x-3 gap-y-1 justify-end">
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="admin-prod-link">Edit</a>
                                        <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-prod-btn-inline admin-prod-link--danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-500 py-10">
                                    No expenses yet.
                                    <a href="{{ route('admin.expenses.create') }}" class="admin-prod-link">Add one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
