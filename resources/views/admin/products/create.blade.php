<x-admin-layout>
    <div class="py-12 px-8 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Add New Stock</h1>
                <p class="mt-2 text-slate-500">Expand your inventory with the latest Samsung devices.</p>
            </div>
            <a href="{{ route('admin.products.index') }}"
                class="flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Inventory
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
            <!-- Form Header with Brand Badge -->
            <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Device Details</h2>
                <div
                    class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wider border border-blue-100">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                    </svg>
                    Brand: Samsung
                </div>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Category -->
                        <div>
                            <label for="category_id"
                                class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                            <select name="category_id" id="category_id" required
                                class="block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 font-medium cursor-pointer">
                                <option value="">Select a Category</option>
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

                        <!-- Model Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Model Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="pl-10 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 font-medium placeholder:text-slate-400"
                                    placeholder="e.g. Galaxy S24 Ultra 512GB">
                            </div>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Price & Stock Row -->
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-semibold text-slate-700 mb-2">Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-sm font-bold">$</span>
                                    </div>
                                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}"
                                        required
                                        class="pl-8 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 font-medium"
                                        placeholder="0.00">
                                </div>
                                @error('price')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Stock Quantity -->
                            <div>
                                <label for="stock_quantity"
                                    class="block text-sm font-semibold text-slate-700 mb-2">Quantity</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <input type="number" name="stock_quantity" id="stock_quantity"
                                        value="{{ old('stock_quantity') }}" required
                                        class="pl-10 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 font-medium"
                                        placeholder="0">
                                </div>
                                @error('stock_quantity')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Rating -->
                        <div>
                            <label for="rating" class="block text-sm font-semibold text-slate-700 mb-2">Rating
                                (0-5)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                </div>
                                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating"
                                    value="{{ old('rating', 5.0) }}" required
                                    class="pl-10 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 font-medium"
                                    placeholder="4.5">
                            </div>
                            @error('rating')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Images Upload -->
                        <div>
                            <label for="images" class="block text-sm font-semibold text-slate-700 mb-2">Product Images
                                (Max 5)</label>
                            <div class="relative">
                                <input type="file" name="images[]" id="images" multiple accept="image/*"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all rounded-lg border border-slate-200 bg-slate-50">
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Accepted formats: JPG, PNG, GIF, WebP.
                                <span class="text-xs text-orange-600 font-bold block mt-1">Note: Server limit is
                                    {{ ini_get('upload_max_filesize') }}. Please upload files smaller than this.</span>
                            </p>
                            @error('images')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                            @error('images.*')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column: Description -->
                    <div class="flex flex-col h-full">
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description /
                            Specs</label>
                        <textarea name="description" id="description"
                            class="flex-1 w-full rounded-lg border-slate-200 bg-slate-50 text-slate-900 focus:ring-2 focus:ring-[#fa8900] focus:border-transparent transition-shadow sm:text-sm p-3 placeholder:text-slate-400 resize-none"
                            placeholder="Enter device specifications, features, and condition details...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-400 mt-2 text-right">Markdown supported</p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.products.index') }}"
                        class="px-5 py-2.5 text-slate-600 hover:text-slate-900 bg-transparent hover:bg-slate-50 rounded-lg font-semibold text-sm transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-2.5 bg-[#fa8900] hover:bg-orange-600 text-white rounded-lg shadow-md hover:shadow-lg font-bold text-sm transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>