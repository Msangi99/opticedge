<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="mb-8">
            <p class="admin-prod-eyebrow">Store</p>
            <h1 class="admin-prod-title">Store settings</h1>
            <p class="admin-prod-subtitle">Selcom checkout credentials and environment.</p>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Selcom configuration</h2>
                <p class="admin-prod-form-hint">Used for storefront checkout. Keep secrets out of version control in production.</p>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" class="admin-prod-form-body space-y-6">
                @csrf

                <div>
                    <label for="selcom_vendor_id" class="admin-prod-label">Vendor ID</label>
                    <input type="text" name="selcom_vendor_id" id="selcom_vendor_id" value="{{ $settings['selcom_vendor_id'] ?? '' }}" class="admin-prod-input">
                </div>

                <div>
                    <label for="selcom_api_key" class="admin-prod-label">API key</label>
                    <input type="text" name="selcom_api_key" id="selcom_api_key" value="{{ $settings['selcom_api_key'] ?? '' }}" class="admin-prod-input" autocomplete="off">
                </div>

                <div>
                    <label for="selcom_api_secret" class="admin-prod-label">API secret</label>
                    <input type="password" name="selcom_api_secret" id="selcom_api_secret" value="{{ $settings['selcom_api_secret'] ?? '' }}" class="admin-prod-input" autocomplete="new-password">
                </div>

                <div>
                    <label for="selcom_is_live" class="admin-prod-label">Environment</label>
                    <select name="selcom_is_live" id="selcom_is_live" class="admin-prod-select">
                        <option value="0" @selected(($settings['selcom_is_live'] ?? '0') == '0')>Test (apigwtest.selcommobile.com)</option>
                        <option value="1" @selected(($settings['selcom_is_live'] ?? '0') == '1')>Live (apigw.selcommobile.com)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-2">Use <strong>Live</strong> for real payments; <strong>Test</strong> for sandbox.</p>
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <button type="submit" class="admin-prod-btn-primary px-8">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
