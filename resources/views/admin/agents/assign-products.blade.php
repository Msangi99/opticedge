<x-admin-layout>
    @include('admin.partials.catalog-styles')

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .admin-prod-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 1.75rem;
                padding-left: 0.15rem;
            }

            .admin-prod-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 2.5rem;
            }
        </style>
    @endpush

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Sales team</p>
                <h1 class="admin-prod-title">Assign products to agent</h1>
                <p class="admin-prod-subtitle">Select an agent and product, then choose one or more IMEIs (unsold units from
                    eligible purchases: paid, partial, unpaid, or purchase still has IMEI limit remaining). The agent will only see those devices on the sell screen in the app.</p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="admin-prod-back shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to agents
            </a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden admin-prod-select2-wrap">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Assignment</h2>
                <p class="admin-prod-form-hint">Agent, product, and IMEIs.</p>
            </div>
            <form method="POST" action="{{ route('admin.agents.store-assignment') }}" class="admin-prod-form-body space-y-6"
                id="assign-form">
                @csrf
                <div>
                    <label for="agent_id" class="admin-prod-label">Agent</label>
                    <select id="agent_id" name="agent_id" class="admin-prod-select" required>
                        <option value="">Select agent</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}"
                                {{ old('agent_id', request('agent_id')) == $a->id ? 'selected' : '' }}>
                                {{ $a->name }} ({{ $a->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('agent_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="product_id" class="admin-prod-label">Product</label>
                    <select id="product_id" name="product_id" class="admin-prod-select" required>
                        <option value="">Select product</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->category->name ?? '—' }} – {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div id="imei-wrap" class="hidden">
                    <label for="imei_select" class="admin-prod-label">IMEIs to assign</label>
                    <p class="text-xs text-slate-500 mt-0.5 mb-2">Only unsold devices from eligible purchases are listed (paid, partial, unpaid, or purchase still has IMEI slots left; matched by catalog product or linked purchase).</p>
                    <select id="imei_select" name="product_list_ids[]" multiple="multiple" class="w-full"></select>
                    @error('product_list_ids')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                    @error('product_list_ids.*')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.agents.index') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Assign</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            (function () {
                const assignableUrl = @json(route('admin.assignable-imeis'));
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
