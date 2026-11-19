<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('backtests.index') }}" class="font-semibold text-slate-400 hover:text-white transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        History
                    </a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('backtests.workspace', $session) }}" class="font-semibold text-slate-400 hover:text-white transition-colors">
                        {{ $session->title }}
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        ANALYTICS
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1 flex items-center gap-2">
                    Backtest Performance Analytics
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('backtests.workspace', $session) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full glass-panel-subtle hover:bg-white/10 text-slate-200 text-xs font-semibold border border-white/10 transition-all shadow-sm"
                >
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Workspace
                </a>

                <form action="{{ route('backtests.calculate', $session) }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold shadow-lg shadow-blue-500/25 transition-all transform active:scale-95"
                    >
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Recalculate
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="pb-16 pt-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('status'))
                <div class="p-4 rounded-2xl glass-panel border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($result)
                <!-- Primary KPI Cards (Top 4 Cards in Glassmorphic Finish) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Final Balance Card -->
                    <div class="glass-panel rounded-3xl p-5 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Final Balance</span>
                            <span class="w-2 h-2 rounded-full {{ (float) $result->net_profit >= 0 ? 'bg-emerald-400 shadow-[0_0_8px_#34d399]' : 'bg-rose-400 shadow-[0_0_8px_#f87171]' }}"></span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-white">
                            ${{ number_format((float) $result->final_balance, 2) }}
                        </div>
                        <div class="mt-1 text-xs flex items-center gap-1.5 text-slate-400">
                            <span>Modal: ${{ number_format((float) $session->initial_capital, 2) }}</span>
                            <span>·</span>
                            @php
                                $gainPct = (float) $session->initial_capital > 0 ? (((float) $result->final_balance - (float) $session->initial_capital) / (float) $session->initial_capital) * 100 : 0;
                            @endphp
                            <span class="font-bold {{ $gainPct >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $gainPct >= 0 ? '+' : '' }}{{ number_format($gainPct, 2) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Net Profit Card -->
                    <div class="glass-panel rounded-3xl p-5 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Net Profit</span>
                            <span class="text-xs text-blue-400 font-bold">$</span>
                        </div>
                        <div class="mt-2 text-2xl font-black {{ (float) $result->net_profit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ (float) $result->net_profit >= 0 ? '+$' : '-$' }}{{ number_format(abs((float) $result->net_profit), 2) }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">
                            +${{ number_format((float) $result->gross_profit, 2) }} / -${{ number_format((float) $result->gross_loss, 2) }}
                        </div>
                    </div>

                    <!-- Win Rate Card -->
                    <div class="glass-panel rounded-3xl p-5 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Win Rate</span>
                            <span class="text-xs font-bold text-slate-300">{{ $result->total_win }}W / {{ $result->total_loss }}L</span>
                        </div>
                        <div class="mt-2 text-2xl font-black {{ (float) $result->win_rate >= 50 ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ number_format((float) $result->win_rate, 1) }}%
                        </div>
                        <div class="mt-2.5 w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, max(0, (float) $result->win_rate)) }}%"></div>
                        </div>
                    </div>

                    <!-- Profit Factor Card -->
                    <div class="glass-panel rounded-3xl p-5 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Profit Factor</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/10 text-slate-300">W/L</span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-white">
                            {{ number_format((float) $result->profit_factor, 2) }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">
                            Max DD: <span class="font-bold text-rose-400">{{ number_format((float) $result->max_drawdown_percent, 2) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Secondary Stats Row (Pill Cards) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Trades</div>
                        <div class="text-lg font-black text-white mt-0.5">{{ $result->total_trades }}</div>
                    </div>
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Win (TP)</div>
                        <div class="text-lg font-black text-emerald-400 mt-0.5">{{ $result->total_win }}</div>
                    </div>
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Loss (SL)</div>
                        <div class="text-lg font-black text-rose-400 mt-0.5">{{ $result->total_loss }}</div>
                    </div>
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Max Win Streak</div>
                        <div class="text-lg font-black text-emerald-400 mt-0.5">{{ $result->max_consecutive_win }}</div>
                    </div>
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Max Loss Streak</div>
                        <div class="text-lg font-black text-rose-400 mt-0.5">{{ $result->max_consecutive_loss }}</div>
                    </div>
                    <div class="glass-panel p-3.5 rounded-2xl text-center border border-white/5">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Calculated</div>
                        <div class="text-xs font-bold text-slate-300 mt-1.5">{{ $result->calculated_at->format('M d, H:i') }}</div>
                    </div>
                </div>

                <!-- Equity Curve Chart (Chart.js styled for Dark Navy Theme) -->
                <div class="glass-panel rounded-3xl p-5 sm:p-6 border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-400 shadow-[0_0_8px_#60a5fa]"></span>
                            <h3 class="font-bold text-white text-base">Equity Curve Progression</h3>
                        </div>
                        <div class="text-xs text-slate-400">
                            Dynamic balance trajectory across trades
                        </div>
                    </div>

                    <div class="relative w-full h-[320px]">
                        <canvas id="equityCurveChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Trades Breakdown Table -->
                <div class="glass-panel rounded-3xl border border-white/10 overflow-hidden">
                    <div class="px-6 py-4 bg-white/[0.03] border-b border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <h3 class="font-bold text-white text-sm">Trade-by-Trade Performance Log</h3>
                        </div>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300 font-semibold">
                            {{ $session->trades->count() }} trades logged
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/10 text-slate-400 font-semibold uppercase tracking-wider bg-white/[0.02]">
                                    <th class="py-3 px-4">#</th>
                                    <th class="py-3 px-4">Symbol</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Entry Date</th>
                                    <th class="py-3 px-4 text-right">Entry Price</th>
                                    <th class="py-3 px-4 text-right">Stop Loss</th>
                                    <th class="py-3 px-4 text-right">Take Profit</th>
                                    <th class="py-3 px-4 text-center">Result</th>
                                    <th class="py-3 px-4 text-right">Pips</th>
                                    <th class="py-3 px-4 text-right">Profit / Loss ($)</th>
                                    <th class="py-3 px-4 text-right">Balance After</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 font-medium">
                                @foreach ($session->trades as $trade)
                                    <tr class="hover:bg-white/[0.03] transition-colors">
                                        <td class="py-3 px-4 font-bold text-white">{{ $trade->sequence_number }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                {{ $trade->symbol?->code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($trade->trade_type === 'BUY')
                                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">BUY</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-500/15 text-rose-400 border border-rose-500/30">SELL</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-300">{{ $trade->entry_date->format('Y-m-d') }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-white">{{ number_format((float) $trade->entry_price, 4) }}</td>
                                        <td class="py-3 px-4 text-right text-rose-400">{{ number_format((float) $trade->stop_loss, 4) }}</td>
                                        <td class="py-3 px-4 text-right text-emerald-400">{{ number_format((float) $trade->take_profit, 4) }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($trade->result_status === 'TP')
                                                <span class="px-3 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white shadow-sm shadow-emerald-500/30">TP</span>
                                            @else
                                                <span class="px-3 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-sm shadow-rose-500/30">SL</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold {{ (float) $trade->pips_gained >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ (float) $trade->pips_gained >= 0 ? '+' : '' }}{{ number_format((float) $trade->pips_gained, 1) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold {{ (float) $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ (float) $trade->profit_loss >= 0 ? '+$' : '-$' }}{{ number_format(abs((float) $trade->profit_loss), 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-white">
                                            ${{ number_format((float) $trade->balance_after, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <!-- No Results State -->
                <div class="glass-panel rounded-3xl p-12 text-center border border-white/10">
                    <h3 class="text-base font-bold text-white">No results calculated yet</h3>
                    <p class="text-xs text-slate-400 mt-1">Open workspace, verify your trade list, and click "Save & Calculate Results".</p>
                    <div class="mt-4">
                        <a href="{{ route('backtests.workspace', $session) }}" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100">
                            Open Workspace
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Chart.js Dark Theme Equity Curve Script -->
    @if ($result && !empty($chartData['labels']))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('equityCurveChart');
                if (!ctx) return;

                const labels = @js($chartData['labels']);
                const data = @js($chartData['data']);
                const initialCapital = {{ (float) $session->initial_capital }};

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Equity Balance ($)',
                                data: data,
                                borderColor: '#38bdf8',
                                backgroundColor: 'rgba(56, 189, 248, 0.12)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.25,
                                pointRadius: data.length > 50 ? 0 : 3.5,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#38bdf8',
                                pointBorderColor: '#0b1328',
                                pointBorderWidth: 2
                            },
                            {
                                label: 'Initial Modal ($)',
                                data: Array(labels.length).fill(initialCapital),
                                borderColor: 'rgba(255, 255, 255, 0.25)',
                                borderWidth: 1,
                                borderDash: [4, 4],
                                fill: false,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10, family: 'Plus Jakarta Sans' }
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10, family: 'Plus Jakarta Sans' },
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: '#e2e8f0',
                                    font: { size: 11, family: 'Plus Jakarta Sans', weight: 'bold' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#091124',
                                titleColor: '#fff',
                                bodyColor: '#93c5fd',
                                borderColor: 'rgba(255,255,255,0.1)',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
