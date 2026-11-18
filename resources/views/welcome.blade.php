<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Backtest Tool — Trading Backtesting Software</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col justify-between">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-[#0f5132] flex items-center justify-center text-white shadow-sm">
                            <svg class="w-5 h-5 text-[#00b050]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 9h2v10H5V9zm4-4h2v14H9V5zm4 8h2v6h-2v-6zm4-5h2v11h-2V8z"/>
                            </svg>
                        </div>
                        <div class="font-bold text-xl text-gray-900 tracking-tight">
                            Backtest<span class="text-[#00b050]">Tool</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                Go to Workspace
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-1 flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl w-full text-center space-y-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-[#0f5132]">
                    <span class="w-2 h-2 rounded-full bg-[#00b050]"></span>
                    Precision Trading Backtesting for Forex & Commodities
                </div>

                <h1 class="text-4xl sm:text-6xl font-extrabold text-gray-900 tracking-tight">
                    Master Your Strategy with <br/>
                    <span class="text-[#0f5132]">Data-Driven</span>
                    <span class="text-[#00b050]">Backtesting</span>
                </h1>

                <p class="max-w-2xl mx-auto text-base sm:text-lg text-gray-600">
                    Track your trade entries, auto-calculate Stop Loss & Take Profit pips, evaluate winrates, and monitor your running equity curve with a modern trading interface.
                </p>

                <!-- Action CTA -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-[#00b050] hover:bg-[#009b46] text-white text-base font-bold rounded-xl shadow-md transition-all">
                            Open My Backtest Workspace
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-[#00b050] hover:bg-[#009b46] text-white text-base font-bold rounded-xl shadow-md transition-all">
                            Start Backtesting Free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 text-base font-semibold rounded-xl shadow-sm transition-colors">
                            Sign in to Existing Session
                        </a>
                    @endauth
                </div>

                <!-- Feature Highlights Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-12 text-left">
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-[#0f5132] flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-base">Auto Fill SL & TP</h3>
                        <p class="text-xs text-gray-500 mt-1">Preset your target pips and let the tool calculate exact price thresholds for BUY and SELL trades.</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-[#0f5132] flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-base">Visual Equity Curve</h3>
                        <p class="text-xs text-gray-500 mt-1">Interactive Chart.js equity curve showing balance progression, drawdowns, and profit factors.</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-[#0f5132] flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-base">CSV Import & Export</h3>
                        <p class="text-xs text-gray-500 mt-1">Download templates and bulk import your trading history from TradingView or MetaTrader effortlessly.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Backtest Tool. Designed for serious traders.
        </footer>
    </body>
</html>
