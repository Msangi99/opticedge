<x-admin-layout>
    @include('admin.partials.catalog-styles')
    
    <style>
        .field-error input, .field-error select, .field-error textarea {
            border-color: #ef4444 !important;
            background-color: #fee2e2;
        }
        .field-valid input {
            border-color: #10b981 !important;
        }
        .helper-text {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.375rem;
        }
    </style>

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Agents</p>
                <h1 class="admin-prod-title">Create agent sale</h1>
                <p class="admin-prod-subtitle">Record a manual agent sale.</p>
            </div>
            <a href="{{ route('admin.stock.agent-sales') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Sale details</h2>
            </div>
            <form method="POST" action="{{ route('admin.stock.store-agent-sale') }}" class="admin-prod-form-body space-y-6" id="sale-form">
                @csrf

                <div>
                    <label for="date" class="admin-prod-label">Date <span class="text-red-500">*</span></label>
                    <input id="date" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required autofocus max="{{ date('Y-m-d') }}" class="admin-prod-input">
                    @error('date')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text">Date cannot be in the future</p>
                </div>

                <div>
                    <label for="customer_name" class="admin-prod-label">Customer name <span class="text-red-500">*</span></label>
                    <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Customer name" required class="admin-prod-input">
                    @error('customer_name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seller_name" class="admin-prod-label">Seller name</label>
                    <input id="seller_name" type="text" name="seller_name" value="{{ old('seller_name', auth()->user()->name) }}" class="admin-prod-input" disabled>
                    <p class="helper-text">Auto-filled from your account</p>
                </div>

                <div>
                    <label for="product_id" class="admin-prod-label">Product (stock items) <span class="text-red-500">*</span></label>
                    <select id="product_id" name="product_id" required class="admin-prod-select">
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-available="0" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} ({{ $product->category->name ?? 'No category' }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text">Available: <span id="available-qty">—</span> units</p>
                </div>

                <div>
                    <label for="quantity_sold" class="admin-prod-label">Quantity sold <span class="text-red-500">*</span></label>
                    <input id="quantity_sold" type="number" name="quantity_sold" value="{{ old('quantity_sold') }}" required min="1" class="admin-prod-input">
                    @error('quantity_sold')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text" id="qty-error" style="color: #ef4444; display: none;"></p>
                </div>

                <div>
                    <label for="selling_price" class="admin-prod-label">Selling price per unit <span class="text-red-500">*</span></label>
                    <input id="selling_price" type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required min="0" class="admin-prod-input">
                    @error('selling_price')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text">Must be greater than 0</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-slate-900">Total amount:</span>
                        <span class="text-2xl font-bold text-slate-900">{{ number_format(0, 2) }} TZS</span>
                    </div>
                    <input type="hidden" id="total-amount" name="total_amount" value="0">
                    <p class="text-xs text-slate-600 mt-2">Calculated as: Quantity × Selling Price per Unit</p>
                </div>

                <div>
                    <label for="paid_amount" class="admin-prod-label">Initial payment (if any)</label>
                    <input id="paid_amount" type="number" step="0.01" name="paid_amount" value="{{ old('paid_amount', 0) }}" min="0" class="admin-prod-input">
                    @error('paid_amount')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text" id="payment-status"></p>
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.stock.agent-sales') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8" id="submit-btn" onclick="return validateForm()">Record sale</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const form = document.getElementById('sale-form');
            const customerName = document.getElementById('customer_name').value.trim();
            const productId = document.getElementById('product_id').value;
            const quantity = parseFloat(document.getElementById('quantity_sold').value) || 0;
            const price = parseFloat(document.getElementById('selling_price').value) || 0;
            
            if (!customerName) {
                alert('❌ Please enter customer name');
                document.getElementById('customer_name').focus();
                return false;
            }
            
            if (!productId) {
                alert('❌ Please select a product');
                document.getElementById('product_id').focus();
                return false;
            }
            
            if (quantity <= 0) {
                alert('❌ Quantity must be greater than 0');
                document.getElementById('quantity_sold').focus();
                return false;
            }
            
            if (price <= 0) {
                alert('❌ Selling price must be greater than 0');
                document.getElementById('selling_price').focus();
                return false;
            }
            
            return confirm('✓ Confirm record sale?\n\nCustomer: ' + customerName + '\nQuantity: ' + quantity + '\nPrice: ' + price.toFixed(2) + ' TZS');
        }

    <script>
        const quantityInput = document.getElementById('quantity_sold');
        const priceInput = document.getElementById('selling_price');
        const paidAmountInput = document.getElementById('paid_amount');
        const totalAmountDisplay = document.querySelector('.bg-slate-50 .text-2xl');
        const totalAmountInput = document.getElementById('total-amount');
        const submitBtn = document.getElementById('submit-btn');
        const paymentStatus = document.getElementById('payment-status');
        const qtyError = document.getElementById('qty-error');
        const productSelect = document.getElementById('product_id');

        function formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        function calculateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            
            totalAmountDisplay.textContent = formatCurrency(total) + ' TZS';
            totalAmountInput.value = total;
            
            updatePaymentStatus();
            validateSubmit();
        }

        function updatePaymentStatus() {
            const total = parseFloat(totalAmountInput.value) || 0;
            const paid = parseFloat(paidAmountInput.value) || 0;
            const remaining = total - paid;

            if (total === 0) {
                paymentStatus.textContent = '';
                paymentStatus.style.color = '#64748b';
            } else if (paid === 0) {
                paymentStatus.textContent = 'Unpaid';
                paymentStatus.style.color = '#ef4444';
            } else if (paid >= total) {
                paymentStatus.textContent = '✓ Fully paid';
                paymentStatus.style.color = '#10b981';
            } else {
                paymentStatus.textContent = 'Partially paid • Remaining: ' + formatCurrency(remaining) + ' TZS';
                paymentStatus.style.color = '#f59e0b';
            }
        }

        function validateSubmit() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = parseFloat(totalAmountInput.value) || 0;
            const customerName = document.getElementById('customer_name').value.trim();
            const productSelected = productSelect.value !== '';

            if (quantity > 0 && price > 0 && total > 0 && customerName && productSelected) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        quantityInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);
        paidAmountInput.addEventListener('change', updatePaymentStatus);
        document.getElementById('customer_name').addEventListener('input', validateSubmit);
        productSelect.addEventListener('change', validateSubmit);

        // Initial calculation and validation
        calculateTotal();
        validateSubmit();

        // Form submission
        document.getElementById('sale-form').addEventListener('submit', function(e) {
            if (!submitBtn.disabled) {
                // Form will submit normally
                return true;
            }
            e.preventDefault();
        });
    </script>
</x-admin-layout>
