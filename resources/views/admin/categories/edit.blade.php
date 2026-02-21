<x-admin-layout>
    <div class="py-12 px-8 max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Edit Category</h1>
            <p class="text-slate-500 text-sm mt-1">Update the name of your category.</p>
        </div>

        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-8 rounded-lg shadow-sm border border-slate-200">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Category Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $category->name) }}"
                        class="w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-[#fa8900] focus:border-transparent outline-none transition-all"
                        placeholder="e.g. Electronics, Home & Kitchen">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-slate-700 mb-1">Cover Image</label>
                    @if($category->image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                class="w-24 h-24 object-cover rounded-lg border border-slate-200">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-[#fa8900] focus:border-transparent outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Leave empty to keep the current image. Max server limit:
                        {{ ini_get('upload_max_filesize') }}.</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.categories.index') }}"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-[#fa8900] text-white rounded-md hover:bg-orange-600 transition-colors shadow-sm font-medium">
                        Update Category
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>