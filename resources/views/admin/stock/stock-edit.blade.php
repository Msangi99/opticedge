<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="{{ route('admin.stock.stocks') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Stocks
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900">Edit stock: {{ $stock->name }}</h1>
                    <p class="mt-1 text-slate-600">Set default category and model for when this stock has no products yet. When you add products in the app, "Add via Purchases" will use that category and model. Quantity always uses the stock limit.</p>
                </div>
            </div>

            @if(session('info'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.stock.stocks.update', $stock) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stock name</label>
                            <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $stock->name }}</div>
                            <p class="text-xs text-slate-500 mt-1">Name is set when the stock is created (e.g. in the app).</p>
                        </div>

                        <div>
                            <label for="default_category_id" class="block text-sm font-medium text-slate-700 mb-1">Default category</label>
                            <select name="default_category_id" id="default_category_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('default_category_id', $stock->default_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('default_category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="default_model" class="block text-sm font-medium text-slate-700 mb-1">Default model (product name)</label>
                            <input type="text" name="default_model" id="default_model" value="{{ old('default_model', $stock->default_model) }}" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Galaxy A15">
                            @error('default_model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('admin.stock.stocks') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Cancel</a>
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 font-medium">
                            Save defaults
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
