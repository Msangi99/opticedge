<x-account-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Your Account</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Orders Card -->
        <a href="{{ route('orders.index') }}"
            class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-8 h-8 text-[#fa8900]" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Your Orders</h3>
                    <p class="text-sm text-gray-500">Track, return, or buy things again</p>
                </div>
            </div>
        </a>

        <!-- Addresses Card -->
        <a href="{{ route('addresses.index') }}"
            class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Your Addresses</h3>
                    <p class="text-sm text-gray-500">Edit addresses for orders</p>
                </div>
            </div>
        </a>

        <!-- History Card -->
        <a href="#"
            class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-stone-100 rounded-full">
                    <svg class="w-8 h-8 text-stone-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Browsing History</h3>
                    <p class="text-sm text-gray-500">View and edit your history</p>
                </div>
            </div>
        </a>
    </div>
</x-account-layout>