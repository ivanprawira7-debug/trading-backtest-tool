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
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $session->status === 'COMPLETED' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                        {{ $session->status }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1 flex items-center gap-2">
                    {{ $session->title }}
                </h1>
            </div>

            <!-- Top Bar Actions (Sleek pill buttons) -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Download Template Button -->
                <a
                    href="{{ route('backtests.template.download') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full glass-panel-subtle hover:bg-white/10 text-slate-200 text-xs font-semibold border border-white/10 transition-all shadow-sm"
                    title="Download CSV template"
                >
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Template CSV
                </a>

                <!-- Upload Data Trading Button -->
                <button
                    type="button"
                    @click="$dispatch('open-upload-modal')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full glass-panel-subtle hover:bg-white/10 text-slate-200 text-xs font-semibold border border-white/10 transition-all shadow-sm"
                    title="Import trades from CSV"
                >
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload CSV
                </button>

                @if ($session->result)
                    <a
                        href="{{ route('backtests.results', $session) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-lg shadow-blue-500/20 transition-all"
                    >
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                        View Results
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Main Workspace Alpine Component -->
    <div
        x-data="backtestWorkspace({
            csrfToken: '{{ csrf_token() }}',
            updateUrl: '{{ route('backtests.update', $session) }}',
            calculateUrl: '{{ route('backtests.calculate', $session) }}',
            uploadUrl: '{{ route('backtests.upload', $session) }}',
            resultsUrl: '{{ route('backtests.results', $session) }}',
            initialTitle: @js($session->title),
            initialDescription: @js($session->description ?? ''),
            initialCapital: {{ (float) $session->initial_capital }},
            initialLot: {{ (float) $session->default_lot }},
            initialAutoFill: {{ $session->auto_fill_sl_tp ? 'true' : 'false' }},
            initialSlPips: {{ (float) ($session->default_sl_pips ?? 50) }},
            initialTpPips: {{ (float) ($session->default_tp_pips ?? 100) }},
            availableSymbols: @js($availableSymbols),
            selectedSymbolIds: @js($session->symbols->pluck('id')->values()->all()),
            initialTrades: @js($session->trades->map(fn($t) => [
                'id' => $t->id,
                'sequence_number' => (int) $t->sequence_number,
                'symbol_id' => (int) $t->symbol_id,
                'trade_type' => $t->trade_type,
                'entry_date' => $t->entry_date->format('Y-m-d'),
                'entry_price' => (float) $t->entry_price,
                'stop_loss' => (float) $t->stop_loss,
                'take_profit' => (float) $t->take_profit,
                'exit_date' => $t->exit_date->format('Y-m-d'),
                'result_status' => $t->result_status,
                'pips_gained' => $t->pips_gained ? (float) $t->pips_gained : null,
                'profit_loss' => $t->profit_loss ? (float) $t->profit_loss : null,
                'balance_after' => $t->balance_after ? (float) $t->balance_after : null,
            ])->values()->all())
        })"
        x-init="init()"
        class="pb-28 pt-2"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Toast Notification -->
            <div
                x-show="notification.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-3"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-3"
                class="fixed bottom-24 right-5 z-50 max-w-sm p-4 rounded-2xl glass-panel border shadow-2xl text-xs flex items-start gap-3"
                :class="notification.type === 'error' ? 'border-rose-500/30 text-rose-300' : 'border-emerald-500/30 text-emerald-300'"
                style="display: none;"
            >
                <div class="shrink-0 mt-0.5">
                    <template x-if="notification.type === 'error'">
                        <span class="w-5 h-5 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">!</span>
                    </template>
                    <template x-if="notification.type !== 'error'">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                    </template>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-white text-xs" x-text="notification.title"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="notification.message"></p>
                </div>
                <button @click="notification.show = false" class="text-slate-400 hover:text-white">×</button>
            </div>

            <!-- Parameters & Auto Fill Cards (Responsive Grid) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                <!-- Left Column: Modal, Lot & Symbol Tags (7 cols) -->
                <div class="lg:col-span-7 glass-panel rounded-3xl p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400 shadow-[0_0_8px_#60a5fa]"></span>
                            <h2 class="font-bold text-white text-sm">Account & Position Sizing</h2>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Total Trades: <span class="font-bold text-white" x-text="trades.length"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Modal (Initial Capital) -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                Modal / Initial Capital ($)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    x-model.number="initialCapital"
                                    class="glass-input block w-full pl-8 pr-4 py-2.5 rounded-2xl text-sm font-bold text-white"
                                />
                            </div>
                        </div>

                        <!-- Lot Size -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                Default Lot Size
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.001"
                                x-model.number="defaultLot"
                                class="glass-input block w-full px-4 py-2.5 rounded-2xl text-sm font-bold text-white"
                            />
                        </div>
                    </div>

                    <!-- Multi-Symbol Selector with Removable Pill Tags -->
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                Active Session Symbols
                            </label>

                            <!-- Add Symbol Pill Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/20 text-xs font-semibold transition-all"
                                >
                                    <span>+ Add Symbol</span>
                                </button>

                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition
                                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-2xl shadow-2xl bg-[#091124] border border-white/10 z-30 py-1.5 divide-y divide-white/5"
                                    style="display: none;"
                                >
                                    <template x-for="sym in availableSymbols" :key="sym.id">
                                        <button
                                            type="button"
                                            @click="addSymbol(sym); open = false"
                                            class="w-full text-left px-3.5 py-2 text-xs flex items-center justify-between transition-colors"
                                            :class="isSymbolActive(sym.id) ? 'opacity-40 cursor-not-allowed text-slate-500' : 'text-slate-200 hover:bg-white/10 hover:text-white'"
                                            :disabled="isSymbolActive(sym.id)"
                                        >
                                            <span class="font-bold" x-text="sym.code"></span>
                                            <span class="text-[10px] text-slate-400" x-text="isSymbolActive(sym.id) ? 'Active' : sym.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Active Symbols Pill Container (Scrollable on mobile) -->
                        <div class="flex items-center gap-2 p-2 rounded-2xl bg-white/[0.03] border border-white/5 overflow-x-auto no-scrollbar">
                            <template x-for="sym in activeSymbols" :key="sym.id">
                                <div class="shrink-0 inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-full text-xs font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">
                                    <span x-text="sym.code"></span>
                                    <button
                                        type="button"
                                        @click="removeSymbol(sym.id)"
                                        class="w-4 h-4 rounded-full bg-white/10 hover:bg-rose-500 hover:text-white flex items-center justify-center text-[10px] text-slate-300 transition-colors"
                                        title="Remove symbol"
                                    >
                                        ×
                                    </button>
                                </div>
                            </template>

                            <template x-if="activeSymbols.length === 0">
                                <span class="text-xs text-slate-500 italic px-2">No active symbols selected.</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Auto Fill SL & TP Section (5 cols) -->
                <div class="lg:col-span-5 glass-panel rounded-3xl p-5 sm:p-6 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-white/5 pb-3">
                            <div class="flex items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    id="auto_fill_sl_tp"
                                    x-model="autoFillSlTp"
                                    class="rounded-md border-white/20 bg-slate-900/50 text-blue-500 focus:ring-blue-500 focus:ring-offset-0 w-4 h-4"
                                />
                                <label for="auto_fill_sl_tp" class="font-bold text-white text-sm cursor-pointer select-none">
                                    Auto Fill Stop Loss & Take Profit
                                </label>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                                Pip Rule
                            </span>
                        </div>

                        <p class="text-xs text-slate-400 mt-3">
                            Automatically compute Stop Loss & Take Profit prices for each trade card based on Entry Price & BUY/SELL direction.
                        </p>

                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Stop Loss (Pips)
                                </label>
                                <input
                                    type="number"
                                    step="1"
                                    min="1"
                                    x-model.number="slPips"
                                    class="glass-input block w-full px-3.5 py-2 rounded-2xl text-sm font-semibold text-white"
                                    placeholder="50"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Take Profit (Pips)
                                </label>
                                <input
                                    type="number"
                                    step="1"
                                    min="1"
                                    x-model.number="tpPips"
                                    class="glass-input block w-full px-3.5 py-2 rounded-2xl text-sm font-semibold text-white"
                                    placeholder="100"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div class="pt-3 border-t border-white/5 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500" x-text="'Affects ' + trades.length + ' trade(s)'"></span>
                        <button
                            type="button"
                            @click="applyAutoFill()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold shadow-md shadow-blue-500/25 transition-all transform active:scale-95"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Apply Auto Fill
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trade Entries Action Bar -->
            <div class="glass-panel rounded-3xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Trading Entries
                    </h3>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300 font-semibold" x-text="trades.length + ' cards'"></span>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-medium">Sort:</span>
                        <select
                            x-model="sortOption"
                            @change="sortTrades()"
                            class="glass-input text-xs py-1.5 pl-3 pr-7 rounded-full font-medium text-slate-200"
                        >
                            <option value="seq_asc">Sequence (1 → N)</option>
                            <option value="seq_desc">Sequence (N → 1)</option>
                            <option value="date_asc">Date (Oldest First)</option>
                            <option value="date_desc">Date (Newest First)</option>
                        </select>
                    </div>

                    <!-- Add Trade Button -->
                    <button
                        type="button"
                        @click="addTradeRow()"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition-all shadow-sm"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Add Trade
                    </button>
                </div>
            </div>

            <!-- Dynamic Trade Cards List (Mobile-First responsive cards) -->
            <div class="space-y-3.5">
                <template x-for="(trade, index) in trades" :key="trade._uid || (trade.id ? 't-' + trade.id : 'idx-' + index)">
                    <!-- Trade Card -->
                    <div class="glass-panel rounded-3xl p-4 sm:p-5 border border-white/10 hover:border-blue-500/30 transition-all group relative">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                            <!-- Left Section: Sequence badge, Symbol Selector, Trade Type Toggle -->
                            <div class="flex flex-wrap items-center justify-between sm:justify-start gap-3">
                                <div class="flex items-center gap-2.5">
                                    <!-- Sequence Number -->
                                    <div class="w-7 h-7 rounded-full bg-blue-500/15 border border-blue-500/30 flex items-center justify-center font-black text-xs text-blue-300">
                                        <span x-text="index + 1"></span>
                                    </div>

                                    <!-- Symbol Selector Pill -->
                                    <div class="relative" x-data="{ openSymbolMenu: false }">
                                        <button
                                            type="button"
                                            @click="openSymbolMenu = !openSymbolMenu"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-colors"
                                        >
                                            <span x-text="getSymbolCode(trade.symbol_id)"></span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>

                                        <div
                                            x-show="openSymbolMenu"
                                            @click.away="openSymbolMenu = false"
                                            class="origin-top-left absolute left-0 mt-2 w-36 rounded-2xl shadow-2xl bg-[#0b1328] border border-white/10 z-20 py-1 divide-y divide-white/5"
                                            style="display: none;"
                                        >
                                            <template x-for="sym in activeSymbols" :key="sym.id">
                                                <button
                                                    type="button"
                                                    @click="trade.symbol_id = sym.id; openSymbolMenu = false"
                                                    class="w-full text-left px-3.5 py-1.5 text-xs text-slate-200 hover:bg-white/10 hover:text-white flex items-center justify-between"
                                                    :class="trade.symbol_id === sym.id ? 'font-bold bg-white/5 text-blue-400' : ''"
                                                >
                                                    <span x-text="sym.code"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Trade Type Toggle Segmented Control (Buy Green / Sell Red) -->
                                <div class="inline-flex p-1 rounded-full bg-[#070b16] border border-white/10">
                                    <button
                                        type="button"
                                        @click="setTradeType(trade, 'BUY')"
                                        class="px-3.5 py-1 rounded-full text-xs font-bold transition-all"
                                        :class="trade.trade_type === 'BUY'
                                            ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30'
                                            : 'text-slate-400 hover:text-white'"
                                    >
                                        Buy
                                    </button>
                                    <button
                                        type="button"
                                        @click="setTradeType(trade, 'SELL')"
                                        class="px-3.5 py-1 rounded-full text-xs font-bold transition-all"
                                        :class="trade.trade_type === 'SELL'
                                            ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30'
                                            : 'text-slate-400 hover:text-white'"
                                    >
                                        Sell
                                    </button>
                                </div>
                            </div>

                            <!-- Middle Section: Inputs Grid (Entry Date, Entry Price, SL, TP, Exit Date) -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 flex-1 lg:mx-3">
                                <!-- Entry Date -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Entry Date</label>
                                    <input
                                        type="date"
                                        x-model="trade.entry_date"
                                        required
                                        class="glass-input block w-full rounded-xl text-xs py-1.5 px-2.5"
                                    />
                                </div>

                                <!-- Entry Price -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Entry Price</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.entry_price"
                                        @input="onEntryPriceChanged(trade)"
                                        required
                                        class="glass-input block w-full rounded-xl text-xs py-1.5 px-2.5 font-bold text-white"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Stop Loss -->
                                <div>
                                    <label class="block text-[10px] font-bold text-rose-400 uppercase tracking-wider mb-1">Stop Loss</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.stop_loss"
                                        required
                                        class="glass-input block w-full rounded-xl text-xs py-1.5 px-2.5 font-semibold text-rose-300"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Take Profit -->
                                <div>
                                    <label class="block text-[10px] font-bold text-emerald-400 uppercase tracking-wider mb-1">Take Profit</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.take_profit"
                                        required
                                        class="glass-input block w-full rounded-xl text-xs py-1.5 px-2.5 font-semibold text-emerald-300"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Exit Date -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Exit Date</label>
                                    <input
                                        type="date"
                                        x-model="trade.exit_date"
                                        required
                                        class="glass-input block w-full rounded-xl text-xs py-1.5 px-2.5"
                                    />
                                </div>
                            </div>

                            <!-- Right Section: Result Status (TP/SL) & Circular Action Buttons (+ / ×) -->
                            <div class="flex items-center justify-between lg:justify-end gap-3 pt-3 lg:pt-0 border-t lg:border-t-0 border-white/5">
                                <!-- Result Status Segmented Control: TP / SL -->
                                <div class="inline-flex p-1 rounded-full bg-[#070b16] border border-white/10">
                                    <button
                                        type="button"
                                        @click="trade.result_status = 'TP'"
                                        class="px-3 py-1 rounded-full text-xs font-bold transition-all"
                                        :class="trade.result_status === 'TP'
                                            ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30'
                                            : 'text-slate-400 hover:text-white'"
                                    >
                                        TP
                                    </button>
                                    <button
                                        type="button"
                                        @click="trade.result_status = 'SL'"
                                        class="px-3 py-1 rounded-full text-xs font-bold transition-all"
                                        :class="trade.result_status === 'SL'
                                            ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30'
                                            : 'text-slate-400 hover:text-white'"
                                    >
                                        SL
                                    </button>
                                </div>

                                <!-- Circular Action Buttons matching reference screenshot -->
                                <div class="flex items-center gap-1.5">
                                    <!-- Insert Below (+) -->
                                    <button
                                        type="button"
                                        @click="insertTradeBelow(index)"
                                        class="w-8 h-8 rounded-full bg-white text-slate-900 font-extrabold flex items-center justify-center hover:bg-slate-200 transition-all shadow-sm active:scale-90"
                                        title="Add trade row below"
                                    >
                                        +
                                    </button>
                                    <!-- Remove (×) -->
                                    <button
                                        type="button"
                                        @click="removeTradeRow(index)"
                                        class="w-8 h-8 rounded-full bg-white/10 text-slate-300 font-bold flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all active:scale-90"
                                        title="Remove trade row"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="trades.length === 0">
                    <div class="glass-panel rounded-3xl p-12 text-center border border-white/10">
                        <p class="text-sm font-semibold text-white">No trade entries yet</p>
                        <p class="text-xs text-slate-400 mt-1">Start by adding your first trade card or import from CSV.</p>
                        <div class="mt-4 flex items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="addTradeRow()"
                                class="px-5 py-2 rounded-full bg-white text-slate-900 font-bold text-xs hover:bg-slate-100"
                            >
                                + Add First Trade Row
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Sleek Mobile-First Floating Bottom Action Bar -->
            <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[92%] max-w-xl p-2 rounded-full glass-panel border border-white/15 shadow-2xl flex items-center justify-between z-40 backdrop-blur-2xl">
                <div class="flex items-center gap-2 pl-4 text-xs">
                    <span class="w-2 h-2 rounded-full" :class="isDirty ? 'bg-amber-400 animate-ping' : 'bg-emerald-400'"></span>
                    <span class="text-slate-300 font-medium text-[11px]" x-text="isDirty ? 'Unsaved changes' : 'All saved'"></span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Save Draft Button -->
                    <button
                        type="button"
                        @click="saveWorkspace(false)"
                        :disabled="isSaving"
                        class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 text-white text-xs font-semibold border border-white/10 transition-all disabled:opacity-50"
                    >
                        <span x-text="isSaving ? 'Saving...' : 'Save Draft'"></span>
                    </button>

                    <!-- Save & Calculate Button -->
                    <button
                        type="button"
                        @click="calculateAndRedirect()"
                        :disabled="isCalculating || trades.length === 0"
                        class="px-5 py-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95 disabled:opacity-50"
                    >
                        <span x-text="isCalculating ? 'Calculating...' : 'Calculate Results'"></span>
                    </button>
                </div>
            </div>

            <!-- Upload CSV Modal (Frosted Glass) -->
            <div
                x-data="{ showModal: false }"
                @open-upload-modal.window="showModal = true"
                x-show="showModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
            >
                <div class="fixed inset-0 bg-black/75 backdrop-blur-md transition-opacity" @click="showModal = false"></div>

                <div class="min-h-full flex items-center justify-center p-4">
                    <div class="relative bg-[#0c1429] rounded-3xl max-w-md w-full p-6 shadow-2xl border border-white/15">
                        <div class="flex items-center justify-between pb-3 border-b border-white/10">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs">↑</span>
                                Upload Data Trading (CSV)
                            </h3>
                            <button @click="showModal = false" class="text-slate-400 hover:text-white text-lg">×</button>
                        </div>

                        <form @submit.prevent="submitUpload($el); showModal = false" class="mt-4 space-y-4">
                            <p class="text-xs text-slate-400">
                                Import historical trade logs. Supported columns:
                                <code class="text-blue-300 font-semibold">symbol, trade_type, entry_date, entry_price, stop_loss, take_profit, exit_date, result_status</code>.
                            </p>

                            <div class="border-2 border-dashed border-white/15 rounded-2xl p-6 text-center hover:border-blue-500/50 transition-colors">
                                <input
                                    type="file"
                                    name="csv_file"
                                    accept=".csv,text/csv"
                                    required
                                    class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer"
                                />
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('backtests.template.download') }}" class="text-xs text-blue-400 hover:underline font-semibold">
                                    Download Template
                                </a>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showModal = false" class="px-4 py-1.5 rounded-full text-xs font-semibold text-slate-400 hover:text-white">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-5 py-2 rounded-full bg-white text-slate-900 font-bold text-xs hover:bg-slate-100 shadow-md">
                                        Import
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Alpine.js Workspace Engine -->
    <script>
        function backtestWorkspace(config) {
            return {
                csrfToken: config.csrfToken,
                updateUrl: config.updateUrl,
                calculateUrl: config.calculateUrl,
                uploadUrl: config.uploadUrl,
                resultsUrl: config.resultsUrl,

                title: config.initialTitle,
                description: config.initialDescription,
                initialCapital: config.initialCapital,
                defaultLot: config.initialLot,
                autoFillSlTp: config.initialAutoFill,
                slPips: config.initialSlPips,
                tpPips: config.initialTpPips,

                availableSymbols: config.availableSymbols || [],
                activeSymbols: [],
                trades: [],

                sortOption: 'seq_asc',
                isDirty: false,
                isSaving: false,
                isCalculating: false,

                notification: {
                    show: false,
                    type: 'success',
                    title: '',
                    message: ''
                },

                init() {
                    this.activeSymbols = this.availableSymbols.filter(s =>
                        config.selectedSymbolIds.includes(s.id)
                    );

                    if (this.activeSymbols.length === 0 && this.availableSymbols.length > 0) {
                        this.activeSymbols.push(this.availableSymbols[0]);
                    }

                    if (config.initialTrades && config.initialTrades.length > 0) {
                        this.trades = config.initialTrades.map((t, idx) => ({
                            ...t,
                            _uid: 'trade-' + Math.random().toString(36).substr(2, 9),
                            sequence_number: t.sequence_number || (idx + 1)
                        }));
                    } else {
                        this.addTradeRow();
                    }

                    this.$watch('trades', () => { this.isDirty = true; }, { deep: true });
                    this.$watch('initialCapital', () => { this.isDirty = true; });
                    this.$watch('defaultLot', () => { this.isDirty = true; });
                },

                notify(title, message, type = 'success') {
                    this.notification = { show: true, title, message, type };
                    setTimeout(() => { this.notification.show = false; }, 4000);
                },

                isSymbolActive(symbolId) {
                    return this.activeSymbols.some(s => s.id === symbolId);
                },

                addSymbol(symbol) {
                    if (!this.isSymbolActive(symbol.id)) {
                        this.activeSymbols.push(symbol);
                        this.isDirty = true;
                    }
                },

                removeSymbol(symbolId) {
                    if (this.activeSymbols.length <= 1) {
                        this.notify('Warning', 'At least one active symbol is required.', 'error');
                        return;
                    }
                    this.activeSymbols = this.activeSymbols.filter(s => s.id !== symbolId);

                    const fallbackId = this.activeSymbols[0].id;
                    this.trades.forEach(t => {
                        if (t.symbol_id === symbolId) {
                            t.symbol_id = fallbackId;
                        }
                    });

                    this.isDirty = true;
                },

                getSymbol(symbolId) {
                    return this.availableSymbols.find(s => s.id === symbolId) || this.activeSymbols[0] || null;
                },

                getSymbolCode(symbolId) {
                    const s = this.getSymbol(symbolId);
                    return s ? s.code : 'SYMBOL';
                },

                addTradeRow() {
                    const defaultSym = this.activeSymbols[0] || this.availableSymbols[0];
                    const today = new Date().toISOString().split('T')[0];
                    const defaultEntry = defaultSym && defaultSym.code === 'XAUUSD' ? 2000.00 : 1.1000;

                    const newTrade = {
                        _uid: 'trade-' + Math.random().toString(36).substr(2, 9),
                        id: null,
                        sequence_number: this.trades.length + 1,
                        symbol_id: defaultSym ? defaultSym.id : 1,
                        trade_type: 'BUY',
                        entry_date: today,
                        entry_price: defaultEntry,
                        stop_loss: 0,
                        take_profit: 0,
                        exit_date: today,
                        result_status: 'TP'
                    };

                    this.calculateTradePrices(newTrade);
                    this.trades.push(newTrade);
                    this.isDirty = true;
                },

                insertTradeBelow(index) {
                    const currentTrade = this.trades[index];
                    const defaultSym = this.getSymbol(currentTrade.symbol_id) || this.activeSymbols[0];

                    const newTrade = {
                        _uid: 'trade-' + Math.random().toString(36).substr(2, 9),
                        id: null,
                        sequence_number: index + 2,
                        symbol_id: defaultSym ? defaultSym.id : 1,
                        trade_type: currentTrade.trade_type,
                        entry_date: currentTrade.exit_date || currentTrade.entry_date,
                        entry_price: currentTrade.entry_price,
                        stop_loss: currentTrade.stop_loss,
                        take_profit: currentTrade.take_profit,
                        exit_date: currentTrade.exit_date,
                        result_status: 'TP'
                    };

                    if (this.autoFillSlTp) {
                        this.calculateTradePrices(newTrade);
                    }

                    this.trades.splice(index + 1, 0, newTrade);
                    this.resequence();
                    this.isDirty = true;
                },

                removeTradeRow(index) {
                    if (this.trades.length <= 1) {
                        this.notify('Notice', 'Cannot delete the only trade card.', 'error');
                        return;
                    }
                    this.trades.splice(index, 1);
                    this.resequence();
                    this.isDirty = true;
                },

                resequence() {
                    this.trades.forEach((t, i) => {
                        t.sequence_number = i + 1;
                    });
                },

                setTradeType(trade, type) {
                    trade.trade_type = type;
                    if (this.autoFillSlTp) {
                        this.calculateTradePrices(trade);
                    }
                    this.isDirty = true;
                },

                onEntryPriceChanged(trade) {
                    if (this.autoFillSlTp) {
                        this.calculateTradePrices(trade);
                    }
                    this.isDirty = true;
                },

                calculateTradePrices(trade) {
                    const symbol = this.getSymbol(trade.symbol_id);
                    const pipSize = symbol ? parseFloat(symbol.pip_size) : 0.01;
                    const slPips = parseFloat(this.slPips) || 50;
                    const tpPips = parseFloat(this.tpPips) || 100;
                    const entry = parseFloat(trade.entry_price) || 0;

                    const decimals = pipSize < 0.001 ? 5 : 2;

                    if (trade.trade_type === 'BUY') {
                        trade.stop_loss = parseFloat((entry - (slPips * pipSize)).toFixed(decimals));
                        trade.take_profit = parseFloat((entry + (tpPips * pipSize)).toFixed(decimals));
                    } else {
                        trade.stop_loss = parseFloat((entry + (slPips * pipSize)).toFixed(decimals));
                        trade.take_profit = parseFloat((entry - (tpPips * pipSize)).toFixed(decimals));
                    }
                },

                applyAutoFill() {
                    if (this.trades.length === 0) {
                        this.notify('Notice', 'No trades available to apply auto-fill.', 'error');
                        return;
                    }

                    this.autoFillSlTp = true;
                    this.trades.forEach(trade => {
                        this.calculateTradePrices(trade);
                    });

                    this.notify('Success', `Auto-filled SL & TP for ${this.trades.length} trades.`);
                    this.isDirty = true;
                },

                sortTrades() {
                    if (this.sortOption === 'seq_asc') {
                        this.trades.sort((a, b) => a.sequence_number - b.sequence_number);
                    } else if (this.sortOption === 'seq_desc') {
                        this.trades.sort((a, b) => b.sequence_number - a.sequence_number);
                    } else if (this.sortOption === 'date_asc') {
                        this.trades.sort((a, b) => new Date(a.entry_date) - new Date(b.entry_date));
                    } else if (this.sortOption === 'date_desc') {
                        this.trades.sort((a, b) => new Date(b.entry_date) - new Date(a.entry_date));
                    }
                },

                preparePayload() {
                    return {
                        title: this.title,
                        description: this.description,
                        initial_capital: this.initialCapital,
                        default_lot: this.defaultLot,
                        auto_fill_sl_tp: this.autoFillSlTp,
                        default_sl_pips: this.slPips,
                        default_tp_pips: this.tpPips,
                        symbols: this.activeSymbols.map(s => s.id),
                        trades: this.trades.map((t, idx) => ({
                            id: t.id || null,
                            sequence_number: idx + 1,
                            symbol_id: t.symbol_id,
                            trade_type: t.trade_type,
                            entry_date: t.entry_date,
                            entry_price: t.entry_price,
                            stop_loss: t.stop_loss,
                            take_profit: t.take_profit,
                            exit_date: t.exit_date,
                            result_status: t.result_status
                        }))
                    };
                },

                async saveWorkspace(silent = false) {
                    this.isSaving = true;
                    try {
                        const response = await fetch(this.updateUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(this.preparePayload())
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to save trades');
                        }

                        if (data.session && data.session.trades) {
                            data.session.trades.forEach((persistedTrade, i) => {
                                if (this.trades[i]) {
                                    this.trades[i].id = persistedTrade.id;
                                }
                            });
                        }

                        this.isDirty = false;
                        if (!silent) {
                            this.notify('Saved', 'Trading workspace saved successfully.');
                        }
                        return true;
                    } catch (err) {
                        this.notify('Save Error', err.message, 'error');
                        return false;
                    } finally {
                        this.isSaving = false;
                    }
                },

                async calculateAndRedirect() {
                    if (this.trades.length === 0) {
                        this.notify('Error', 'Please add at least one trade before calculating.', 'error');
                        return;
                    }

                    this.isCalculating = true;

                    const saved = await this.saveWorkspace(true);
                    if (!saved) {
                        this.isCalculating = false;
                        return;
                    }

                    try {
                        const response = await fetch(this.calculateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(this.preparePayload())
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || data.message || 'Failed to calculate results');
                        }

                        this.notify('Success', 'Calculations completed! Redirecting...');
                        window.location.href = data.redirect_url || this.resultsUrl;
                    } catch (err) {
                        this.notify('Calculation Error', err.message, 'error');
                        this.isCalculating = false;
                    }
                },

                async submitUpload(formEl) {
                    const formData = new FormData(formEl);

                    try {
                        const response = await fetch(this.uploadUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || data.message || 'Upload failed');
                        }

                        if (data.session && data.session.trades) {
                            this.trades = data.session.trades.map((t, idx) => ({
                                ...t,
                                _uid: 'trade-' + Math.random().toString(36).substr(2, 9),
                                sequence_number: t.sequence_number || (idx + 1)
                            }));

                            if (data.session.symbols) {
                                this.activeSymbols = data.session.symbols;
                            }
                        }

                        this.notify('Import Successful', data.message);
                        this.isDirty = false;
                    } catch (err) {
                        this.notify('Import Error', err.message, 'error');
                    }
                }
            };
        }
    </script>
</x-app-layout>
