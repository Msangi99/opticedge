<x-admin-layout>
    <div class="py-12 px-8 max-w-xl">
        <h1 class="text-2xl font-bold text-slate-900">Edit Branch</h1>

        <form action="{{ route('admin.branches.update', $branch) }}" method="POST" class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $branch->name) }}" required
                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#e67d00] font-medium">Update</button>
                <a href="{{ route('admin.branches.index') }}" class="text-slate-600 hover:text-slate-900 py-2">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
