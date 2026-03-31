<x-admin-layout>
    <div class="admin-prod-page admin-prod-form-wide" x-data="{ editingVendor: null }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Management</p>
                <h1 class="admin-prod-title">Vendors</h1>
                <p class="admin-prod-subtitle">Manage distributors / vendors for purchases.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="admin-clay-panel admin-prod-form-shell p-6 space-y-5">
                    <div>
                        <h2 class="admin-prod-form-title mb-1">Add vendor</h2>
                        <p class="text-xs text-slate-500">Create a vendor record with contact and office details so it
                            appears in purchase forms.</p>
                    </div>

                    <form action="{{ route('admin.vendors.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label for="name" class="admin-prod-label">Vendor name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="admin-prod-input" placeholder="Eg. ABC Distributors Ltd">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="phone" class="admin-prod-label">Phone</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                           class="admin-prod-input" placeholder="+255 7xx xxx xxx">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="email" class="admin-prod-label">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                           class="admin-prod-input" placeholder="vendor@example.com">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="office_name" class="admin-prod-label">Office name</label>
                                <input type="text" name="office_name" id="office_name" value="{{ old('office_name') }}"
                                       class="admin-prod-input" placeholder="Eg. Kariakoo Branch">
                                @error('office_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="location" class="admin-prod-label">Location / address</label>
                                <input type="text" name="location" id="location" value="{{ old('location') }}"
                                       class="admin-prod-input" placeholder="City, street, building">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="admin-prod-form-footer !mt-4 flex items-center justify-between gap-3">
                            <p class="text-[11px] text-slate-400 leading-snug">
                                Tip: Use a clear vendor name and office so admins can quickly recognise it in purchase forms.
                            </p>
                            <button type="submit" class="admin-prod-btn-primary px-6">
                                Save vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="admin-clay-panel admin-prod-form-shell p-6 overflow-x-auto">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="admin-prod-form-title">Vendor list</h2>
                        <p class="text-xs text-slate-500">
                            {{ $vendors->count() }} vendor{{ $vendors->count() === 1 ? '' : 's' }}
                        </p>
                    </div>

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
                                <th class="py-2 pl-4 pr-2 font-medium text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-800">
                                        {{ $vendor->name }}
                                    </td>
                                    <td class="py-2 pr-4">{{ $vendor->phone ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->email ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->office_name ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $vendor->location ?? '—' }}</td>
                                    <td class="py-2 pl-4 pr-2 text-right space-x-2 whitespace-nowrap">
                                        <button type="button"
                                                class="admin-prod-btn-inline text-xs text-[#232f3e] hover:underline"
                                                @click="editingVendor = {{ $vendor->toJson() }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Delete this vendor? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="admin-prod-btn-inline text-xs text-red-600 hover:underline">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit vendor modal -->
        <div x-show="editingVendor" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="editingVendor = null">
            <div
                class="w-full max-w-lg rounded-3xl border border-white/80 bg-gradient-to-br from-white/98 via-slate-50/95 to-slate-100/90 shadow-[18px_22px_45px_rgba(15,23,42,0.32),-6px_-8px_24px_rgba(255,255,255,0.95)]">
                <div class="admin-dash-section-head flex items-start justify-between">
                    <div>
                        <h3 class="admin-dash-section-title">Edit vendor</h3>
                        <p class="admin-dash-section-desc" x-text="editingVendor ? editingVendor.name : ''"></p>
                    </div>
                    <button type="button" class="ml-4 rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-white/80"
                            @click="editingVendor = null">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="admin-dash-body">
                    <form x-bind:action="editingVendor ? '{{ url('admin/vendors') }}/' + editingVendor.id : '#'"
                          method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="admin-prod-label">Vendor name</label>
                                <input type="text" name="name" class="admin-prod-input"
                                       x-model="editingVendor.name">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="admin-prod-label">Phone</label>
                                    <input type="text" name="phone" class="admin-prod-input"
                                           x-model="editingVendor.phone">
                                </div>
                                <div>
                                    <label class="admin-prod-label">Email</label>
                                    <input type="email" name="email" class="admin-prod-input"
                                           x-model="editingVendor.email">
                                </div>
                            </div>
                            <div>
                                <label class="admin-prod-label">Office name</label>
                                <input type="text" name="office_name" class="admin-prod-input"
                                       x-model="editingVendor.office_name">
                            </div>
                            <div>
                                <label class="admin-prod-label">Location / address</label>
                                <input type="text" name="location" class="admin-prod-input"
                                       x-model="editingVendor.location">
                            </div>
                        </div>
                        <div class="admin-prod-form-footer !mt-4 flex items-center justify-end gap-3">
                            <button type="button" class="admin-prod-btn-secondary px-4"
                                    @click="editingVendor = null">
                                Cancel
                            </button>
                            <button type="submit" class="admin-prod-btn-primary px-6">
                                Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

