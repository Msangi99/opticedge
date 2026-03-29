<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Catalog</p>
                <h1 class="admin-prod-title">Add category</h1>
                <p class="admin-prod-subtitle">Create a category to group products in the storefront.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="admin-prod-back shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to categories
            </a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Category details</h2>
                <p class="admin-prod-form-hint">Name and optional cover image.</p>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
                class="admin-prod-form-body space-y-6">
                @csrf

                <div>
                    <label for="name" class="admin-prod-label">Category name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="admin-prod-input" placeholder="e.g. Smartphones, Accessories">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="image" class="admin-prod-label">Cover image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="admin-prod-file">
                    <p class="text-xs text-slate-500 mt-2">Server limit: {{ ini_get('upload_max_filesize') }}.</p>
                    @error('image')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer">
                    <a href="{{ route('admin.categories.index') }}" class="admin-prod-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="admin-prod-btn-primary px-8">
                        Create category
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
