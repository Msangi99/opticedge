<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'opticedgeafrica') }} - Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-bulma.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html,
        body {
            font-family: "Inter", BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
    </style>
    @stack('styles')
</head>

<body class="has-background-light" x-data="{ sidebarOpen: false }">

    <div class="admin-shell-header">
        <nav class="navbar is-dark admin-navbar-brand" role="navigation" aria-label="Admin main">
            <div class="navbar-brand">
                <button type="button" class="navbar-item button is-dark p-3" @click="sidebarOpen = !sidebarOpen"
                    aria-label="Toggle sidebar">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </span>
                </button>
                <a class="navbar-item py-4" href="{{ route('admin.dashboard') }}">
                    <span class="title is-4 has-text-white mb-0">
                        opticedg<span class="has-text-brand-orange">eafrica</span>
                    </span>
                    <span class="tag is-brand-admin is-small ml-2">ADMIN</span>
                </a>
            </div>

            <div class="navbar-menu is-active" style="background: transparent; box-shadow: none;">
                <div class="navbar-end">
                    <div class="navbar-item is-hidden-touch">
                        <button type="button" class="button is-dark is-static"
                            style="border: none; background: transparent; position: relative;">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" width="24" height="24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </span>
                            <span class="is-size-7 has-background-danger"
                                style="position: absolute; top: 0.5rem; right: 0.5rem; width: 8px; height: 8px; border-radius: 9999px;"></span>
                        </button>
                    </div>
                    <a class="navbar-item is-hidden-touch" href="/" target="_blank" rel="noopener">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </span>
                        <span>View Site</span>
                    </a>

                    <div class="navbar-item is-hidden-touch" x-data="{ userMenuOpen: false }">
                        <div class="dropdown is-right" :class="{ 'is-active': userMenuOpen }">
                            <div class="dropdown-trigger">
                                <button type="button" class="button is-dark"
                                    style="border: 1px solid transparent;"
                                    @click="userMenuOpen = !userMenuOpen"
                                    aria-haspopup="true">
                                    <span class="icon is-medium">
                                        <span
                                            class="has-background-brand-orange has-text-dark is-flex is-align-items-center is-justify-content-center"
                                            style="width: 2rem; height: 2rem; border-radius: 9999px; font-weight: 700;">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </span>
                                    <span class="is-size-7 has-text-grey-lighter is-block">Admin</span>
                                    <span class="has-text-weight-semibold">{{ Auth::user()->name }}</span>
                                    <span class="icon is-small">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <div class="dropdown-menu" role="menu" x-show="userMenuOpen" @click.away="userMenuOpen = false"
                                x-cloak x-transition>
                                <div class="dropdown-content">
                                    <a href="{{ route('profile') }}" class="dropdown-item">
                                        <span class="icon-text">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </span>
                                            <span>Profile</span>
                                        </span>
                                    </a>
                                    <hr class="dropdown-divider">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item has-text-danger">Log Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <nav class="navbar is-dark admin-navbar-sub admin-scrollbar py-2 px-4" style="min-height: unset;"
            aria-label="Admin shortcuts">
            <div class="navbar-menu is-active" style="background: transparent; overflow-x: auto; flex-wrap: nowrap;">
                <div class="navbar-start" style="flex-wrap: nowrap;">
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.orders.index') }}">Orders</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.dealers.index') }}">Dealers</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.agents.index') }}">Agents</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.stock.stocks') }}">Stock</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.reports.index') }}">Reports</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.expenses.index') }}">Expenses</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('admin.settings.index') }}">Settings</a>
                    <a class="navbar-item py-1 has-text-white" href="{{ route('command.center') }}">Commands</a>
                </div>
            </div>
        </nav>
    </div>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="is-hidden-desktop"
        style="position: fixed; background: rgba(0,0,0,0.5); z-index: 37; top: 0; left: 0; right: 0; bottom: 0;"></div>

    <aside
        class="admin-sidebar has-background-white admin-scrollbar is-flex is-flex-direction-column"
        :class="{ 'is-open': sidebarOpen }"
        style="border-right: 1px solid #ededed;">

        <div class="p-4 is-hidden-desktop is-flex is-justify-content-space-between is-align-items-center"
            style="border-bottom: 1px solid #f5f5f5;">
            <span class="title is-6 mb-0">opticedg<span class="has-text-brand-orange">eafrica</span> Menu</span>
            <button type="button" class="delete is-medium" @click="sidebarOpen = false" aria-label="Close"></button>
        </div>

        <nav class="menu p-4 admin-sidebar-scroll">
            <p class="menu-label">Dashboard</p>
            <ul class="menu-list">
                <li>
                    <a href="{{ route('admin.dashboard') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </span>
                            <span>Main Dashboard</span>
                        </span>
                    </a>
                </li>
            </ul>

            <p class="menu-label">Management</p>
            <ul class="menu-list">
                <li>
                    <a href="{{ route('admin.products.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </span>
                            <span>Products</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </span>
                            <span>Categories</span>
                        </span>
                    </a>
                </li>
                <li x-data="{ open: false }">
                    <a @click.prevent="open = !open">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            <span>Users & Dealers</span>
                        </span>
                        <span class="icon is-pulled-right">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                style="transition: transform 0.2s;" :style="open ? 'transform: rotate(180deg)' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </a>
                    <ul x-show="open" x-cloak>
                        <li><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                        <li><a href="{{ route('admin.dealers.index') }}">Dealers</a></li>
                        <li><a href="{{ route('admin.agents.index') }}">Agents</a></li>
                    </ul>
                </li>
            </ul>

            <p class="menu-label">Stock Management</p>
            <ul class="menu-list">
                <li x-data="{ open: true }">
                    <a @click.prevent="open = !open">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </span>
                            <span>Stock</span>
                        </span>
                    </a>
                    <ul x-show="open" x-cloak>
                        <li><a href="{{ route('admin.stock.stocks') }}">Stocks</a></li>
                        <li><a href="{{ route('admin.branches.index') }}">Branches</a></li>
                        <li><a href="{{ route('admin.stock.purchases') }}">Purchases</a></li>
                        <li><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                        <li><a href="{{ route('admin.stock.distribution') }}">Distribution</a></li>
                        <li><a href="{{ route('admin.stock.agent-sales') }}">Agent Sales</a></li>
                        <li><a href="{{ route('admin.stock.agent-credits') }}">Agent Credit</a></li>
                    </ul>
                </li>
            </ul>

            <p class="menu-label">Operations</p>
            <ul class="menu-list">
                <li>
                    <a href="{{ route('admin.payment-options.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </span>
                            <span>Channels</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.expenses.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span>Expenses</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <span>Sales Reports</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings.index') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <span>Store Settings</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('command.center') }}">
                        <span class="icon-text">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span>Command center</span>
                        </span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-4 mt-auto" style="border-top: 1px solid #ededed;" x-data="{ open: false }">
            <button type="button" class="button is-white is-fullwidth is-justify-content-flex-start" @click="open = !open">
                <span class="icon">
                    <span class="has-background-grey-lighter is-flex is-align-items-center is-justify-content-center"
                        style="width: 2.25rem; height: 2.25rem; border-radius: 9999px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                </span>
                <span class="is-flex is-flex-direction-column is-align-items-flex-start" style="min-width: 0;">
                    <span class="has-text-weight-semibold is-size-7 has-text-dark is-block text-truncate"
                        style="max-width: 11rem;">{{ Auth::user()->name }}</span>
                    <span class="is-size-7 has-text-grey is-block text-truncate"
                        style="max-width: 11rem;">{{ Auth::user()->email }}</span>
                </span>
                <span class="icon is-small ml-auto">
                    <svg :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>
            <div x-show="open" x-cloak class="mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="button is-danger is-light is-fullwidth is-small">Log Out</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="admin-main-offset">
        <main class="section py-5" style="min-height: calc(100vh - 7rem);">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>

</html>
