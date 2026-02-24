<x-account-layout>
    <div class="mb-6">
        <a href="{{ route('agent.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900">&larr; Back to dashboard</a>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">Record sale</h2>
        <p class="mt-1 text-slate-600">{{ $assignment->product->category->name ?? '—' }} – {{ $assignment->product->name }}</p>
    </div>

    <div class="max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('agent.record-sale') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

            <div>
                <x-input-label for="customer_name" :value="__('Customer name')" />
                <x-text-input id="customer_name" class="mt-1 block w-full" type="text" name="customer_name" :value="old('customer_name')" required />
                <x-input-error :messages="$errors->get('customer_name')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="quantity_sold" :value="__('Quantity')" />
                <input type="hidden" name="quantity_sold" value="1" />
                <x-text-input id="quantity_sold" class="mt-1 block w-full bg-slate-100 cursor-not-allowed" type="number" value="1" min="1" max="1" readonly disabled />
                <p class="mt-1 text-xs text-slate-500">Fixed at 1.</p>
            </div>

            <div>
                <x-input-label for="selling_price" :value="__('Sell price (per unit)')" />
                <x-text-input id="selling_price" class="mt-1 block w-full" type="number" step="0.01" name="selling_price" :value="old('selling_price')" min="0" required />
                <x-input-error :messages="$errors->get('selling_price')" class="mt-1" />
            </div>

            <div class="flex gap-3 pt-2">
                <x-primary-button type="submit">Record sale</x-primary-button>
                <a href="{{ route('agent.dashboard') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>
</x-account-layout>
