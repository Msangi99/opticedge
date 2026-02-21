<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">Payment Settings</h2>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 max-w-2xl">
        <h3 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Selcom Configuration</h3>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Vendor ID</label>
                    <input type="text" name="selcom_vendor_id" value="{{ $settings['selcom_vendor_id'] ?? '' }}"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                    <input type="text" name="selcom_api_key" value="{{ $settings['selcom_api_key'] ?? '' }}"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">API Secret</label>
                    <input type="password" name="selcom_api_secret" value="{{ $settings['selcom_api_secret'] ?? '' }}"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Environment</label>
                    <select name="selcom_is_live" class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                        <option value="0" {{ ($settings['selcom_is_live'] ?? '0') == '0' ? 'selected' : '' }}>Test (apigwtest.selcommobile.com)</option>
                        <option value="1" {{ ($settings['selcom_is_live'] ?? '0') == '1' ? 'selected' : '' }}>Live (apigw.selcommobile.com)</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Use <strong>Live</strong> for production with real payments. Use <strong>Test</strong> for sandbox.</p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="bg-[#fa8900] hover:bg-[#e87f00] text-white font-bold py-2 px-4 rounded shadow-sm transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>