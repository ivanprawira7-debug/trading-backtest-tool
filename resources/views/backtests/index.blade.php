<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    My Backtest History
                </h1>
                <p class="text-sm text-gray-600 mt-1">Review performance, analyze equity curves, and manage your trading backtests.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('backtests.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#00b050] hover:bg-[#009b46] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00b050]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Backtest Session
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Alert -->
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border-l-4 border-[#00b050] rounded-r-md text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Sessions List Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-[#0f5132] text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <h2 class="font-semibold text-lg">Saved Backtest Sessions</h2>
                    </div>
                    <span class="text-xs bg-white/10 px-2.5 py-1 rounded-full text-white/90">
                        {{ $sessions->total() }} total {{ Str::plural('session', $sessions->total()) }}
                    </span>
                </div>

                @if ($sessions->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-emerald-50 rounded-full flex items-center justify-center text-[#0f5132]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">No backtest sessions found</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Start by creating your first trading backtest session to test your strategy rules, track trades, and calculate winrate.</p>
                        <div class="mt-6">
                            <a href="{{ route('backtests.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-sm font-semibold rounded-lg shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create First Session
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <th class="py-3 px-4">Title & Symbols</th>
                                    <th class="py-3 px-4 text-right">Modal ($)</th>
                                    <th class="py-3 px-4 text-center">Trades</th>
                                    <th class="py-3 px-4 text-center">Winrate %</th>
                                    <th class="py-3 px-4 text-right">Net Profit</th>
                                    <th class="py-3 px-4 text-right">Final Balance</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @foreach ($sessions as $session)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <!-- Title & Symbols -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-gray-900">{{ $session->title }}</div>
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                                @forelse ($session->symbols as $symbol)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-[#0f5132] border border-emerald-200">
                                                        {{ $symbol->code }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-400">No symbols</span>
                                                @endforelse
                                                <span class="text-xs text-gray-400 ml-1">· Lot: {{ number_format((float) $session->default_lot, 2) }}</span>
                                            </div>
                                        </td>

                                        <!-- Initial Capital -->
                                        <td class="py-3.5 px-4 text-right font-medium text-gray-800">
                                            ${{ number_format((float) $session->initial_capital, 2) }}
                                        </td>

                                        <!-- Total Trades -->
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                {{ $session->result ? $session->result->total_trades : $session->trades_count }}
                                            </span>
                                        </td>

                                        <!-- Winrate -->
                                        <td class="py-3.5 px-4 text-center">
                                            @if ($session->result && $session->result->total_trades > 0)
                                                @php
                                                    $winRate = (float) $session->result->win_rate;
                                                    $badgeClass = $winRate >= 50 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                    {{ number_format($winRate, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>

                                        <!-- Net Profit -->
                                        <td class="py-3.5 px-4 text-right font-semibold">
                                            @if ($session->result)
                                                @php
                                                    $netProfit = (float) $session->result->net_profit;
                                                    $colorClass = $netProfit > 0 ? 'text-[#00b050]' : ($netProfit < 0 ? 'text-[#e52521]' : 'text-gray-600');
                                                    $prefix = $netProfit > 0 ? '+$' : ($netProfit < 0 ? '-$' : '$');
                                                @endphp
                                                <span class="{{ $colorClass }}">
                                                    {{ $prefix }}{{ number_format(abs($netProfit), 2) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>

                                        <!-- Final Balance -->
                                        <td class="py-3.5 px-4 text-right font-semibold text-gray-900">
                                            @if ($session->result)
                                                ${{ number_format((float) $session->result->final_balance, 2) }}
                                            @else
                                                ${{ number_format((float) $session->initial_capital, 2) }}
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4 text-center">
                                            @if ($session->status === 'COMPLETED')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#00b050]"></span>
                                                    COMPLETED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    DRAFT
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('backtests.workspace', $session) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#0f5132] hover:bg-[#0c4027] text-white text-xs font-medium rounded-md shadow-sm transition-colors" title="Open Trading Workspace">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Workspace
                                                </a>

                                                @if ($session->result)
                                                    <a href="{{ route('backtests.results', $session) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-medium rounded-md shadow-sm transition-colors" title="View Analytics & Equity Curve">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                                        Analytics
                                                    </a>
                                                @endif

                                                <form action="{{ route('backtests.destroy', $session) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backtest session?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-[#e52521] transition-colors rounded" title="Delete Session">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($sessions->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $sessions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
