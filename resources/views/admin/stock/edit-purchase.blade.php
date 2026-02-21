<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Edit Purchase</h1>
                    <p class="mt-2 text-slate-600">Update purchase details.</p>
                </div>
                <a href="{{ route('admin.stock.purchases') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.stock.update-purchase', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date -->
                        <div class="col-span-1">
                            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Date of Purchase</label>
                            <input type="date" name="date" id="date" value="{{ old('date', $purchase->date) }}" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                        </div>

                        <!-- Distributor -->
                        <div class="col-span-1">
                            <label for="distributor_name" class="block text-sm font-medium text-slate-700 mb-1">Distributor Name</label>
                            <input list="distributors" name="distributor_name" id="distributor_name" value="{{ old('distributor_name', $purchase->distributor_name) }}" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                        </div>

                        <!-- Category -->
                        <div class="col-span-1">
                            <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                            <select name="category_id" id="category_id" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $purchase->product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Model -->
                        <div class="col-span-1">
                            <label for="model" class="block text-sm font-medium text-slate-700 mb-1">Model (Product Name)</label>
                            <input type="text" name="model" id="model" value="{{ old('model', $purchase->product->name ?? '') }}" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                        </div>

                        <!-- Quantity -->
                        <div class="col-span-1">
                            <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $purchase->quantity) }}" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-1">
                            <label for="unit_price" class="block text-sm font-medium text-slate-700 mb-1">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" id="unit_price" value="{{ old('unit_price', $purchase->unit_price) }}" disabled class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-not-allowed">
                        </div>

                        <!-- Total Value (Read Only) -->
                        <div class="col-span-2">
                            <label for="total_amount" class="block text-sm font-medium text-slate-700 mb-1">Total Purchase Value</label>
                            <input type="text" id="total_amount" readonly class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm cursor-not-allowed font-bold text-gray-700" value="{{ number_format($purchase->quantity * $purchase->unit_price, 2) }}">
                        </div>

                        @php
                            $productImages = [];
                            if ($purchase->product) {
                                $productImages = is_string($purchase->product->images ?? null) ? json_decode($purchase->product->images, true) : ($purchase->product->images ?? []);
                                $productImages = is_array($productImages) ? $productImages : [];
                            }
                        @endphp
                        <!-- Product Images -->
                        <div class="col-span-2">
                            <label for="images" class="block text-sm font-medium text-slate-700 mb-1">Product Images</label>
                            @if(count($productImages) > 0)
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($productImages as $img)
                                        <img src="{{ asset('storage/' . $img) }}" alt="Product" class="w-16 h-16 object-cover rounded border border-slate-200">
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-[#fa8900] file:text-white file:font-medium hover:file:bg-[#e67d00]">
                            <p class="text-xs text-slate-500 mt-1">Upload new images to replace current ones. At least 3 required when uploading. Formats: JPG, PNG, GIF, WebP.</p>
                            @error('images')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            @error('images.*')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Payment Details</h3>
                        </div>

                        <!-- Paid Date -->
                        <div class="col-span-1">
                            <label for="paid_date" class="block text-sm font-medium text-slate-700 mb-1">Paid Date</label>
                            <input type="date" name="paid_date" id="paid_date" value="{{ old('paid_date', $purchase->paid_date) }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Paid Amount -->
                        <div class="col-span-1">
                            <label for="paid_amount" class="block text-sm font-medium text-slate-700 mb-1">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $purchase->paid_amount) }}" min="0" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('paid_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Pending Amount (read-only, decreases when paid amount increases) -->
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pending Amount</label>
                            @php $purchaseTotal = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price); @endphp
                            <input type="text" id="pending_amount" readonly class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm cursor-not-allowed font-medium text-gray-700" value="{{ number_format(max(0, $purchaseTotal - $purchase->paid_amount), 2) }}">
                            <p class="text-xs text-slate-500 mt-1">Auto: Total − Paid. Status updates automatically when you save.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.stock.purchases') }}" class="bg-gray-100 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">
                            Update Purchase
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var totalAmount = {{ $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price) }};
            var paidInput = document.getElementById('paid_amount');
            var pendingEl = document.getElementById('pending_amount');
            if (paidInput && pendingEl) {
                paidInput.addEventListener('input', function() {
                    var paid = parseFloat(this.value) || 0;
                    var pending = Math.max(0, totalAmount - paid);
                    pendingEl.value = pending.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                });
            }
        })();
    </script>
</x-admin-layout>
