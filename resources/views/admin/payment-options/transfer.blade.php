<x-admin-layout>
    @include('admin.partials.catalog-styles')
    
    <style>
        .helper-text {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.375rem;
        }
    </style>

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Payments</p>
                <h1 class="admin-prod-title">Transfer money</h1>
                <p class="admin-prod-subtitle">Transfer money between payment channels.</p>
            </div>
            <a href="{{ route('admin.payment-options.index') }}" class="admin-prod-back shrink-0">Back to channels</a>
        </div>

        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Transfer details</h2>
            </div>
            <form method="POST" action="{{ route('admin.payment-transfer.store') }}" class="admin-prod-form-body space-y-6" id="transfer-form">
                @csrf

                <div>
                    <label for="from_channel_id" class="admin-prod-label">From channel <span class="text-red-500">*</span></label>
                    <select id="from_channel_id" name="from_channel_id" required class="admin-prod-select" onchange="updateFromBalance()">
                        <option value="">Select channel to transfer from</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}" data-balance="{{ $channel->balance ?? 0 }}">
                                {{ $channel->name }} ({{ number_format($channel->balance ?? 0, 0) }} TZS)
                            </option>
                        @endforeach
                    </select>
                    @error('from_channel_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text">Available balance: <span id="from-balance">0</span> TZS</p>
                </div>

                <div>
                    <label for="to_channel_id" class="admin-prod-label">To channel <span class="text-red-500">*</span></label>
                    <select id="to_channel_id" name="to_channel_id" required class="admin-prod-select">
                        <option value="">Select channel to transfer to</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                    @error('to_channel_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="admin-prod-label">Amount <span class="text-red-500">*</span></label>
                    <input id="amount" type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01" class="admin-prod-input" oninput="calculateTransfer()">
                    @error('amount')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text" id="amount-error" style="color: #ef4444; display: none;"></p>
                </div>

                <div>
                    <label for="description" class="admin-prod-label">Description (optional)</label>
                    <textarea id="description" name="description" rows="3" class="admin-prod-input" placeholder="e.g., Daily cash reconciliation, Bank deposit, etc.">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text">Add a note for your records</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-600">Transfer amount:</p>
                            <p class="text-2xl font-bold text-slate-900" id="transfer-display">0.00 TZS</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600">Remaining balance:</p>
                            <p class="text-2xl font-bold text-slate-900" id="remaining-display">0.00 TZS</p>
                        </div>
                    </div>
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.payment-options.index') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8" id="submit-btn" onclick="return validateTransfer()">Confirm transfer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateFromBalance() {
            const select = document.getElementById('from_channel_id');
            const option = select.options[select.selectedIndex];
            const balance = parseFloat(option.getAttribute('data-balance')) || 0;
            document.getElementById('from-balance').textContent = balance.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0});
            calculateTransfer();
        }

        function calculateTransfer() {
            const fromSelect = document.getElementById('from_channel_id');
            const fromOption = fromSelect.options[fromSelect.selectedIndex];
            const fromBalance = parseFloat(fromOption.getAttribute('data-balance')) || 0;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const remaining = fromBalance - amount;
            
            document.getElementById('transfer-display').textContent = amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TZS';
            document.getElementById('remaining-display').textContent = remaining.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TZS';
            
            const amountError = document.getElementById('amount-error');
            if (amount > fromBalance) {
                amountError.textContent = '❌ Insufficient balance (max: ' + fromBalance.toFixed(2) + ' TZS)';
                amountError.style.display = 'block';
            } else if (amount <= 0) {
                amountError.textContent = '❌ Amount must be greater than 0';
                amountError.style.display = 'block';
            } else {
                amountError.style.display = 'none';
            }
            
            validateSubmit();
        }

        function validateSubmit() {
            const fromId = document.getElementById('from_channel_id').value;
            const toId = document.getElementById('to_channel_id').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const fromSelect = document.getElementById('from_channel_id');
            const fromOption = fromSelect.options[fromSelect.selectedIndex];
            const fromBalance = parseFloat(fromOption.getAttribute('data-balance')) || 0;
            
            const submitBtn = document.getElementById('submit-btn');
            if (fromId && toId && amount > 0 && fromId !== toId && amount <= fromBalance) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        function validateTransfer() {
            const fromId = document.getElementById('from_channel_id').value;
            const toId = document.getElementById('to_channel_id').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const fromSelect = document.getElementById('from_channel_id');
            const toSelect = document.getElementById('to_channel_id');
            const fromOption = fromSelect.options[fromSelect.selectedIndex];
            const toOption = toSelect.options[toSelect.selectedIndex];
            const fromBalance = parseFloat(fromOption.getAttribute('data-balance')) || 0;
            
            if (!fromId) {
                alert('❌ Please select source channel');
                return false;
            }
            if (!toId) {
                alert('❌ Please select destination channel');
                return false;
            }
            if (fromId === toId) {
                alert('❌ Source and destination must be different channels');
                return false;
            }
            if (amount <= 0) {
                alert('❌ Amount must be greater than 0');
                return false;
            }
            if (amount > fromBalance) {
                alert('❌ Insufficient balance. Available: ' + fromBalance.toFixed(2) + ' TZS');
                return false;
            }
            
            const fromName = fromOption.text.split('(')[0].trim();
            const toName = toOption.text.split('(')[0].trim();
            return confirm('✓ Confirm transfer?\n\nFrom: ' + fromName + '\nTo: ' + toName + '\nAmount: ' + amount.toFixed(2) + ' TZS');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateFromBalance();
            validateSubmit();
        });
    </script>
</x-admin-layout>
