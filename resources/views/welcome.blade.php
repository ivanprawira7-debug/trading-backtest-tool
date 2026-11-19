<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>Backtest Tool — Mobile-First Trading Backtesting</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#060b17] text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden">
        <!-- Atmospheric Glows (Matching reference screenshot) -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[650px] h-[400px] bg-gradient-to-b from-blue-600/20 via-indigo-600/10 to-transparent blur-3xl rounded-full"></div>
            <div class="absolute top-1/2 -left-40 w-[500px] h-[500px] bg-blue-500/10 blur-3xl rounded-full"></div>
            <div class="absolute top-1/3 -right-40 w-[500px] h-[500px] bg-indigo-500/15 blur-3xl rounded-full"></div>
        </div>

        <div class="relative z-10 flex-1 flex flex-col justify-between">
            <!-- Navbar -->
            <nav class="sticky top-0 z-40 bg-[#060b17]/75 backdrop-blur-xl border-b border-white/10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 p-[1px] shadow-lg shadow-blue-500/20">
                                <div class="w-full h-full bg-[#070b16] rounded-2xl flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                            </div>
                            <span class="font-extrabold text-base text-white tracking-tight">
                                Backtest<span class="text-blue-400">Tool</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 shadow-md transition-all">
                                    Open Workspace →
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-white transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-5 py-2 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 shadow-md transition-all">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section (Mobile-First Showcase Inspired by Reference Screenshot) -->
            <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 text-center space-y-10">

                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-xs font-bold text-blue-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                        Mobile-first approach to ensure flawless usability
                    </div>

                    <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight max-w-3xl mx-auto">
                        Precision Trading Backtests <br class="hidden sm:block"/>
                        <span class="bg-gradient-to-r from-blue-400 via-indigo-300 to-white bg-clip-text text-transparent">
                            on Any Screen Size
                        </span>
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-400 max-w-lg mx-auto">
                        Optimized mobile experience for Forex & Gold traders. Auto-calculate stop loss & take profit, track winrates, and monitor equity curves seamlessly.
                    </p>
                </div>

                <!-- CTA Pills -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold text-sm shadow-xl shadow-blue-500/30 transition-all transform active:scale-95">
                            Go to My Backtest Workspace
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-white text-slate-900 hover:bg-slate-100 font-extrabold text-sm shadow-xl transition-all transform active:scale-95">
                            Start Backtesting Free
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-full glass-panel-subtle hover:bg-white/10 text-white font-bold text-sm border border-white/10 transition-all">
                            Sign In
                        </a>
                    @endauth
                </div>

                <!-- Feature Glass Showcase Cards (Matching Reference Layout) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-8 text-left">
                    <div class="glass-panel rounded-3xl p-6 border border-white/10 space-y-3">
                        <div class="w-8 h-8 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-400 flex items-center justify-center text-xs font-bold">
                            ⚡
                        </div>
                        <h3 class="text-white font-bold text-sm">Auto Fill SL & TP</h3>
                        <p class="text-[11px] text-slate-400">
                            Preset your pip targets and let the engine calculate exact price thresholds for BUY and SELL trades based on pip sizes.
                        </p>
                    </div>

                    <div class="glass-panel rounded-3xl p-6 border border-white/10 space-y-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xs font-bold">
                            📈
                        </div>
                        <h3 class="text-white font-bold text-sm">Interactive Equity Curve</h3>
                        <p class="text-[11px] text-slate-400">
                            Chart.js visualization of your equity trajectory, win streaks, profit factor, and maximum drawdown calculations.
                        </p>
                    </div>

                    <div class="glass-panel rounded-3xl p-6 border border-white/10 space-y-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-xs font-bold">
                            📂
                        </div>
                        <h3 class="text-white font-bold text-sm">CSV Import & Export</h3>
                        <p class="text-[11px] text-slate-400">
                            Bulk import your trades from TradingView or MetaTrader with one click using the built-in CSV template.
                        </p>
                    </div>
                </div>

            </main>

            <!-- Footer -->
            <footer class="py-6 border-t border-white/5 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Backtest Tool. Designed with a mobile-first philosophy.
            </footer>
        </div>
    </body>
</html>
