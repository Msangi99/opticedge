<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'opticedgeafrica') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <!-- Styles / Scripts -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style type="text/css">
        @theme {
            --color-brand-black: #232f3e;
            --color-brand-dark: #19212c;
            --color-brand-yellow: #febd69;
            --color-brand-orange: #fa8900;
            --color-brand-blue: #007185;

            --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
        }
    </style>
    @vite(['resources/js/app.js'])
</head>

<body
    class="font-sans antialiased text-slate-900 bg-slate-50 h-full flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center mb-6">
        <a href="/" class="flex justify-center items-center gap-1 mb-6">
            <span class="text-3xl font-bold tracking-tight text-[#232f3e]">opticedg<span
                    class="text-[#fa8900]">eafrica</span></span>
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-200">
            {{ $slot }}
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            <p>&copy; 2026 OpticEdgeAfrica, Inc. All rights reserved.</p>
        </div>
    </div>
</body>

</html>