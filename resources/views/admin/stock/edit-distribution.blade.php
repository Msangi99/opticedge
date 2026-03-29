<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Dealers</p>
                <h1 class="admin-prod-title">Edit distribution sale</h1>
                <p class="admin-prod-subtitle">Update paid amount; pending updates automatically.</p>
            </div>
            <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Payment</h2>
            </div>
            <form action="{{ route('admin.stock.update-distribution', $sale->id) }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2 text-sm text-slate-600 rounded-lg border border-slate-200/80 bg-slate-50/50 p-4">
                    <p><span class="font-semibold text-slate-800">Dealer:</span> {{ $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-slate-800">Product:</span> {{ $sale->product ? ($sale->product->name) : 'N/A' }}</p>
                    <p><span class="font-semibold text-slate-800">Total selling value:</span> {{ number_format($sale->total_selling_value ?? 0, 0) }} TZS</p>
                    <p><span class="font-semibold text-slate-800">Current balance:</span> <strong class="text-amber-800">{{ number_format($sale->balance ?? 0, 0) }} TZS</strong></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="paid_amount" class="admin-prod-label">Paid amount</label>
                        <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $sale->paid_amount) }}" min="0" class="admin-prod-input">
                        @error('paid_amount')
                            <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="collection_date" class="admin-prod-label">Collection date</label>
                        <input type="date" name="collection_date" id="collection_date" value="{{ old('collection_date', $sale->collection_date) }}" class="admin-prod-input">
                        @error('collection_date')
                            <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-xs text-slate-500">Pending = total selling value − paid amount after save.</p>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
