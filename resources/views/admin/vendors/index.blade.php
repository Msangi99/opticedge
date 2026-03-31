<x-admin-layout>
    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Management</p>
                <h1 class="admin-prod-title">Vendors</h1>
                <p class="admin-prod-subtitle">Manage distributors / vendors for purchases.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="admin-clay-panel admin-prod-form-shell p-6">
                    <h2 class="admin-prod-form-title mb-2">Add vendor</h2>
                    <p class="text-sm text-slate-500 mb-4">Create a new vendor that can be selected when recording purchases.</p>

                    <form action="{{ route('admin.vendors.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="admin-prod-label">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="admin-prod-input">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="admin-prod-label">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   class="admin-prod-input">
                            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="admin-prod-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="admin-prod-input">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="office_name" class="admin-prod-label">Office name</label>
                            <input type="text" name="office_name" id="office_name" value="{{ old('office_name') }}"
                                   class="admin-prod-input">
                            @error('office_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="location" class="admin-prod-label">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                   class="admin-prod-input">
                            @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="admin-prod-form-footer !mt-4">
                            <button type="submit" class="admin-prod-btn-primary px-6">Save vendor</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="admin-clay-panel admin-prod-form-shell p-6 overflow-x-auto">
                    <h2 class="admin-prod-form-title mb-4">Vendor list</h2>
                    @if($vendors->isEmpty())
                        <p class="text-sm text-slate-500">No vendors added yet.</p>
                    @else
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-2 pr-4 font-medium">Name</th>
                                <th class="py-2 pr-4 font-medium">Phone</th>
                                <th class="py-2 pr-4 font-medium">Email</th>
                                <th class="py-2 pr-4 font-medium">Office</th>
                                <th class="py-2 pr-4 font-medium">Location</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-800">{{ $vendor->name }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->phone ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->email ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->office_name ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->location ?? '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

