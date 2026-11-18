<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('backtests.index') }}" class="text-sm font-medium text-gray-500 hover:text-[#0f5132] flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        History
                    </a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('backtests.workspace', $session) }}" class="text-sm font-medium text-gray-500 hover:text-[#0f5132]">
                        {{ $session->title }}
                    </a>
                    <span class="text-gray-300">/</span>
                    <span class="text-xs px-2 py-0.5 rounded font-semibold bg-emerald-100 text-emerald-800">
                        ANALYTICS & RESULTS
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1 flex items-center gap-2">
                    Backtest Performance Analytics
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('backtests.workspace', $session) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 shadow-sm transition-colors">
                    <svg class="w-4 h-4 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit in Workspace
                </a>

                <form action="{{ route('backtests.calculate', $session) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Recalculate
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Alert -->
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border-l-4 border-[#00b050] rounded-r-md text-emerald-800 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($result)
                <!-- Key KPI Cards (Top 4 Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Final Balance Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Final Balance</span>
                            <span class="w-2 h-2 rounded-full {{ (float) $result->net_profit >= 0 ? 'bg-[#00b050]' : 'bg-[#e52521]' }}"></span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-gray-900">
                            ${{ number_format((float) $result->final_balance, 2) }}
                        </div>
                        <div class="mt-1 text-xs flex items-center gap-1.5 text-gray-500">
                            <span>Initial: ${{ number_format((float) $session->initial_capital, 2) }}</span>
                            <span>·</span>
                            @php
                                $gainPct = (float) $session->initial_capital > 0 ? (((float) $result->final_balance - (float) $session->initial_capital) / (float) $session->initial_capital) * 100 : 0;
                            @endphp
                            <span class="font-bold {{ $gainPct >= 0 ? 'text-[#00b050]' : 'text-[#e52521]' }}">
                                {{ $gainPct >= 0 ? '+' : '' }}{{ number_format($gainPct, 2) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Net Profit Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Net Profit</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="mt-2 text-2xl font-black {{ (float) $result->net_profit >= 0 ? 'text-[#00b050]' : 'text-[#e52521]' }}">
                            {{ (float) $result->net_profit >= 0 ? '+$' : '-$' }}{{ number_format(abs((float) $result->net_profit), 2) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Gross: +${{ number_format((float) $result->gross_profit, 2) }} / -${{ number_format((float) $result->gross_loss, 2) }}
                        </div>
                    </div>

                    <!-- Win Rate Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Win Rate</span>
                            <span class="text-xs font-bold text-gray-700">{{ $result->total_win }}W / {{ $result->total_loss }}L</span>
                        </div>
                        <div class="mt-2 text-2xl font-black {{ (float) $result->win_rate >= 50 ? 'text-[#00b050]' : 'text-amber-600' }}">
                            {{ number_format((float) $result->win_rate, 1) }}%
                        </div>
                        <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#00b050] h-1.5 rounded-full" style="width: {{ min(100, max(0, (float) $result->win_rate)) }}%"></div>
                        </div>
                    </div>

                    <!-- Profit Factor Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <span>Profit Factor</span>
                            <span class="text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">Gross W/L</span>
                        </div>
                        <div class="mt-2 text-2xl font-black text-gray-900">
                            {{ number_format((float) $result->profit_factor, 2) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Max DD: <span class="font-bold text-[#e52521]">{{ number_format((float) $result->max_drawdown_percent, 2) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Secondary Metrics Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-gray-500 uppercase">Total Trades</div>
                        <div class="text-lg font-bold text-gray-900 mt-0.5">{{ $result->total_trades }}</div>
                    </div>
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-emerald-700 uppercase">Total Win (TP)</div>
                        <div class="text-lg font-bold text-[#00b050] mt-0.5">{{ $result->total_win }}</div>
                    </div>
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-rose-700 uppercase">Total Loss (SL)</div>
                        <div class="text-lg font-bold text-[#e52521] mt-0.5">{{ $result->total_loss }}</div>
                    </div>
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-gray-500 uppercase">Max Win Streak</div>
                        <div class="text-lg font-bold text-[#00b050] mt-0.5">{{ $result->max_consecutive_win }}</div>
                    </div>
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-gray-500 uppercase">Max Loss Streak</div>
                        <div class="text-lg font-bold text-[#e52521] mt-0.5">{{ $result->max_consecutive_loss }}</div>
                    </div>
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm text-center">
                        <div class="text-[11px] font-semibold text-gray-500 uppercase">Calculated At</div>
                        <div class="text-xs font-semibold text-gray-700 mt-1.5">{{ $result->calculated_at->format('M d, H:i') }}</div>
                    </div>
                </div>

                <!-- Equity Curve Chart (Chart.js) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#00b050]"></span>
                            <h3 class="font-bold text-gray-900 text-lg">Equity Curve</h3>
                        </div>
                        <div class="text-xs text-gray-500">
                            Running balance progression after each trade entry
                        </div>
                    </div>

                    <div class="relative w-full h-[320px]">
                        <canvas id="equityCurveChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Trades Breakdown Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-[#0f5132] text-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <h3 class="font-bold text-base">Trade-by-Trade Performance Log</h3>
                        </div>
                        <span class="text-xs bg-white/10 px-2.5 py-1 rounded text-white/90">
                            {{ $session->trades->count() }} trades logged
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold uppercase tracking-wider">
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
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @foreach ($session->trades as $trade)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3 px-4 font-bold text-gray-900">{{ $trade->sequence_number }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-[#0f5132] border border-emerald-200">
                                                {{ $trade->symbol?->code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($trade->trade_type === 'BUY')
                                                <span class="px-2 py-0.5 rounded font-bold text-[11px] bg-emerald-100 text-emerald-800">BUY</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded font-bold text-[11px] bg-rose-100 text-rose-800">SELL</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">{{ $trade->entry_date->format('Y-m-d') }}</td>
                                        <td class="py-3 px-4 text-right font-semibold text-gray-900">{{ number_format((float) $trade->entry_price, 4) }}</td>
                                        <td class="py-3 px-4 text-right text-rose-600">{{ number_format((float) $trade->stop_loss, 4) }}</td>
                                        <td class="py-3 px-4 text-right text-emerald-600">{{ number_format((float) $trade->take_profit, 4) }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($trade->result_status === 'TP')
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#00b050] text-white">TP</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#e52521] text-white">SL</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-semibold {{ (float) $trade->pips_gained >= 0 ? 'text-[#00b050]' : 'text-[#e52521]' }}">
                                            {{ (float) $trade->pips_gained >= 0 ? '+' : '' }}{{ number_format((float) $trade->pips_gained, 1) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold {{ (float) $trade->profit_loss >= 0 ? 'text-[#00b050]' : 'text-[#e52521]' }}">
                                            {{ (float) $trade->profit_loss >= 0 ? '+$' : '-$' }}{{ number_format(abs((float) $trade->profit_loss), 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-gray-900">
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">No results calculated yet</h3>
                    <p class="text-xs text-gray-500 mt-1">Open the workspace, add your trades, and click "Save & Calculate Results".</p>
                    <div class="mt-4">
                        <a href="{{ route('backtests.workspace', $session) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow-sm">
                            Open Workspace
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Chart.js Equity Curve Script -->
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
                                borderColor: '#00b050',
                                backgroundColor: 'rgba(0, 176, 80, 0.08)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.25,
                                pointRadius: data.length > 50 ? 0 : 3,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#00b050'
                            },
                            {
                                label: 'Initial Modal ($)',
                                data: Array(labels.length).fill(initialCapital),
                                borderColor: '#9ca3af',
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
                                    font: { size: 10 }
                                }
                            },
                            y: {
                                grid: {
                                    color: '#f3f4f6'
                                },
                                ticks: {
                                    font: { size: 10 },
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
                                    font: { size: 11, weight: 'bold' }
                                }
                            },
                            tooltip: {
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
