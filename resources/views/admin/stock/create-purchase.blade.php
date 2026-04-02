<x-admin-layout>
    @include('admin.partials.catalog-styles')
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
    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Inventory</p>
                <h1 class="admin-prod-title">Add purchase</h1>
                <p class="admin-prod-subtitle">Record a new stock purchase.</p>
            </div>
            <a href="{{ route('admin.stock.purchases') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden admin-prod-select2-wrap">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Purchase details</h2>
                <p class="admin-prod-form-hint">Invoice, branch, and pricing.</p>
            </div>
            <form action="{{ route('admin.stock.store-purchase') }}" method="POST" enctype="multipart/form-data" class="admin-prod-form-body">
                    @csrf
                    @if($fromStock)
                        <input type="hidden" name="stock_id" value="{{ $fromStock->id }}">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($fromStock)
                            <!-- Stock name (from stock – read-only) -->
                            <div class="col-span-2">
                                <label class="admin-prod-label">Stock</label>
                                <div class="admin-prod-readonly-box font-medium">{{ $fromStock->name }}</div>
                                <p class="text-xs text-slate-500 mt-1">Category and model from products in this stock (as added in the app). Quantity = stock limit.</p>
                            </div>
                        @endif

                        <!-- Date -->
                        <div class="col-span-1">
                            <label for="date" class="admin-prod-label">Date of Purchase</label>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="admin-prod-input">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Distributor -->
                        <div class="col-span-1">
                            <label for="distributor_name" class="admin-prod-label">Distributor Name</label>
                            <select name="distributor_name" id="distributor_name" class="admin-prod-select">
                                <option value="">{{ __('Select vendor…') }}</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->name }}" {{ old('distributor_name') === $vendor->name ? 'selected' : '' }}>
                                        {{ $vendor->name }}@if($vendor->office_name) — {{ $vendor->office_name }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('distributor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Branch -->
                        <div class="col-span-2">
                            <label for="branch_id" class="admin-prod-label">Branch</label>
                            <select name="branch_id" id="branch_id" class="admin-prod-select">
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
                                <label class="admin-prod-label">Category</label>
                                <div class="admin-prod-readonly-box">{{ $fromStock->purchase_category_name ?? '–' }}</div>
                                <input type="hidden" name="category_id" value="{{ $fromStock->purchase_category_id }}">
                                @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="admin-prod-label">Model (product name)</label>
                                <div class="admin-prod-readonly-box">{{ $fromStock->purchase_model }}</div>
                                <input type="hidden" name="model" value="{{ $fromStock->purchase_model }}">
                                @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="col-span-2">
                                <label for="product_id" class="admin-prod-label">Model (product name)</label>
                                <select name="product_id" id="product_id" required class="admin-prod-select">
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
                            <label for="quantity" class="admin-prod-label">Quantity</label>
                            @if($fromStock)
                                <div class="admin-prod-readonly-box">{{ $fromStock->purchase_quantity }}</div>
                                <input type="hidden" name="quantity" id="quantity" value="{{ $fromStock->purchase_quantity }}">
                            @else
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="1" class="admin-prod-input" oninput="calculateTotal()">
                            @endif
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-1">
                            <label for="unit_price" class="admin-prod-label">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" required min="0" class="admin-prod-input" oninput="calculateTotal()">
                            @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sell Price -->
                        <div class="col-span-1">
                            <label for="sell_price" class="admin-prod-label">Sell Price</label>
                            <input type="number" step="0.01" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" min="0" placeholder="Optional" class="admin-prod-input">
                            @error('sell_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Total Value (Read Only) -->
                        <div class="col-span-2">
                            <label for="total_amount" class="admin-prod-label">Total Purchase Value</label>
                            <input type="text" id="total_amount" readonly class="admin-prod-input font-bold cursor-not-allowed">
                        </div>

                    </div>

                    <div class="admin-prod-form-footer !mt-6">
                        <button type="submit" class="admin-prod-btn-primary px-8">Save purchase</button>
                    </div>
                </form>
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

            document.addEventListener('DOMContentLoaded', function() {
                calculateTotal();

                @if(!$fromStock)
                if (window.jQuery && jQuery.fn.select2) {
                    var $productSel = jQuery('#product_id');
                    $productSel.select2({
                        placeholder: 'Search category-model…',
                        width: '100%',
                        allowClear: false
                    });
                    var oldPid = @json(old('product_id'));
                    if (oldPid) {
                        $productSel.val(String(oldPid)).trigger('change');
                    }
                    var $vendorSel = jQuery('#distributor_name');
                    $vendorSel.select2({
                        placeholder: 'Select vendor…',
                        width: '100%',
                        allowClear: true
                    });
                }
                @endif
            });
        </script>
    @endpush
</x-admin-layout>
