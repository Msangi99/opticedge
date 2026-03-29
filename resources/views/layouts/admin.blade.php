<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full bg-gradient-to-br from-[#dce3ee] via-[#e8edf5] to-[#d4dce8]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'opticedgeafrica') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style type="text/css">
        @theme {
            --color-brand-black: #232f3e;
            --color-brand-dark: #19212c;
            --color-brand-orange: #fa8900;
            --color-brand-yellow: #febd69;
            --color-clay-canvas-from: #dce3ee;
            --color-clay-canvas-via: #e8edf5;
            --color-clay-canvas-to: #d4dce8;
            --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.35);
            border-radius: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 6px;
            box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.5);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5);
        }

        /* Clay panels (mirrors resources/css/app.css — ensures admin works without Vite) */
        .admin-clay-panel {
            border-radius: 1.5rem;
            background: linear-gradient(155deg,
                    rgba(255, 255, 255, 0.94) 0%,
                    rgba(248, 250, 252, 0.9) 45%,
                    rgba(241, 245, 249, 0.88) 100%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow:
                10px 12px 24px rgba(163, 177, 198, 0.28),
                -6px -8px 20px rgba(255, 255, 255, 0.9),
                inset 2px 2px 5px rgba(255, 255, 255, 0.85),
                inset -2px -3px 8px rgba(148, 163, 184, 0.06);
        }

        .admin-clay-panel-interactive {
            border-radius: 1.5rem;
            background: linear-gradient(155deg,
                    rgba(255, 255, 255, 0.96) 0%,
                    rgba(248, 250, 252, 0.92) 100%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow:
                8px 10px 22px rgba(163, 177, 198, 0.26),
                -5px -6px 18px rgba(255, 255, 255, 0.88),
                inset 2px 2px 4px rgba(255, 255, 255, 0.8),
                inset -2px -2px 6px rgba(148, 163, 184, 0.05);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .admin-clay-panel-interactive:hover {
            transform: translateY(-3px);
            box-shadow:
                14px 16px 32px rgba(163, 177, 198, 0.32),
                -6px -8px 22px rgba(255, 255, 255, 0.95),
                inset 2px 2px 5px rgba(255, 255, 255, 0.9),
                inset -2px -3px 8px rgba(148, 163, 184, 0.07);
        }

        .admin-clay-inset {
            border-radius: 1rem;
            background: linear-gradient(165deg, rgba(226, 232, 240, 0.45), rgba(248, 250, 252, 0.65));
            box-shadow:
                inset 4px 4px 10px rgba(163, 177, 198, 0.2),
                inset -3px -3px 8px rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body
    class="font-sans antialiased text-slate-600 min-h-full bg-gradient-to-br from-[#dce3ee] via-[#e8edf5] to-[#d4dce8]"
    x-data="{ sidebarOpen: false }">

    <!-- Header (clay slab) -->
    <header class="sticky top-0 z-50 px-3 pt-3 sm:px-4 sm:pt-4">
        <div
            class="max-w-[1600px] mx-auto rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-white/95 via-slate-50/90 to-slate-100/85 text-slate-700 shadow-[10px_14px_28px_rgba(163,177,198,0.28),-6px_-8px_20px_rgba(255,255,255,0.95),inset_2px_2px_4px_rgba(255,255,255,0.9),inset_-1px_-2px_6px_rgba(148,163,184,0.06)] overflow-hidden">
        <!-- Main Bar -->
        <div class="flex items-center gap-2 lg:gap-4 px-3 py-2.5 sm:px-4 sm:py-3">
            <!-- Sidebar Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen"
                class="flex items-center gap-1 p-2 rounded-xl admin-clay-inset text-slate-600 hover:text-[#232f3e] transition-all duration-200"
                aria-label="Toggle Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Logo -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center pt-1 px-2 rounded-xl transition-all duration-200 hover:bg-white/60">
                <span class="text-2xl font-bold tracking-tight text-[#232f3e]">opticedg<span
                        class="text-[#fa8900]">eafrica</span></span>
                <span
                    class="ml-2 text-xs font-semibold bg-gradient-to-br from-[#fa8900] to-[#e67a00] text-white px-2.5 py-1 rounded-lg shadow-[3px_3px_8px_rgba(250,137,0,0.35),inset_1px_1px_2px_rgba(255,255,255,0.35)]">ADMIN</span>
            </a>

            <!-- Spacer -->
            <div class="flex-grow"></div>

            <!-- Quick Actions -->
            <div class="hidden md:flex items-center gap-2">
                <!-- Notifications -->
                <button
                    class="p-2 rounded-xl admin-clay-inset text-slate-600 hover:text-[#232f3e] transition-all duration-200 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- View Website -->
                <a href="/" target="_blank"
                    class="flex items-center gap-2 p-2 px-3 rounded-xl admin-clay-inset text-slate-600 hover:text-[#232f3e] transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span class="text-sm font-medium">View Site</span>
                </a>
            </div>

            <!-- User Profile -->
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 p-1.5 pr-2 rounded-2xl admin-clay-inset text-slate-700 hover:text-[#232f3e] transition-all duration-200">
                    <div
                        class="w-9 h-9 rounded-full bg-gradient-to-br from-[#fa8900] to-[#e07800] flex items-center justify-center text-sm font-bold text-white shadow-[inset_1px_2px_4px_rgba(255,255,255,0.35),2px_3px_8px_rgba(250,137,0,0.3)]">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="hidden md:flex flex-col items-start">
                        <span class="text-xs text-slate-500">Admin</span>
                        <span class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': userMenuOpen }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- User Dropdown -->
                <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-52 rounded-2xl border border-white/90 bg-gradient-to-br from-white/98 to-slate-50/95 py-1 z-50 shadow-[12px_16px_32px_rgba(163,177,198,0.35),-4px_-4px_12px_rgba(255,255,255,0.9),inset_1px_1px_2px_rgba(255,255,255,0.8)]">
                    <a href="{{ route('profile') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-white/80 rounded-xl mx-1">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-white/80 hover:text-red-600 rounded-xl mx-1 mb-1">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Log Out
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sub Navigation (inset clay strip) -->
        <div
            class="admin-clay-inset flex items-center gap-1 py-1.5 px-2 sm:px-3 mx-2 mb-2 text-sm font-medium overflow-x-auto whitespace-nowrap custom-scrollbar">
            <a href="{{ route('admin.dashboard') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Dashboard</a>
            <a href="{{ route('admin.orders.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Orders</a>
            <a href="{{ route('admin.dealers.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Dealers</a>
            <a href="{{ route('admin.agents.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Agents</a>
            <a href="{{ route('admin.stock.stocks') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Stock</a>
            <a href="{{ route('admin.reports.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Reports</a>
            <a href="{{ route('admin.expenses.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Expenses</a>
            <a href="{{ route('admin.settings.index') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Settings</a>
            <a href="{{ route('command.center') }}"
                class="px-3 py-1.5 rounded-xl text-slate-600 hover:text-[#232f3e] hover:bg-white/70 transition-all shrink-0">Commands</a>
        </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Overlay (Mobile) -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-64 flex-shrink-0 flex flex-col h-[calc(100vh-124px)] overflow-y-auto transform transition-transform duration-300 ease-in-out custom-scrollbar mt-[124px] sm:mt-[128px] rounded-r-[1.75rem] border border-white/70 border-l-0 bg-gradient-to-b from-white/95 via-slate-50/92 to-slate-100/88 shadow-[8px_12px_28px_rgba(163,177,198,0.22),inset_2px_0_8px_rgba(255,255,255,0.65)]">

            <!-- Close button (Mobile) -->
            <div class="lg:hidden flex items-center justify-between p-4 border-b border-white/50">
                <span class="text-lg font-bold tracking-tight text-[#232f3e]">opticedg<span
                        class="text-[#fa8900]">eafrica</span>
                    Menu</span>
                <button @click="sidebarOpen = false" class="p-2 rounded-xl admin-clay-inset text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-6">

                <!-- Dashboard Section -->
                <div>
                    <h3 class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Dashboard</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Main Dashboard
                        </a>
                    </div>
                </div>

                <!-- Management Section -->
                <div>
                    <h3 class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Management</h3>
                    <div class="space-y-1">

                        <!-- Products -->
                        <a href="{{ route('admin.products.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Products
                        </a>
                        <a href="{{ route('admin.categories.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Categories
                        </a>

                        <!-- Customers -->
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Users & Dealers
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="pl-10 space-y-1 mt-1 border-l-2 border-white/60 ml-4">
                                <a href="{{ route('admin.customers.index') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Customers</a>
                                <a href="{{ route('admin.dealers.index') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Dealers</a>
                                <a href="{{ route('admin.agents.index') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Agents</a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Stock Management Section -->
                <div>
                    <h3 class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Stock Management</h3>
                    <div class="space-y-1">
                        <div x-data="{ open: true }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    Stock
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="pl-10 space-y-1 mt-1 border-l-2 border-white/60 ml-4">
                                <a href="{{ route('admin.stock.stocks') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Stocks</a>
                                <a href="{{ route('admin.branches.index') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Branches</a>
                                <a href="{{ route('admin.stock.purchases') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Purchases</a>
                                <a href="{{ route('admin.orders.index') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Orders</a>
                                <a href="{{ route('admin.stock.distribution') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Distribution</a>
                                <a href="{{ route('admin.stock.agent-sales') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Agent Sales</a>
                                <a href="{{ route('admin.stock.agent-credits') }}"
                                    class="block px-2 py-1.5 text-sm text-slate-600 hover:text-slate-900">Agent Credit</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operations Section -->
                <div>
                    <h3 class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Operations</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.payment-options.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Channels
                        </a>
                        <a href="{{ route('admin.expenses.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Expenses
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Sales Reports
                        </a>
                        <a href="{{ route('admin.settings.index') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Store Settings
                        </a>
                        <a href="{{ route('command.center') }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-700 rounded-xl hover:bg-white/75 group transition-all shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Command center
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Profile (Bottom) -->
            <div class="p-4 border-t border-white/50 mt-auto" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" class="w-full flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-full admin-clay-inset flex items-center justify-center text-slate-600 font-bold overflow-hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 text-left">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute bottom-full left-0 w-full mb-2 rounded-2xl border border-white/90 bg-gradient-to-br from-white/98 to-slate-50/95 py-1 z-50 shadow-[12px_16px_32px_rgba(163,177,198,0.35),-4px_-4px_12px_rgba(255,255,255,0.9),inset_1px_1px_2px_rgba(255,255,255,0.8)]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-white/80 hover:text-red-600 flex items-center gap-2 rounded-xl mx-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:pl-64 flex flex-col min-h-[calc(100vh-124px)] sm:min-h-[calc(100vh-128px)] overflow-y-auto">
            <!-- Main Content -->
            <main class="flex-1 bg-transparent p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>