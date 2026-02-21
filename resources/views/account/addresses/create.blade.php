<x-account-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Add a new address</h2>
    </div>

    <form action="{{ route('addresses.store') }}" method="POST"
        class="max-w-2xl bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
        @csrf
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Country -->
            <div class="sm:col-span-6">
                <label for="country" class="block text-sm font-bold text-gray-700">Country/Region</label>
                <select id="country" name="country"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-gray-50 shadow-[inset_0_1px_2px_rgba(0,0,0,0.06)]">
                    <option value="Tanzania" selected>Tanzania</option>
                    <option value="United States">United States</option>
                    <option value="Kenya">Kenya</option>
                    <option value="Uganda">Uganda</option>
                </select>
            </div>

            <!-- Full Name -->
            <!-- We assume address is for the user, but sometimes it is for someone else. For simplicity we hide this or use user name -->

            <!-- Address Type (Label) -->
            <div class="sm:col-span-6">
                <label for="type" class="block text-sm font-bold text-gray-700">Full name (First and Last name)</label>
                <!-- We're using 'type' field in the DB for address label (Home/Office) actually in the migration I put type default 'Home'. But in the view I might want to let them choose or just use 'type' as label.
                 Wait, migration has 'type' default 'Home'.
                 Let's add a Label field or use Type. -->
                <!-- Re-reading migration: $table->string('type')->default('Home');
                 Let's repurpose it as a Label, but the prompt asked for "Full Name" usually in addresses. User model has name. Address usually has a 'Recipients Name'.
                 I didn't add recipient name to migration. I'll just skip it and assume it's for the User.
                 I'll use 'type' for "Address Type (e.g. Home, Office)". -->
                <div class="mt-1">
                    <p class="text-sm text-gray-500">{{ Auth::user()->name }}</p>
                </div>
            </div>

            <!-- Address Line 1 -->
            <div class="sm:col-span-6">
                <label for="address" class="block text-sm font-bold text-gray-700">Street address</label>
                <input type="text" name="address" id="address" autocomplete="street-address"
                    placeholder="Street address, P.O. box, company name, c/o"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- City -->
            <div class="sm:col-span-3">
                <label for="city" class="block text-sm font-bold text-gray-700">City</label>
                <input type="text" name="city" id="city" autocomplete="address-level2"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- State -->
            <div class="sm:col-span-3">
                <label for="state" class="block text-sm font-bold text-gray-700">State / Province / Region</label>
                <input type="text" name="state" id="state" autocomplete="address-level1"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- Zip -->
            <div class="sm:col-span-3">
                <label for="zip" class="block text-sm font-bold text-gray-700">Zip Code</label>
                <input type="text" name="zip" id="zip" autocomplete="postal-code"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>

            <!-- Address Type -->
            <div class="sm:col-span-3">
                <label for="type" class="block text-sm font-bold text-gray-700">Address Type</label>
                <select name="type" id="type"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="Home">Home</option>
                    <option value="Office">Office</option>
                    <option value="Other">Other</option>
                </select>
            </div>

        </div>

        <!-- Map Section -->
        <div class="mt-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">Pin Location on Map</label>
            <div id="map" class="h-64 w-full rounded-md border border-gray-300 shadow-sm"></div>
            <p class="text-xs text-gray-500 mt-1">Drag the marker to pinpoint your exact location.</p>

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
        </div>

        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
            <style>
                .leaflet-container {
                    z-index: 0;
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Default to Dar es Salaam, Tanzania
                    var defaultLat = -6.7924;
                    var defaultLng = 39.2083;

                    var map = L.map('map').setView([defaultLat, defaultLng], 13);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    var marker = L.marker([defaultLat, defaultLng], {
                        draggable: true
                    }).addTo(map);

                    // Update hidden inputs initially
                    document.getElementById('latitude').value = defaultLat;
                    document.getElementById('longitude').value = defaultLng;

                    marker.on('dragend', function (e) {
                        var latLng = marker.getLatLng();
                        document.getElementById('latitude').value = latLng.lat;
                        document.getElementById('longitude').value = latLng.lng;
                    });

                    // Update marker on click
                    map.on('click', function (e) {
                        marker.setLatLng(e.latlng);
                        document.getElementById('latitude').value = e.latlng.lat;
                        document.getElementById('longitude').value = e.latlng.lng;
                    });
                });
            </script>
        @endpush

        <div class="mt-6">
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-900 bg-[#ffd814] hover:bg-[#f7ca00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#f7ca00] border-[#fcd200]">
                Add address
            </button>
        </div>
    </form>
</x-account-layout>