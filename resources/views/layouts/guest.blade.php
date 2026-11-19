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
    <body class="font-sans antialiased bg-[#060b17] text-slate-100 min-h-screen flex flex-col justify-center items-center p-4 relative overflow-x-hidden selection:bg-blue-500 selection:text-white">
        <!-- Atmospheric Glow -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-gradient-to-b from-blue-600/20 to-transparent blur-3xl rounded-full"></div>
            <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-indigo-500/10 blur-3xl rounded-full"></div>
        </div>

        <div class="relative z-10 w-full max-w-md space-y-6">
            <div class="flex justify-center">
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 p-[1px] shadow-lg shadow-blue-500/20">
                        <div class="w-full h-full bg-[#070b16] rounded-2xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <span class="font-extrabold text-xl text-white tracking-tight">
                        Backtest<span class="text-blue-400">Tool</span>
                    </span>
                </a>
            </div>

            <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
