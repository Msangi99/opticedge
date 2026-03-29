<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Finance</p>
                <h1 class="admin-prod-title">Edit expense</h1>
                <p class="admin-prod-subtitle">Update activity, amount, or channel.</p>
            </div>
            <a href="{{ route('admin.expenses.index') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Expense</h2>
            </div>
            <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="activity" class="admin-prod-label">Activity</label>
                    <input type="text" name="activity" id="activity" value="{{ old('activity', $expense->activity) }}" required
                        placeholder="e.g. Office supplies…" class="admin-prod-input">
                    @error('activity')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="admin-prod-label">Amount (TZS)</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" required min="0" step="0.01" class="admin-prod-input">
                    @error('amount')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_option_id" class="admin-prod-label">Payment channel</label>
                    <select name="payment_option_id" id="payment_option_id" required class="admin-prod-select">
                        <option value="">Select channel…</option>
                        @foreach($paymentOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('payment_option_id', $expense->payment_option_id) == $option->id)>
                                {{ $option->name }} ({{ ucfirst($option->type) }}) — {{ number_format($option->balance ?? 0, 0) }} TZS
                            </option>
                        @endforeach
                    </select>
                    @error('payment_option_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="admin-prod-label">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $expense->date) }}" required class="admin-prod-input">
                    @error('date')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.expenses.index') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Update expense</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
