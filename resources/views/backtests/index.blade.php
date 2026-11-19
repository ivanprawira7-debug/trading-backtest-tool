<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-xs font-semibold text-blue-400 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Portfolio & History Dashboard
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    My Backtest Sessions
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Review strategy winrates, monitor profit factors, and inspect equity curves.</p>
            </div>
            <div>
                <a
                    href="{{ route('backtests.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold shadow-lg shadow-blue-500/25 transition-all transform active:scale-95"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    New Backtest Session
                </a>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Alert -->
            @if (session('status'))
                <div class="p-4 rounded-2xl glass-panel border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">✓</span>
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Sessions List Container -->
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wider font-bold text-slate-400">Trading Sessions</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300 font-semibold">
                            {{ $sessions->total() }}
                        </span>
                    </div>
                </div>

                @if ($sessions->isEmpty())
                    <!-- Empty State -->
                    <div class="glass-panel rounded-3xl p-12 text-center border border-white/10">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">No backtest sessions found</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Create your first trading backtest session to test your strategy rules, track trades, and calculate winrate.</p>
                        <div class="mt-6">
                            <a href="{{ route('backtests.create') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-slate-900 rounded-full text-xs font-bold hover:bg-slate-100 shadow-lg transition-all">
                                + Create First Session
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Grid of Session Cards (Mobile-first card design inspired by reference image) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($sessions as $session)
                            <div class="glass-panel rounded-3xl p-5 border border-white/10 hover:border-blue-500/30 transition-all group flex flex-col justify-between relative overflow-hidden">
                                <!-- Top Accent Glow -->
                                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl pointer-events-none"></div>

                                <div>
                                    <!-- Header: Title & Status Pill -->
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <h3 class="font-bold text-white text-base group-hover:text-blue-300 transition-colors line-clamp-1">
                                                {{ $session->title }}
                                            </h3>
                                            @if ($session->description)
                                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                                    {{ $session->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $session->status === 'COMPLETED' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                            {{ $session->status }}
                                        </span>
                                    </div>

                                    <!-- Symbols Pills -->
                                    <div class="flex flex-wrap items-center gap-1.5 mt-3">
                                        @forelse ($session->symbols as $symbol)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                {{ $symbol->code }}
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-slate-500">No symbols</span>
                                        @endforelse
                                        <span class="text-[10px] text-slate-500 ml-1">· Lot: {{ number_format((float) $session->default_lot, 2) }}</span>
                                    </div>

                                    <!-- Stats Grid -->
                                    <div class="grid grid-cols-3 gap-2 mt-4 p-3 rounded-2xl bg-white/[0.03] border border-white/5 text-center">
                                        <div>
                                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Trades</span>
                                            <span class="font-extrabold text-sm text-white mt-0.5 block">
                                                {{ $session->result ? $session->result->total_trades : $session->trades_count }}
                                            </span>
                                        </div>

                                        <div>
                                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Winrate</span>
                                            @if ($session->result && $session->result->total_trades > 0)
                                                @php $winRate = (float) $session->result->win_rate; @endphp
                                                <span class="font-extrabold text-sm mt-0.5 block {{ $winRate >= 50 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                    {{ number_format($winRate, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-500 mt-0.5 block">—</span>
                                            @endif
                                        </div>

                                        <div>
                                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Net Profit</span>
                                            @if ($session->result)
                                                @php $net = (float) $session->result->net_profit; @endphp
                                                <span class="font-extrabold text-sm mt-0.5 block {{ $net >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                    {{ $net >= 0 ? '+$' : '-$' }}{{ number_format(abs($net), 2) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-500 mt-0.5 block">—</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Balance Row -->
                                    <div class="flex items-center justify-between mt-3 px-1 text-xs">
                                        <span class="text-slate-400">Final Balance:</span>
                                        <span class="font-black text-white text-sm">
                                            @if ($session->result)
                                                ${{ number_format((float) $session->result->final_balance, 2) }}
                                            @else
                                                ${{ number_format((float) $session->initial_capital, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Action Footer (Matching circular action buttons from screenshot) -->
                                <div class="pt-4 mt-4 border-t border-white/5 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <!-- Workspace Button -->
                                        <a
                                            href="{{ route('backtests.workspace', $session) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition-all shadow-sm"
                                        >
                                            <span>Workspace</span>
                                            <svg class="w-3.5 h-3.5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>

                                        @if ($session->result)
                                            <!-- Analytics Button -->
                                            <a
                                                href="{{ route('backtests.results', $session) }}"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/10 hover:bg-white/15 text-white text-xs font-semibold border border-white/10 transition-all"
                                                title="View Equity Curve & Detailed Stats"
                                            >
                                                <span>Analytics</span>
                                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Delete Icon -->
                                    <form action="{{ route('backtests.destroy', $session) }}" method="POST" onsubmit="return confirm('Delete this backtest session?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-full bg-white/5 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 flex items-center justify-center transition-all" title="Delete">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($sessions->hasPages())
                        <div class="pt-6">
                            {{ $sessions->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
