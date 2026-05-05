<x-admin-layout>
    @include('admin.partials.catalog-styles')

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .field-error input, .field-error select, .field-error textarea {
                border-color: #ef4444 !important;
                background-color: #fee2e2;
            }
            .helper-text {
                font-size: 0.75rem;
                color: #64748b;
                margin-top: 0.375rem;
            }
            .no-number-spin::-webkit-outer-spin-button,
            .no-number-spin::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            .no-number-spin {
                -moz-appearance: textfield;
                appearance: textfield;
            }
            .admin-prod-select2-wrap .select2-container--default .select2-selection--single {
                min-height: 42px;
                padding: 6px 8px;
                border-color: #cbd5e1;
            }
            .admin-prod-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 28px;
            }
            .admin-prod-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px;
            }
            .dist-line-table th {
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #64748b;
            }
        </style>
    @endpush

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Dealers</p>
                <h1 class="admin-prod-title">Create distribution sale</h1>
                <p class="admin-prod-subtitle">Add one or more phone models. Search a model, then set quantity and unit price for each line.</p>
            </div>
            <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Sale details</h2>
            </div>
            <form method="POST" action="{{ route('admin.stock.store-distribution') }}" class="admin-prod-form-body space-y-6" id="dist-form">
                @csrf
                <input type="hidden" name="seller_name" value="{{ old('seller_name', auth()->user()->name) }}">

                <div>
                    <label for="date" class="admin-prod-label">Date <span class="text-red-500">*</span></label>
                    <input id="date" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required autofocus max="{{ date('Y-m-d') }}" class="admin-prod-input">
                    @error('date')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dealer_id" class="admin-prod-label">Dealer <span class="text-red-500">*</span></label>
                    <select id="dealer_id" name="dealer_id" required class="admin-prod-select">
                        <option value="">Select dealer</option>
                        @foreach($dealers as $dealer)
                            <option value="{{ $dealer->id }}" @selected(old('dealer_id') == $dealer->id)>
                                {{ $dealer->name }}@if($dealer->business_name) — {{ $dealer->business_name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('dealer_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-clay-panel border border-slate-200/80 !shadow-none admin-prod-select2-wrap">
                    <div class="p-4 border-b border-slate-200/60">
                        <label for="product_picker" class="admin-prod-label !mb-2">Add model to this sale <span class="text-red-500">*</span></label>
                        <select id="product_picker" class="w-full" data-placeholder="Search category / model…">
                            <option value=""></option>
                            @foreach($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    data-stock="{{ (int) $product->stock_quantity }}"
                                    data-suggest="{{ (float) ($product->price ?? 0) }}"
                                >
                                    {{ $product->category?->name ?? '—' }} — {{ $product->name }} (stock {{ (int) $product->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                        <p class="helper-text mt-2">Select a model below — a row appears with quantity and selling price. Repeat for multiple models.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm dist-line-table">
                            <thead class="bg-slate-50/90">
                                <tr>
                                    <th scope="col" class="text-left px-4 py-3 font-semibold">Model</th>
                                    <th scope="col" class="text-right px-3 py-3 font-semibold w-[7rem]">Qty</th>
                                    <th scope="col" class="text-right px-3 py-3 font-semibold w-[9rem]">Unit sell (TZS)</th>
                                    <th scope="col" class="text-right px-3 py-3 font-semibold w-[10rem]">Line total</th>
                                    <th scope="col" class="w-12 px-2 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="line-items-body"></tbody>
                        </table>
                        <p id="no-lines-hint" class="px-4 py-8 text-center text-slate-500 text-sm">No models added yet — use the search field above.</p>
                    </div>
                </div>

                @error('lines')
                    <p class="text-red-600 text-xs font-semibold">{{ $message }}</p>
                @enderror

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                    <div class="flex justify-between items-center gap-4 flex-wrap">
                        <span class="font-semibold text-slate-900">Grand total (all lines)</span>
                        <span id="dist-total-display" class="text-2xl font-bold text-slate-900">0.00 TZS</span>
                    </div>
                    <input type="hidden" id="total-amount" name="total_amount_meta" value="0">
                    <p class="text-xs text-slate-600 mt-2">Sum of each line: quantity × unit selling price.</p>
                </div>

                <div>
                    <label for="paid_amount" class="admin-prod-label">Paid amount <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input id="paid_amount" type="text" name="paid_amount" value="{{ old('paid_amount') }}" inputmode="decimal" autocomplete="off" placeholder="Split proportionally across lines" class="admin-prod-input">
                    @error('paid_amount')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="helper-text" id="payment-status">Optional — allocated across lines by share of total.</p>
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8" id="submit-btn">Record sale</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $productMeta = $products->keyBy('id')->map(function ($p) {
            return [
                'id' => $p->id,
                'label' => ($p->category?->name ?? '—') . ' — ' . $p->name,
                'stock' => (int) ($p->stock_quantity ?? 0),
                'suggest' => (float) ($p->price ?? 0),
            ];
        })->toArray();
    @endphp

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            const PRODUCT_META = @json($productMeta);

            const tbody = document.getElementById('line-items-body');
            const noLinesHint = document.getElementById('no-lines-hint');
            const totalDisplay = document.getElementById('dist-total-display');
            const totalHidden = document.getElementById('total-amount');
            const paidInput = document.getElementById('paid_amount');
            const paymentStatus = document.getElementById('payment-status');
            const form = document.getElementById('dist-form');
            const submitBtn = document.getElementById('submit-btn');

            function parseMoney(el) {
                if (!el || el.value === undefined || el.value === '') return 0;
                return parseFloat(String(el.value).replace(/,/g, '').trim()) || 0;
            }

            function formatCurrency(value) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value);
            }

            function lineTotalRow(tr) {
                const q = parseFloat(tr.querySelector('.line-qty')?.value) || 0;
                const p = parseMoney(tr.querySelector('.line-price'));
                return q * p;
            }

            function selectedProductIds() {
                return [...tbody.querySelectorAll('tr[data-product-id]')].map(tr => tr.getAttribute('data-product-id'));
            }

            function renumberLines() {
                const rows = tbody.querySelectorAll('tr[data-line-row]');
                rows.forEach((tr, idx) => {
                    tr.querySelector('.line-product-id').name = 'lines[' + idx + '][product_id]';
                    tr.querySelector('.line-qty').name = 'lines[' + idx + '][quantity_sold]';
                    tr.querySelector('.line-price').name = 'lines[' + idx + '][selling_price]';
                });
                noLinesHint.style.display = rows.length ? 'none' : 'block';
            }

            function recalcGrandTotal() {
                let sum = 0;
                tbody.querySelectorAll('tr[data-line-row]').forEach(tr => {
                    const lt = lineTotalRow(tr);
                    sum += lt;
                    const cell = tr.querySelector('.line-line-total');
                    if (cell) cell.textContent = formatCurrency(lt) + ' TZS';
                });
                totalDisplay.textContent = formatCurrency(sum) + ' TZS';
                totalHidden.value = sum;

                const paid = parseMoney(paidInput);
                if (sum <= 0) {
                    paymentStatus.textContent = 'Optional — allocated across lines by share of total.';
                    paymentStatus.style.color = '#64748b';
                } else if (paid <= 0) {
                    paymentStatus.textContent = 'No upfront payment — record later from Edit sale.';
                    paymentStatus.style.color = '#64748b';
                } else if (Math.abs(paid - sum) < 0.01) {
                    paymentStatus.textContent = '✓ Matches grand total (split across lines)';
                    paymentStatus.style.color = '#10b981';
                } else if (paid > sum * 1.01) {
                    paymentStatus.textContent = '⚠️ Paid exceeds grand total';
                    paymentStatus.style.color = '#ef4444';
                } else {
                    paymentStatus.textContent = 'Partial payment • Remaining ' + formatCurrency(sum - paid) + ' TZS (allocated proportionally)';
                    paymentStatus.style.color = '#f59e0b';
                }

                submitBtn.disabled = sum <= 0 || document.getElementById('dealer_id').value === '';
                submitBtn.classList.toggle('opacity-50', submitBtn.disabled);
                submitBtn.classList.toggle('cursor-not-allowed', submitBtn.disabled);
            }

            function addLine(productId) {
                const idStr = String(productId);
                if (selectedProductIds().includes(idStr)) {
                    alert('This model is already on the sale. Adjust quantity on that row, or remove it first.');
                    return;
                }
                const meta = PRODUCT_META[idStr];
                if (!meta) return;

                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100 hover:bg-slate-50/50';
                tr.setAttribute('data-line-row', '1');
                tr.setAttribute('data-product-id', idStr);
                const idx = tbody.querySelectorAll('tr[data-line-row]').length;
                const maxStock = meta.stock;
                const suggest = meta.suggest > 0 ? meta.suggest : '';

                tr.innerHTML =
                    '<td class="px-4 py-3 align-top">' +
                        '<div class="font-medium text-[#232f3e]">' + escapeHtml(meta.label) + '</div>' +
                        '<div class="text-xs text-slate-500 mt-0.5">Available stock: ' + maxStock + '</div>' +
                        '<input type="hidden" class="line-product-id" name="lines[' + idx + '][product_id]" value="' + idStr + '">' +
                    '</td>' +
                    '<td class="px-3 py-3 align-top text-right">' +
                        '<input type="number" min="1" max="' + maxStock + '" value="1" required class="admin-prod-input no-number-spin text-right w-full max-w-[6rem] ml-auto line-qty" name="lines[' + idx + '][quantity_sold]">' +
                    '</td>' +
                    '<td class="px-3 py-3 align-top text-right">' +
                        '<input type="text" required inputmode="decimal" placeholder="0" class="admin-prod-input text-right w-full max-w-[8rem] ml-auto line-price" name="lines[' + idx + '][selling_price]" value="' + (suggest !== '' ? suggest : '') + '">' +
                    '</td>' +
                    '<td class="px-3 py-3 align-top text-right font-variant-numeric font-semibold text-[#232f3e] line-line-total">0.00 TZS</td>' +
                    '<td class="px-2 py-3 align-top">' +
                        '<button type="button" class="text-red-600 hover:text-red-800 text-sm font-semibold remove-line">Remove</button>' +
                    '</td>';

                tbody.appendChild(tr);
                renumberLines();

                tr.querySelector('.line-qty').addEventListener('input', recalcGrandTotal);
                tr.querySelector('.line-price').addEventListener('input', recalcGrandTotal);
                tr.querySelector('.remove-line').addEventListener('click', function () {
                    tr.remove();
                    renumberLines();
                    recalcGrandTotal();
                });

                recalcGrandTotal();
            }

            function escapeHtml(s) {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            document.getElementById('dealer_id').addEventListener('change', recalcGrandTotal);
            paidInput.addEventListener('input', recalcGrandTotal);

            form.addEventListener('submit', function (e) {
                if (tbody.querySelectorAll('tr[data-line-row]').length === 0) {
                    e.preventDefault();
                    alert('Add at least one phone model to the sale.');
                    return false;
                }
                const paid = parseMoney(paidInput);
                const sum = parseFloat(totalHidden.value) || 0;
                if (paid > sum * 1.1 + 0.01) {
                    e.preventDefault();
                    alert('Paid amount cannot exceed the grand total.');
                    return false;
                }
                const dealerName = document.getElementById('dealer_id').options[document.getElementById('dealer_id').selectedIndex].text;
                return confirm('Record distribution sale?\n\nDealer: ' + dealerName + '\nLines: ' + tbody.querySelectorAll('tr[data-line-row]').length + '\nGrand total: ' + formatCurrency(sum) + ' TZS');
            });

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery.fn.select2) {
                    var $pick = jQuery('#product_picker');
                    $pick.select2({
                        placeholder: 'Search category / model…',
                        width: '100%',
                        allowClear: true
                    });
                    $pick.on('select2:select', function (e) {
                        const id = e.params.data.id;
                        if (id) {
                            addLine(id);
                            $pick.val(null).trigger('change');
                        }
                    });
                }
                recalcGrandTotal();
            });
        </script>
    @endpush
</x-admin-layout>
