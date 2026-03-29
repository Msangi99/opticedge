<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Payments</p>
                <h1 class="admin-prod-title">Add channel</h1>
                <p class="admin-prod-subtitle">Mobile, bank, or cash.</p>
            </div>
            <a href="{{ route('admin.payment-options.index') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Channel details</h2>
            </div>
            <form action="{{ route('admin.payment-options.store') }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf

                <div>
                    <label for="type" class="admin-prod-label">Type</label>
                    <select name="type" id="type" required class="admin-prod-select">
                        <option value="">Select type…</option>
                        <option value="mobile" @selected(old('type') === 'mobile')>Mobile</option>
                        <option value="bank" @selected(old('type') === 'bank')>Bank</option>
                        <option value="cash" @selected(old('type') === 'cash')>Cash</option>
                    </select>
                    @error('type')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="admin-prod-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="e.g. M-Pesa, CRDB, Cash drawer" class="admin-prod-input">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.payment-options.index') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Create channel</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
