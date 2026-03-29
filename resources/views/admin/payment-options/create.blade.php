<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add Channel</h1>
                    <p class="mt-2 text-slate-600">Create a new payment channel (Mobile, Bank, or Cash).</p>
                </div>
                <a href="{{ route('admin.payment-options.index') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="admin-clay-panel p-6">
                <form action="{{ route('admin.payment-options.store') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                            <select name="type" id="type" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                                <option value="">Select Type...</option>
                                <option value="mobile" {{ old('type') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>Bank</option>
                                <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('type')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                placeholder="e.g. M-Pesa, Tigo Pesa, CRDB Bank, Cash..."
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                            @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="px-4 py-2 bg-[#fa8900] text-white font-medium rounded-md hover:bg-[#e67d00] transition-colors">
                            Create Channel
                        </button>
                        <a href="{{ route('admin.payment-options.index') }}"
                            class="px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-md hover:bg-slate-200 transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
