<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Organization</p>
                <h1 class="admin-prod-title">Edit branch</h1>
            </div>
            <a href="{{ route('admin.branches.index') }}" class="admin-prod-back shrink-0">Back to branches</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">{{ $branch->name }}</h2>
            </div>
            <form action="{{ route('admin.branches.update', $branch) }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="admin-prod-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $branch->name) }}" required
                        class="admin-prod-input">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.branches.index') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
