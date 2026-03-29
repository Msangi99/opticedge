<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Catalog</p>
                <h1 class="admin-prod-title">Edit category</h1>
                <p class="admin-prod-subtitle">Update name or replace the cover image.</p>
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

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">{{ $category->name }}</h2>
                <p class="admin-prod-form-hint">Leave image empty to keep the current file.</p>
            </div>

            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST"
                enctype="multipart/form-data" class="admin-prod-form-body space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="admin-prod-label">Category name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $category->name) }}"
                        class="admin-prod-input" placeholder="e.g. Electronics, Home & Kitchen">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="image" class="admin-prod-label">Cover image</label>
                    @if($category->image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                class="admin-prod-preview-sm">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*" class="admin-prod-file">
                    <p class="text-xs text-slate-500 mt-2">Leave empty to keep the current image. Max server:
                        {{ ini_get('upload_max_filesize') }}.</p>
                    @error('image')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer">
                    <a href="{{ route('admin.categories.index') }}" class="admin-prod-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="admin-prod-btn-primary px-8">
                        Update category
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
