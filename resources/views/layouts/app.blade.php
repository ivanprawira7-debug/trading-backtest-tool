<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Backtest Tool') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#060b17] text-slate-100 min-h-screen selection:bg-blue-500 selection:text-white relative overflow-x-hidden">
        <!-- Atmospheric Background Glow Elements -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[450px] bg-gradient-to-b from-blue-600/15 via-indigo-600/10 to-transparent blur-3xl rounded-full"></div>
            <div class="absolute top-1/3 -left-32 w-[400px] h-[400px] bg-blue-500/10 blur-3xl rounded-full"></div>
            <div class="absolute top-2/3 -right-32 w-[450px] h-[450px] bg-indigo-500/10 blur-3xl rounded-full"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col justify-between">
            <div>
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
                        {{ $header }}
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>

            <!-- Global Footer -->
            <footer class="mt-16 py-6 border-t border-white/5 text-center text-xs text-slate-500">
                <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="font-semibold text-slate-400">Backtest Tool</span>
                        <span class="text-slate-600">· Mobile-First Trading Engine</span>
                    </div>
                    <div>
                        &copy; {{ date('Y') }} All rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
