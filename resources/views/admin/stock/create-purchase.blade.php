<x-admin-layout>
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--single {
                min-height: 42px;
                padding: 6px 8px;
                border-color: #cbd5e1;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 28px;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }
        </style>
    @endpush
    <div class="py-12 px-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add New Purchase</h1>
                    <p class="mt-2 text-slate-600">Record a new stock purchase.</p>
                </div>
                <a href="{{ route('admin.stock.purchases') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="admin-clay-panel p-6">
                <form action="{{ route('admin.stock.store-purchase') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($fromStock)
                        <input type="hidden" name="stock_id" value="{{ $fromStock->id }}">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($fromStock)
                            <!-- Stock name (from stock – read-only) -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 font-medium">{{ $fromStock->name }}</div>
                                <p class="text-xs text-slate-500 mt-1">Category and model from products in this stock (as added in the app). Quantity = stock limit.</p>
                            </div>
                        @endif

                        <!-- Date -->
                        <div class="col-span-1">
                            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Date of Purchase</label>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Distributor -->
                        <div class="col-span-1">
                            <label for="distributor_name" class="block text-sm font-medium text-slate-700 mb-1">Distributor Name</label>
                            <input list="distributors" name="distributor_name" id="distributor_name" value="{{ old('distributor_name') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Select or type new...">
                            <datalist id="distributors">
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor }}">
                                @endforeach
                            </datalist>
                            @error('distributor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Invoice Number -->
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Invoice Number</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Leave empty for auto (DistributorName-YYYY-MM-DD-HH-MM)">
                            <p id="invoice_preview" class="text-xs text-slate-500 mt-1" aria-live="polite"></p>
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Branch -->
                        <div class="col-span-2">
                            <label for="branch_id" class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                            <select name="branch_id" id="branch_id" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Optional —</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category + model: from stock (read-only), or product picker (Select2) -->
                        @if($fromStock)
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_category_name ?? '–' }}</div>
                                <input type="hidden" name="category_id" value="{{ $fromStock->purchase_category_id }}">
                                @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Model (product name)</label>
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_model }}</div>
                                <input type="hidden" name="model" value="{{ $fromStock->purchase_model }}">
                                @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="col-span-2">
                                <label for="product_id" class="block text-sm font-medium text-slate-700 mb-1">Model (product name)</label>
                                <select name="product_id" id="product_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Search or select…</option>
                                    @foreach($productsForSelect as $p)
                                        <option value="{{ $p->id }}" {{ (string) old('product_id') === (string) $p->id ? 'selected' : '' }}>
                                            {{ ($p->category?->name ?? '—') }}-{{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Options are listed as <span class="font-medium">category-model</span>. Search filters the list.</p>
                                @error('product_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Quantity: from stock limit (read-only) or editable -->
                        <div class="col-span-1">
                            <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                            @if($fromStock)
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_quantity }}</div>
                                <input type="hidden" name="quantity" id="quantity" value="{{ $fromStock->purchase_quantity }}">
                            @else
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="1" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" oninput="calculateTotal()">
                            @endif
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-1">
                            <label for="unit_price" class="block text-sm font-medium text-slate-700 mb-1">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" required min="0" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" oninput="calculateTotal()">
                            @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sell Price -->
                        <div class="col-span-1">
                            <label for="sell_price" class="block text-sm font-medium text-slate-700 mb-1">Sell Price</label>
                            <input type="number" step="0.01" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" min="0" placeholder="Optional" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('sell_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Total Value (Read Only) -->
                        <div class="col-span-2">
                            <label for="total_amount" class="block text-sm font-medium text-slate-700 mb-1">Total Purchase Value</label>
                            <input type="text" id="total_amount" readonly class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm cursor-not-allowed font-bold text-gray-700">
                        </div>

                        <!-- Product Images (for home page & product details) -->
                        <div class="col-span-2" x-data="{
                            selectedImages: [],
                            showGalleryModal: false,
                            showUpload: false,
                            galleryImages: @js($purchaseImages ?? []),
                            uploadedFiles: [],
                            toggleImage(imagePath) {
                                const index = this.selectedImages.indexOf(imagePath);
                                if (index > -1) {
                                    this.selectedImages.splice(index, 1);
                                } else {
                                    this.selectedImages.push(imagePath);
                                }
                            },
                            selectFirstThree() {
                                const unselected = this.galleryImages
                                    .map(img => img.image_path)
                                    .filter(path => !this.selectedImages.includes(path))
                                    .slice(0, 3);
                                this.selectedImages.push(...unselected);
                            },
                            isSelected(imagePath) {
                                return this.selectedImages.includes(imagePath);
                            },
                            openGallery() {
                                this.showGalleryModal = true;
                            },
                            closeGallery() {
                                this.showGalleryModal = false;
                            },
                            toggleUpload() {
                                this.showUpload = !this.showUpload;
                            },
                            getSelectedCount() {
                                return this.selectedImages.length;
                            },
                            getTotalCount() {
                                const fileInput = document.getElementById('images');
                                const fileCount = fileInput ? fileInput.files.length : 0;
                                return this.selectedImages.length + fileCount;
                            },
                            updateFileList() {
                                const fileInput = document.getElementById('images');
                                this.uploadedFiles = fileInput ? Array.from(fileInput.files) : [];
                            }
                        }" x-init="$watch('showUpload', value => { if(value) { setTimeout(() => updateFileList(), 100); } })">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Product Images (min 3)
                                <span class="text-xs font-normal text-slate-500 ml-2" x-text="'Total: ' + getTotalCount() + ' selected'"></span>
                            </label>
                            
                            <!-- Gallery Button -->
                            <div class="mb-4">
                                <button type="button" 
                                        @click="openGallery()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#fa8900] text-white rounded-lg hover:bg-[#e67d00] transition-colors text-sm font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Select from Purchase Gallery</span>
                                    <span x-show="getSelectedCount() > 0" 
                                          class="bg-white text-[#fa8900] rounded-full px-2 py-0.5 text-xs font-bold"
                                          x-text="getSelectedCount()"></span>
                                </button>
                                <p class="text-xs text-slate-500 mt-1">Click to browse and select images from existing purchases.</p>
                            </div>
                            
                            <!-- Hidden inputs for selected gallery images -->
                            <template x-for="(imagePath, index) in selectedImages" :key="index">
                                <input type="hidden" name="selected_images[]" :value="imagePath">
                            </template>
                            
                            <!-- Gallery Modal -->
                            <div x-show="showGalleryModal" 
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-50 overflow-y-auto"
                                 style="display: none;"
                                 @click.self="closeGallery()">
                                <!-- Modal Backdrop -->
                                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
                                
                                <!-- Modal Content -->
                                <div class="flex min-h-full items-center justify-center p-4">
                                    <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         @click.stop>
                                        <!-- Modal Header -->
                                        <div class="flex items-center justify-between p-6 border-b border-slate-200">
                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-900">Select Images from Purchase Gallery</h3>
                                                <p class="text-sm text-slate-500 mt-1">Click images to select. Selected: <span class="font-medium text-[#fa8900]" x-text="getSelectedCount()"></span></p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" 
                                                        @click="selectFirstThree()"
                                                        x-show="getSelectedCount() < 3"
                                                        class="px-3 py-1.5 text-sm bg-[#fa8900] text-white rounded-lg hover:bg-[#e67d00] transition-colors font-medium">
                                                    Select 3 Images
                                                </button>
                                                <button type="button" 
                                                        @click="closeGallery()"
                                                        class="text-slate-400 hover:text-slate-600 transition-colors">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal Body - Gallery -->
                                        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                                            <div x-show="galleryImages.length === 0" class="border border-slate-200 rounded-lg p-8 bg-slate-50 text-center">
                                                <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-slate-600 font-medium">No images found in purchase gallery</p>
                                                <p class="text-slate-500 text-sm mt-1">Please upload images from device instead.</p>
                                            </div>
                                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                                <template x-for="img in galleryImages" :key="img.id">
                                                    <div class="relative cursor-pointer group"
                                                         @click="toggleImage(img.image_path)"
                                                         :class="isSelected(img.image_path) ? 'ring-2 ring-[#fa8900] ring-offset-2' : ''">
                                                        <div class="aspect-square bg-slate-100 rounded-lg overflow-hidden border-2 transition-all"
                                                             :class="isSelected(img.image_path) ? 'border-[#fa8900]' : 'border-slate-200'">
                                                            <img :src="img.image_url"
                                                                 :alt="img.product_name"
                                                                 class="w-full h-full object-cover transition-all"
                                                                 :class="isSelected(img.image_path) ? 'opacity-100' : 'opacity-75 group-hover:opacity-100'">
                                                        </div>
                                                        <div x-show="isSelected(img.image_path)"
                                                             class="absolute top-2 right-2 bg-[#fa8900] text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold shadow-lg">
                                                            ✓
                                                        </div>
                                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg">
                                                            <p class="text-white text-xs font-medium truncate" x-text="img.product_name"></p>
                                                            <p class="text-white/80 text-xs truncate" x-text="img.purchase_name"></p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal Footer -->
                                        <div class="flex items-center justify-between p-6 border-t border-slate-200 bg-slate-50">
                                            <div class="text-sm text-slate-600">
                                                <span class="font-medium" x-text="getSelectedCount()"></span> image(s) selected
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="button" 
                                                        @click="closeGallery()"
                                                        class="px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                                                    Done
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Upload from Device Section -->
                            <div class="mb-2">
                                <button type="button" 
                                        @click="toggleUpload()"
                                        class="text-sm text-[#fa8900] hover:text-[#e67d00] font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span x-text="showUpload ? 'Hide Upload' : 'Upload from Device'"></span>
                                </button>
                            </div>
                            
                            <div x-show="showUpload" class="mb-4">
                                <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    @change="updateFileList()"
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-[#fa8900] file:text-white file:font-medium hover:file:bg-[#e67d00]">
                                <p class="text-xs text-slate-500 mt-1">Upload new images from your device. Formats: JPG, PNG, GIF, WebP. Max 5MB each.</p>
                                <div x-show="uploadedFiles.length > 0" class="mt-2 text-xs text-slate-600">
                                    <span x-text="'Uploaded: ' + uploadedFiles.length + ' file(s)'"></span>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <p class="text-xs" :class="getTotalCount() >= 3 ? 'text-green-600 font-medium' : 'text-slate-500'">
                                    <span x-text="getTotalCount() >= 3 ? '✓ ' : ''"></span>
                                    Select at least 3 images from gallery or upload from device. You can combine both methods.
                                    <span x-show="getTotalCount() < 3" class="text-red-500 font-medium" x-text="' (Need ' + (3 - getTotalCount()) + ' more)'"></span>
                                </p>
                            </div>
                            @error('images')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            @error('images.*')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">
                            Save Purchase
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            function calculateTotal() {
                const qty = parseFloat(document.getElementById('quantity')?.value) || 0;
                const price = parseFloat(document.getElementById('unit_price')?.value) || 0;
                const total = qty * price;
                const el = document.getElementById('total_amount');
                if (el) el.value = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            function slugifyDistributorName(s) {
                if (!s || !String(s).trim()) return 'UNKNOWN';
                s = String(s).trim().replace(/\s+/g, '-').replace(/[^a-zA-Z0-9\-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '');
                return s || 'UNKNOWN';
            }

            function invoicePreviewText() {
                const slug = slugifyDistributorName(document.getElementById('distributor_name')?.value || '');
                const d = document.getElementById('date')?.value || '';
                const el = document.getElementById('invoice_preview');
                if (!el) return;
                if (!d) {
                    el.textContent = '';
                    return;
                }
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const min = String(now.getMinutes()).padStart(2, '0');
                const timePart = h + '-' + min;
                el.textContent = document.getElementById('name')?.value?.trim()
                    ? ''
                    : ('Suggested if invoice left empty: ' + slug + '-' + d + '-' + timePart);
            }

            document.addEventListener('DOMContentLoaded', function() {
                calculateTotal();
                invoicePreviewText();
                ['distributor_name', 'date', 'name'].forEach(function(id) {
                    document.getElementById(id)?.addEventListener('input', invoicePreviewText);
                    document.getElementById(id)?.addEventListener('change', invoicePreviewText);
                });

                @if(!$fromStock)
                if (window.jQuery && jQuery.fn.select2) {
                    var $sel = jQuery('#product_id');
                    $sel.select2({
                        placeholder: 'Search category-model…',
                        width: '100%',
                        allowClear: false
                    });
                    var oldPid = @json(old('product_id'));
                    if (oldPid) {
                        $sel.val(String(oldPid)).trigger('change');
                    }
                }
                @endif
            });
        </script>
    @endpush
</x-admin-layout>
