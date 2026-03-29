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

            .select2-container--default .select2-selection--multiple {
                min-height: 42px;
                border-color: #cbd5e1;
                padding: 4px 8px;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #fff7ed;
                border-color: #fdba74;
            }
        </style>
    @endpush
    <div class="py-12 px-8">
        <a href="{{ route('admin.agents.index') }}" class="text-slate-600 hover:text-slate-900">&larr; Agents</a>
        <div class="mt-4">
            <h1 class="text-2xl font-bold text-slate-900">Assign products to agent</h1>
            <p class="mt-2 text-slate-600">Select an agent and product, then choose one or more IMEIs (unsold units from paid purchases). The agent will only see those devices on the sell screen in the app.</p>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <div class="mt-8 max-w-lg admin-clay-panel p-6">
            <form method="POST" action="{{ route('admin.agents.store-assignment') }}" class="space-y-4" id="assign-form">
                @csrf
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-slate-700">Agent</label>
                    <select id="agent_id" name="agent_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]" required>
                        <option value="">Select agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ old('agent_id', request('agent_id')) == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->email }})</option>
                        @endforeach
                    </select>
                    @error('agent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="product_id" class="block text-sm font-medium text-slate-700">Product</label>
                    <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]" required>
                        <option value="">Select product</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->category->name ?? '—' }} – {{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div id="imei-wrap" class="hidden">
                    <label for="imei_select" class="block text-sm font-medium text-slate-700">IMEIs to assign</label>
                    <p class="mt-1 text-xs text-slate-500">Only unsold devices from purchases marked paid are listed.</p>
                    <select id="imei_select" name="product_list_ids[]" multiple="multiple" class="mt-2 w-full"></select>
                    @error('product_list_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('product_list_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="rounded-lg bg-[#fa8900] px-4 py-2 text-sm font-medium text-white hover:bg-[#e87b00]">Assign</button>
                    <a href="{{ route('admin.agents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            (function () {
                const assignableUrl = @json(route('admin.agents.assignable-imeis'));
                const $product = jQuery('#product_id');
                const $imei = jQuery('#imei_select');
                const $wrap = jQuery('#imei-wrap');

                function loadImeis(productId) {
                    if (!productId) {
                        $wrap.addClass('hidden');
                        if ($imei.data('select2')) {
                            $imei.select2('destroy');
                        }
                        $imei.empty();
                        return;
                    }
                    $wrap.removeClass('hidden');
                    fetch(assignableUrl + '?product_id=' + encodeURIComponent(productId), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            const rows = (json && json.data) ? json.data : [];
                            if ($imei.data('select2')) {
                                $imei.select2('destroy');
                            }
                            $imei.empty();
                            rows.forEach(function (row) {
                                const opt = new Option(row.text, row.id, false, false);
                                $imei.append(opt);
                            });
                            $imei.select2({
                                placeholder: 'Select one or more IMEIs',
                                width: '100%',
                                closeOnSelect: false,
                            });
                        })
                        .catch(function () {
                            if ($imei.data('select2')) {
                                $imei.select2('destroy');
                            }
                            $imei.empty();
                            $imei.select2({
                                placeholder: 'Could not load IMEIs',
                                width: '100%',
                            });
                        });
                }

                $product.on('change', function () {
                    loadImeis(this.value);
                });

                document.addEventListener('DOMContentLoaded', function () {
                    if ($product.val()) {
                        loadImeis($product.val());
                    }
                });
            })();
        </script>
    @endpush
</x-admin-layout>
