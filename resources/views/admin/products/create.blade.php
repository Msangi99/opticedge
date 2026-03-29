<x-admin-layout>
    <div class="py-12 px-8 max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add product</h1>
                <p class="mt-2 text-slate-500">Choose a category, enter the model name, and add specifications.</p>
            </div>
            <a href="{{ route('admin.products.index') }}"
                class="flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm font-medium shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to products
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Product details</h2>
                <p class="text-xs text-slate-500 mt-1">Brand defaults to Samsung. Price and stock can be set when editing.</p>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                    <select name="category_id" id="category_id" required
                        class="block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent sm:text-sm p-3 font-medium cursor-pointer">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Product model</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent sm:text-sm p-3 font-medium placeholder:text-slate-400"
                        placeholder="e.g. Galaxy S24 Ultra 512GB">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description / specifications</label>
                    <textarea name="description" id="description" rows="8"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent sm:text-sm p-3 placeholder:text-slate-400 resize-y"
                        placeholder="Features, specs, condition notes…">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a href="{{ route('admin.products.index') }}"
                        class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg font-semibold text-sm transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-2.5 bg-[#fa8900] hover:bg-orange-600 text-white rounded-lg shadow-sm font-bold text-sm transition-colors">
                        Add product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
