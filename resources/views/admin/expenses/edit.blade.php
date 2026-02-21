<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Edit Expense</h1>
                    <p class="mt-2 text-slate-600">Update expense details.</p>
                </div>
                <a href="{{ route('admin.expenses.index') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.expenses.update', $expense) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label for="activity" class="block text-sm font-medium text-slate-700 mb-1">Activity</label>
                            <input type="text" name="activity" id="activity" value="{{ old('activity', $expense->activity) }}" required
                                placeholder="e.g. Office supplies, Transport..."
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                            @error('activity')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (TZS)</label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" required min="0" step="0.01"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                            @error('amount')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="cash_used" class="block text-sm font-medium text-slate-700 mb-1">Cash Used</label>
                            <select name="cash_used" id="cash_used" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                                @foreach(\App\Models\Expense::cashOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('cash_used', $expense->cash_used) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('cash_used')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="date" id="date" value="{{ old('date', $expense->date) }}" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                            @error('date')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="px-4 py-2 bg-[#fa8900] text-white font-medium rounded-md hover:bg-[#e67d00] transition-colors">
                            Update Expense
                        </button>
                        <a href="{{ route('admin.expenses.index') }}"
                            class="px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-md hover:bg-slate-200 transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
