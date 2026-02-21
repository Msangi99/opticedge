<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Edit Distribution Sale</h1>
                    <p class="mt-2 text-slate-600">Update paid amount. Pending (balance) updates automatically.</p>
                </div>
                <a href="{{ route('admin.stock.distribution') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.stock.update-distribution', $sale->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4 mb-6 text-sm text-slate-600">
                        <p><span class="font-medium text-slate-700">Dealer:</span> {{ $sale->dealer_name ?? $sale->dealer?->name ?? 'N/A' }}</p>
                        <p><span class="font-medium text-slate-700">Product:</span> {{ $sale->product ? ($sale->product->name) : 'N/A' }}</p>
                        <p><span class="font-medium text-slate-700">Total Selling Value:</span> {{ number_format($sale->total_selling_value ?? 0, 0) }} TZS</p>
                        <p><span class="font-medium text-slate-700">Current Balance (Pending):</span> <strong>{{ number_format($sale->balance ?? 0, 0) }} TZS</strong></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-slate-700 mb-1">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $sale->paid_amount) }}" min="0" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('paid_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="collection_date" class="block text-sm font-medium text-slate-700 mb-1">Collection Date</label>
                            <input type="date" name="collection_date" id="collection_date" value="{{ old('collection_date', $sale->collection_date) }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('collection_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pending (balance) = Total selling value − Paid amount. It will update when you save.</p>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.stock.distribution') }}" class="bg-gray-100 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
