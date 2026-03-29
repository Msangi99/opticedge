<x-admin-layout>
    @include('admin.products.partials.styles')

    <div class="admin-prod-page admin-prod-page--wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Inventory</p>
                <h1 class="admin-prod-title">Edit product</h1>
                <p class="admin-prod-subtitle">Update inventory and details for {{ $product->name }}.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="admin-prod-back shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to products
            </a>
        </div>

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <div class="admin-prod-form-head-row">
                    <div>
                        <h2 class="admin-prod-form-title">Device details</h2>
                        <p class="admin-prod-form-hint">Pricing, stock, images, and specifications.</p>
                    </div>
                    <div class="admin-prod-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 shrink-0">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                        </svg>
                        Brand: Samsung
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.products.update', $product->id) }}" method="POST"
                enctype="multipart/form-data" class="admin-prod-form-body">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label for="category_id" class="admin-prod-label">Category</label>
                            <select name="category_id" id="category_id" required class="admin-prod-select cursor-pointer">
                                <option value="">Select a Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="admin-prod-label">Model name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2-2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}"
                                    required
                                    class="admin-prod-input pl-10"
                                    placeholder="e.g. Galaxy S24 Ultra 512GB">
                            </div>
                            @error('name')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label for="price" class="admin-prod-label">Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-sm font-bold">$</span>
                                    </div>
                                    <input type="number" step="0.01" name="price" id="price"
                                        value="{{ old('price', $product->price) }}" required
                                        class="admin-prod-input pl-8" placeholder="0.00">
                                </div>
                                @error('price')
                                    <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="stock_quantity" class="admin-prod-label">Quantity</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <input type="number" name="stock_quantity" id="stock_quantity"
                                        value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                                        class="admin-prod-input pl-10" placeholder="0">
                                </div>
                                @error('stock_quantity')
                                    <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="rating" class="admin-prod-label">Rating (0–5)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                </div>
                                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating"
                                    value="{{ old('rating', $product->rating ?? 5.0) }}" required
                                    class="admin-prod-input pl-10" placeholder="4.5">
                            </div>
                            @error('rating')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="images" class="admin-prod-label">Product images (add new)</label>
                            <input type="file" name="images[]" id="images" multiple accept="image/*"
                                class="admin-prod-file">
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">Accepted: JPG, PNG, GIF, WebP.</p>
                            <p class="text-xs text-orange-700 font-semibold mt-1">Server limit:
                                {{ ini_get('upload_max_filesize') }} — use smaller files if uploads fail.</p>
                            @error('images')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                            @error('images.*')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror

                            @if(!empty($product->images))
                                <div class="mt-4 grid grid-cols-5 gap-2 admin-prod-img-grid">
                                    @foreach($product->images as $image)
                                        <div class="relative group">
                                            <img src="{{ Storage::url($image) }}" alt="Product image"
                                                class="h-20 w-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-500 mt-2">New uploads append to existing (max 5 total).</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col min-h-[16rem] lg:min-h-0">
                        <label for="description" class="admin-prod-label">Description / specs</label>
                        <textarea name="description" id="description" rows="12"
                            class="admin-prod-textarea flex-1 min-h-[12rem] lg:min-h-[20rem]"
                            placeholder="Specifications, features, condition…">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-500 mt-2 text-right">Markdown supported</p>
                    </div>
                </div>

                <div class="admin-prod-form-footer">
                    <a href="{{ route('admin.products.index') }}" class="admin-prod-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="admin-prod-btn-primary px-8 inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
