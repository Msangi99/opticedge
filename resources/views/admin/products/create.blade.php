<x-admin-layout>
    @include('admin.products.partials.styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Catalog</p>
                <h1 class="admin-prod-title">Add product</h1>
                <p class="admin-prod-subtitle">Choose a category, enter the model name, and add specifications.</p>
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

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Product details</h2>
                <p class="admin-prod-form-hint">Brand defaults to Samsung. Price and stock can be set when editing.</p>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf

                <div>
                    <label for="category_id" class="admin-prod-label">Category</label>
                    <select name="category_id" id="category_id" required class="admin-prod-select cursor-pointer">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="admin-prod-label">Product model</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="admin-prod-input" placeholder="e.g. Galaxy S24 Ultra 512GB">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="admin-prod-label">Description / specifications</label>
                    <textarea name="description" id="description" rows="8" class="admin-prod-textarea"
                        placeholder="Features, specs, condition notes…">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer">
                    <a href="{{ route('admin.products.index') }}" class="admin-prod-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="admin-prod-btn-primary px-8">
                        Add product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
