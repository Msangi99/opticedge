<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-sm text-gray-500">Dealer Account Details</p>
                </div>
                <div>
                    <a href="{{ route('admin.dealers.index') }}" class="text-indigo-600 hover:text-indigo-900">Back to List</a>
                </div>
            </div>

            <!-- Dealer Info Card -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6 border border-gray-200">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Applicant Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Personal details and application status.</p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Business Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold">{{ $user->business_name }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Full name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email address</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 text-indigo-600 font-medium">{{ $user->email }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Phone number</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->phone ?? 'N/A' }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : ($user->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Joined Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->created_at->format('F d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Addresses Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6 border border-gray-200">
                 <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Adresses & Locations</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Registered locations for this dealer.</p>
                </div>
                <div class="border-t border-gray-200 p-6">
                    @if($user->addresses->isEmpty())
                        <p class="text-gray-500 text-sm">No addresses registered yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($user->addresses as $address)
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="mb-4">
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-700 mb-2">{{ $address->type }}</span>
                                        @if($address->is_default)
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 mb-2">Default</span>
                                        @endif
                                        <p class="text-sm text-gray-900 font-bold block">{{ $address->address }}</p>
                                        <p class="text-sm text-gray-600 block">{{ $address->city }}, {{ $address->state }} {{ $address->zip }}</p>
                                        <p class="text-sm text-gray-600 block">{{ $address->country }}</p>
                                    </div>
                                    
                                    @if($address->latitude && $address->longitude)
                                        <div id="map-{{ $address->id }}" class="h-48 w-full rounded-md border border-gray-300 shadow-sm z-0"></div>
                                        <div class="mt-2 text-xs">
                                            <span class="text-gray-400">Lat: {{ $address->latitude }}, Lng: {{ $address->longitude }}</span>
                                            <span class="mx-1 text-gray-300">|</span>
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $address->latitude }},{{ $address->longitude }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                Open in Google Maps
                                            </a>
                                        </div>
                                    @else
                                        <div class="h-48 w-full rounded-md border border-gray-300 shadow-sm bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                                            No map location provided
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
             <div class="bg-white shadow sm:rounded-lg mb-6 border border-gray-200 p-6 flex items-center gap-4">
                 @if($user->status === 'pending')
                    <form action="{{ route('admin.dealers.approve', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve Dealer
                        </button>
                    </form>
                    <form action="{{ route('admin.dealers.reject', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reject
                        </button>
                    </form>
                @elseif($user->status === 'active')
                    <form action="{{ route('admin.dealers.reject', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                         <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Suspend Account
                        </button>
                    </form>
                @elseif($user->status === 'suspended')
                    <form action="{{ route('admin.dealers.approve', $user->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Re-activate Account
                        </button>
                    </form>
                @endif
             </div>

        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            .leaflet-container {
                z-index: 1; /* Ensure maps don't overlap strangely */
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var addresses = @json($user->addresses);

                addresses.forEach(function(address) {
                    if (address.latitude && address.longitude) {
                        var mapId = 'map-' + address.id;
                        var lat = parseFloat(address.latitude);
                        var lng = parseFloat(address.longitude);

                        var map = L.map(mapId, {
                            center: [lat, lng],
                            zoom: 15,
                            dragging: true,
                            touchZoom: true,
                            scrollWheelZoom: true,
                            doubleClickZoom: true,
                            boxZoom: true,
                            zoomControl: true
                        });

                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                             attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(map);

                        L.marker([lat, lng]).addTo(map);
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
