<x-admin-layout>
    <div class="py-12 px-8">
        <a href="{{ route('admin.agents.index') }}" class="text-slate-600 hover:text-slate-900">&larr; Agents</a>
        <div class="mt-4">
            <h1 class="text-2xl font-bold text-slate-900">Assign products to agent</h1>
            <p class="mt-2 text-slate-600">Select an agent and product with quantity. Agent will see it in their dashboard and can record sales.</p>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <div class="mt-8 max-w-lg rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.agents.store-assignment') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-slate-700">Agent</label>
                    <select id="agent_id" name="agent_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]" required>
                        <option value="">Select agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ old('agent_id', request('agent_id')) == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->email }})</option>
                        @endforeach
                    </select>
                    @error('agent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="product_id" class="block text-sm font-medium text-slate-700">Product</label>
                    <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]" required>
                        <option value="">Select product</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->category->name ?? '—' }} – {{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="quantity_assigned" class="block text-sm font-medium text-slate-700">Quantity to assign</label>
                    <input type="number" id="quantity_assigned" name="quantity_assigned" value="{{ old('quantity_assigned', 1) }}" min="1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]">
                    @error('quantity_assigned') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-[#fa8900] px-4 py-2 text-sm font-medium text-white hover:bg-[#e87b00]">Assign</button>
                    <a href="{{ route('admin.agents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
