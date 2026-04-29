<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page" x-data="{ editingVendor: null }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Management</p>
                <h1 class="admin-prod-title">Vendors</h1>
                <p class="admin-prod-subtitle">Manage distributors / vendors for purchases.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-4 xl:col-span-4">
                <div class="admin-clay-panel admin-prod-form-shell overflow-hidden h-full">
                    <div class="admin-prod-form-head">
                        <h2 class="admin-prod-form-title m-0">Add vendor</h2>
                        <p class="admin-prod-form-hint mt-1.5 max-w-md">
                            Create a vendor with contact and office details so it appears in purchase forms.
                        </p>
                    </div>
                    <form action="{{ route('admin.vendors.store') }}" method="POST" class="admin-prod-form-body space-y-5">
                        @csrf
                        <div>
                            <label for="name" class="admin-prod-label">Vendor name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="admin-prod-input" placeholder="e.g. ABC Distributors Ltd" autocomplete="organization">
                            @error('name')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-4">
                            <div class="sm:col-span-1">
                                <label for="phone" class="admin-prod-label">Phone</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                       class="admin-prod-input" placeholder="+255 7xx xxx xxx" autocomplete="tel">
                                @error('phone')
                                    <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-1">
                                <label for="email" class="admin-prod-label">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="admin-prod-input" placeholder="vendor@example.com" autocomplete="email">
                                @error('email')
                                    <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="office_name" class="admin-prod-label">Office name</label>
                            <input type="text" name="office_name" id="office_name" value="{{ old('office_name') }}"
                                   class="admin-prod-input" placeholder="e.g. Kariakoo branch">
                            @error('office_name')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="admin-prod-label">Location / address</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                   class="admin-prod-input" placeholder="City, street, building">
                            @error('location')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-xs text-slate-500 leading-relaxed rounded-xl border border-slate-200/80 bg-white/60 px-3.5 py-2.5">
                            Tip: a clear vendor name and office label help admins pick the right supplier in purchase forms.
                        </p>

                        <div class="admin-prod-form-footer !mt-0">
                            <button type="submit" class="admin-prod-btn-primary px-8 min-w-[10rem]">
                                Save vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8 xl:col-span-8">
                <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
                    <div class="admin-prod-form-head flex flex-wrap items-center justify-between gap-2">
                        <h2 class="admin-prod-form-title m-0">Vendor list</h2>
                        <span class="admin-prod-count-pill admin-prod-count-pill--neutral">
                            {{ $vendors->count() }} vendor{{ $vendors->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <div class="admin-prod-form-body !pt-4">
                    @if($vendors->isEmpty())
                        <p class="text-sm text-slate-500 py-4">No vendors added yet.</p>
                    @else
                        <div class="admin-prod-table-wrap">
                        <table class="min-w-full text-sm">
                            <thead>
                            <tr>
                                <th class="admin-prod-th">Name</th>
                                <th class="admin-prod-th">Phone</th>
                                <th class="admin-prod-th">Email</th>
                                <th class="admin-prod-th">Office</th>
                                <th class="admin-prod-th">Location</th>
                                <th class="admin-prod-th admin-prod-th--end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td class="font-semibold text-slate-800">
                                        {{ $vendor->name }}
                                    </td>
                                    <td>{{ $vendor->phone ?? '—' }}</td>
                                    <td>{{ $vendor->email ?? '—' }}</td>
                                    <td>{{ $vendor->office_name ?? '—' }}</td>
                                    <td>{{ $vendor->location ?? '—' }}</td>
                                    <td class="admin-prod-cell-actions">
                                        <span class="admin-prod-actions">
                                            <button type="button"
                                                    class="admin-prod-link text-xs"
                                                    @click="editingVendor = {{ $vendor->toJson() }}">
                                                Edit
                                            </button>
                                            <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Delete this vendor? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="admin-prod-link admin-prod-link--danger text-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit vendor modal -->
        <div x-show="editingVendor" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-3 py-4 sm:px-4 sm:py-6 bg-black/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="editingVendor = null">
            <div
                class="flex max-h-[calc(100vh-2rem)] w-full max-w-3xl flex-col overflow-hidden rounded-3xl border border-white/80 bg-gradient-to-br from-white/95 via-slate-50/95 to-slate-100/90 shadow-[18px_22px_45px_rgba(15,23,42,0.32),-6px_-8px_24px_rgba(255,255,255,0.95)] sm:max-h-[calc(100vh-3rem)]">
                <div class="admin-dash-section-head flex items-start justify-between border-b border-slate-200/70 pb-4">
                    <div class="pr-4">
                        <h3 class="admin-dash-section-title text-xl">Edit vendor details</h3>
                        <p class="admin-dash-section-desc mt-1">Update supplier details used in purchases.</p>
                        <p class="mt-2 inline-flex items-center rounded-full border border-slate-200/80 bg-white/80 px-2.5 py-1 text-xs font-semibold text-slate-600"
                           x-text="editingVendor ? editingVendor.name : ''"></p>
                    </div>
                    <button type="button" class="ml-4 rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-800"
                            @click="editingVendor = null">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="admin-dash-body flex-1 overflow-y-auto !pt-5">
                    <form x-bind:action="editingVendor ? '{{ url('admin/vendors') }}/' + editingVendor.id : '#'"
                          method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div class="rounded-2xl border border-slate-200/70 bg-white/75 p-4 sm:p-5">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-1.5">
                                    <label for="edit_vendor_name" class="admin-prod-label">Vendor name</label>
                                    <input id="edit_vendor_name" type="text" name="name" class="admin-prod-input"
                                           x-model="editingVendor.name" placeholder="e.g. ABC Distributors Ltd" required>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <label for="edit_vendor_phone" class="admin-prod-label">Phone</label>
                                        <input id="edit_vendor_phone" type="text" name="phone" class="admin-prod-input"
                                               x-model="editingVendor.phone" placeholder="+255 7xx xxx xxx">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label for="edit_vendor_email" class="admin-prod-label">Email</label>
                                        <input id="edit_vendor_email" type="email" name="email" class="admin-prod-input"
                                               x-model="editingVendor.email" placeholder="vendor@example.com">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label for="edit_vendor_office_name" class="admin-prod-label">Office name</label>
                                    <input id="edit_vendor_office_name" type="text" name="office_name" class="admin-prod-input"
                                           x-model="editingVendor.office_name" placeholder="e.g. Kariakoo branch">
                                </div>
                                <div class="space-y-1.5">
                                    <label for="edit_vendor_location" class="admin-prod-label">Location / address</label>
                                    <input id="edit_vendor_location" type="text" name="location" class="admin-prod-input"
                                           x-model="editingVendor.location" placeholder="City, street, building">
                                </div>
                            </div>
                        </div>
                        <div class="admin-prod-form-footer sticky bottom-0 -mx-1 !mt-2 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-white/90 px-1 pt-4 pb-1 backdrop-blur-sm">
                            <p class="text-xs text-slate-500">Changes apply immediately to future purchases.</p>
                            <button type="button" class="admin-prod-btn-ghost px-5"
                                    @click="editingVendor = null">
                                Cancel
                            </button>
                            <button type="submit" class="admin-prod-btn-primary px-8 min-w-[9.5rem]">
                                Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

