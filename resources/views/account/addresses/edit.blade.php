<x-account-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Edit address</h2>
    </div>

    <form action="{{ route('addresses.update', $address->id) }}" method="POST"
        class="max-w-2xl bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Country -->
            <div class="sm:col-span-6">
                <label for="country" class="block text-sm font-bold text-gray-700">Country/Region</label>
                <select id="country" name="country"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-gray-50 shadow-[inset_0_1px_2px_rgba(0,0,0,0.06)]">
                    <option value="Tanzania" {{ $address->country == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                    <option value="United States" {{ $address->country == 'United States' ? 'selected' : '' }}>United
                        States</option>
                    <option value="Kenya" {{ $address->country == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                    <option value="Uganda" {{ $address->country == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                </select>
            </div>

            <!-- Address Line 1 -->
            <div class="sm:col-span-6">
                <label for="address" class="block text-sm font-bold text-gray-700">Street address</label>
                <input type="text" name="address" id="address" value="{{ old('address', $address->address) }}"
                    autocomplete="street-address"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- City -->
            <div class="sm:col-span-3">
                <label for="city" class="block text-sm font-bold text-gray-700">City</label>
                <input type="text" name="city" id="city" value="{{ old('city', $address->city) }}"
                    autocomplete="address-level2"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- State -->
            <div class="sm:col-span-3">
                <label for="state" class="block text-sm font-bold text-gray-700">State / Province / Region</label>
                <input type="text" name="state" id="state" value="{{ old('state', $address->state) }}"
                    autocomplete="address-level1"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- Zip -->
            <div class="sm:col-span-3">
                <label for="zip" class="block text-sm font-bold text-gray-700">Zip Code</label>
                <input type="text" name="zip" id="zip" value="{{ old('zip', $address->zip) }}"
                    autocomplete="postal-code"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- Address Type -->
            <div class="sm:col-span-3">
                <label for="type" class="block text-sm font-bold text-gray-700">Address Type</label>
                <select name="type" id="type"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="Home" {{ $address->type == 'Home' ? 'selected' : '' }}>Home</option>
                    <option value="Office" {{ $address->type == 'Office' ? 'selected' : '' }}>Office</option>
                    <option value="Other" {{ $address->type == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

        </div>

        <div class="mt-6">
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-900 bg-[#ffd814] hover:bg-[#f7ca00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#f7ca00] border-[#fcd200]">
                Update address
            </button>
        </div>
    </form>
</x-account-layout>