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
                    <span class="text-xs px-2 py-0.5 rounded font-semibold {{ $session->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $session->status }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1 flex items-center gap-2">
                    {{ $session->title }}
                </h1>
            </div>

            <!-- Top Bar Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Download Template Button -->
                <a href="{{ route('backtests.template.download') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 shadow-sm transition-colors" title="Download CSV template file">
                    <svg class="w-4 h-4 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>

                <!-- Upload Data Trading Button (Triggers Alpine Modal) -->
                <button type="button" @click="$dispatch('open-upload-modal')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 shadow-sm transition-colors" title="Import trades from CSV">
                    <svg class="w-4 h-4 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Data Trading
                </button>

                @if ($session->result)
                    <a href="{{ route('backtests.results', $session) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#0f5132] hover:bg-[#0c4027] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
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
        class="py-6"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Toast Notification -->
            <div
                x-show="notification.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2"
                class="fixed bottom-5 right-5 z-50 max-w-md p-4 rounded-xl shadow-lg border text-sm flex items-start gap-3"
                :class="notification.type === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800'"
                style="display: none;"
            >
                <div class="shrink-0 mt-0.5">
                    <template x-if="notification.type === 'error'">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="notification.type !== 'error'">
                        <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </template>
                </div>
                <div class="flex-1">
                    <p class="font-semibold" x-text="notification.title"></p>
                    <p class="text-xs mt-0.5" x-text="notification.message"></p>
                </div>
                <button @click="notification.show = false" class="text-gray-400 hover:text-gray-600">×</button>
            </div>

            <!-- Top Grid: Session Parameters & Auto Fill Settings -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Column: Modal, Lot & Symbols (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00b050]"></span>
                            <h2 class="font-bold text-gray-900 text-base">Session Parameters</h2>
                        </div>
                        <div class="text-xs text-gray-500">
                            Total Trades: <span class="font-bold text-gray-800" x-text="trades.length"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Modal (Initial Capital) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Modal (Initial Capital $)
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-semibold">$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    x-model.number="initialCapital"
                                    class="block w-full pl-8 rounded-lg border-gray-300 text-sm font-bold text-gray-900 focus:border-[#00b050] focus:ring-[#00b050]"
                                />
                            </div>
                        </div>

                        <!-- Lot Size -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Default Lot
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.001"
                                x-model.number="defaultLot"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm font-bold text-gray-900 focus:border-[#00b050] focus:ring-[#00b050]"
                            />
                        </div>
                    </div>

                    <!-- Symbol Selection with Green Removable Pill Tags -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Active Symbols
                            </label>
                            <!-- Dropdown to add symbol -->
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="text-xs font-semibold text-[#00b050] hover:text-[#009b46] flex items-center gap-1"
                                >
                                    + Add Symbol
                                </button>
                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    class="origin-top-right absolute right-0 mt-1 w-44 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 py-1"
                                    style="display: none;"
                                >
                                    <template x-for="sym in availableSymbols" :key="sym.id">
                                        <button
                                            type="button"
                                            @click="addSymbol(sym); open = false"
                                            class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100 flex items-center justify-between"
                                            :class="isSymbolActive(sym.id) ? 'opacity-50 cursor-not-allowed' : ''"
                                            :disabled="isSymbolActive(sym.id)"
                                        >
                                            <span class="font-bold" x-text="sym.code"></span>
                                            <span class="text-[10px] text-gray-400" x-text="isSymbolActive(sym.id) ? 'Added' : sym.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Active Tags List -->
                        <div class="flex flex-wrap items-center gap-2 min-h-[38px] p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <template x-for="sym in activeSymbols" :key="sym.id">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#00b050] text-white shadow-sm transition-all">
                                    <span x-text="sym.code"></span>
                                    <button
                                        type="button"
                                        @click="removeSymbol(sym.id)"
                                        class="hover:bg-white/20 rounded-full w-4 h-4 inline-flex items-center justify-center text-white"
                                        title="Remove symbol"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>

                            <template x-if="activeSymbols.length === 0">
                                <span class="text-xs text-gray-400 italic">No symbols selected. Add at least one symbol.</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Auto Fill SL & TP Section (#0f5132 dark green header) (5 cols) -->
                <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
                    <div>
                        <!-- Dark Green Header per specification -->
                        <div class="px-5 py-3.5 bg-[#0f5132] text-white flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="auto_fill_sl_tp"
                                    x-model="autoFillSlTp"
                                    class="rounded border-white/30 text-[#00b050] focus:ring-[#00b050] w-4 h-4 bg-white/10"
                                />
                                <label for="auto_fill_sl_tp" class="font-bold text-sm select-none cursor-pointer">
                                    Auto Fill Stop Loss & Take Profit
                                </label>
                            </div>
                            <span class="text-[10px] uppercase tracking-wider bg-white/10 px-2 py-0.5 rounded text-white/90">
                                Pips Rule
                            </span>
                        </div>

                        <!-- Auto Fill Inputs Body -->
                        <div class="p-5 space-y-4">
                            <p class="text-xs text-gray-500">
                                Automatically calculate Stop Loss & Take Profit prices for all trade cards based on Entry Price & Trade Type (BUY/SELL).
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">
                                        Stop Loss (Pips)
                                    </label>
                                    <input
                                        type="number"
                                        step="1"
                                        min="1"
                                        x-model.number="slPips"
                                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm font-semibold focus:border-[#00b050] focus:ring-[#00b050]"
                                        placeholder="50"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700">
                                        Take Profit (Pips)
                                    </label>
                                    <input
                                        type="number"
                                        step="1"
                                        min="1"
                                        x-model.number="tpPips"
                                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm font-semibold focus:border-[#00b050] focus:ring-[#00b050]"
                                        placeholder="100"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500" x-text="'Applies to ' + trades.length + ' trade(s)'"></span>
                        <button
                            type="button"
                            @click="applyAutoFill()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#00b050]"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Apply Auto Fill
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trade Rows Action Bar & Sorting -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Trading Entries
                    </h3>
                    <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-semibold" x-text="trades.length + ' trades'"></span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500 font-medium">Sort By:</span>
                        <select
                            x-model="sortOption"
                            @change="sortTrades()"
                            class="rounded-lg border-gray-300 text-xs py-1.5 pl-2 pr-7 font-medium text-gray-700 focus:border-[#00b050] focus:ring-[#00b050]"
                        >
                            <option value="seq_asc">Sequence (1 → N)</option>
                            <option value="seq_desc">Sequence (N → 1)</option>
                            <option value="date_asc">Entry Date (Oldest First)</option>
                            <option value="date_desc">Entry Date (Newest First)</option>
                        </select>
                    </div>

                    <!-- Add Trade Button -->
                    <button
                        type="button"
                        @click="addTradeRow()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0f5132] hover:bg-[#0c4027] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Trade
                    </button>
                </div>
            </div>

            <!-- Dynamic Trade Cards List -->
            <div class="space-y-3">
                <template x-for="(trade, index) in trades" :key="trade._uid || (trade.id ? 't-' + trade.id : 'idx-' + index)">
                    <!-- Trade Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 transition-all hover:border-gray-300 hover:shadow">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                            <!-- Left Section: Sequence, Symbol Badge & Type Toggle -->
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Sequence Number -->
                                <span class="font-bold text-gray-900 text-base min-w-[28px]" x-text="(index + 1) + '.'"></span>

                                <!-- Symbol Badge / Selector -->
                                <div class="relative" x-data="{ openSymbolMenu: false }">
                                    <button
                                        type="button"
                                        @click="openSymbolMenu = !openSymbolMenu"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-[#0f5132] border border-emerald-300 hover:bg-emerald-100 transition-colors"
                                    >
                                        <span x-text="getSymbolCode(trade.symbol_id)"></span>
                                        <svg class="w-3 h-3 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <!-- Symbol Selector Dropdown -->
                                    <div
                                        x-show="openSymbolMenu"
                                        @click.away="openSymbolMenu = false"
                                        class="origin-top-left absolute left-0 mt-1 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 py-1"
                                        style="display: none;"
                                    >
                                        <template x-for="sym in activeSymbols" :key="sym.id">
                                            <button
                                                type="button"
                                                @click="trade.symbol_id = sym.id; openSymbolMenu = false"
                                                class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-[#0f5132] flex items-center justify-between"
                                                :class="trade.symbol_id === sym.id ? 'font-bold bg-gray-50' : ''"
                                            >
                                                <span x-text="sym.code"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Trade Type Toggle: Buy (Green #00b050) & Sell (Red #e52521) -->
                                <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-100">
                                    <!-- BUY Button -->
                                    <button
                                        type="button"
                                        @click="setTradeType(trade, 'BUY')"
                                        class="px-3 py-1 rounded-md text-xs font-bold transition-all"
                                        :class="trade.trade_type === 'BUY'
                                            ? 'bg-[#00b050] text-white shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'"
                                    >
                                        Buy
                                    </button>
                                    <!-- SELL Button -->
                                    <button
                                        type="button"
                                        @click="setTradeType(trade, 'SELL')"
                                        class="px-3 py-1 rounded-md text-xs font-bold transition-all"
                                        :class="trade.trade_type === 'SELL'
                                            ? 'bg-[#e52521] text-white shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'"
                                    >
                                        Sell
                                    </button>
                                </div>
                            </div>

                            <!-- Middle Section: Inputs Grid (Entry Date, Entry Price, Stop Loss, Take Profit, Exit Date) -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 flex-1 lg:mx-4">
                                <!-- Entry Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Entry Date</label>
                                    <input
                                        type="date"
                                        x-model="trade.entry_date"
                                        required
                                        class="block w-full rounded-md border-gray-300 text-xs py-1.5 px-2 focus:border-[#00b050] focus:ring-[#00b050]"
                                    />
                                </div>

                                <!-- Entry Price -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Entry Price</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.entry_price"
                                        @input="onEntryPriceChanged(trade)"
                                        required
                                        class="block w-full rounded-md border-gray-300 text-xs py-1.5 px-2 font-semibold text-gray-900 focus:border-[#00b050] focus:ring-[#00b050]"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Stop Loss -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Stop Loss</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.stop_loss"
                                        required
                                        class="block w-full rounded-md border-gray-300 text-xs py-1.5 px-2 font-medium text-rose-700 focus:border-[#00b050] focus:ring-[#00b050]"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Take Profit -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Take Profit</label>
                                    <input
                                        type="number"
                                        step="0.00001"
                                        x-model.number="trade.take_profit"
                                        required
                                        class="block w-full rounded-md border-gray-300 text-xs py-1.5 px-2 font-medium text-emerald-700 focus:border-[#00b050] focus:ring-[#00b050]"
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Exit Date -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Exit Date</label>
                                    <input
                                        type="date"
                                        x-model="trade.exit_date"
                                        required
                                        class="block w-full rounded-md border-gray-300 text-xs py-1.5 px-2 focus:border-[#00b050] focus:ring-[#00b050]"
                                    />
                                </div>
                            </div>

                            <!-- Right Section: Result Status (SL/TP) & Action Buttons (+ / x) -->
                            <div class="flex items-center justify-between lg:justify-end gap-3 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                                <!-- Result Status Toggle: TP (Green #00b050) & SL (Red #e52521) -->
                                <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-100">
                                    <!-- TP Button -->
                                    <button
                                        type="button"
                                        @click="trade.result_status = 'TP'"
                                        class="px-2.5 py-1 rounded-md text-xs font-bold transition-all"
                                        :class="trade.result_status === 'TP'
                                            ? 'bg-[#00b050] text-white shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'"
                                    >
                                        TP
                                    </button>
                                    <!-- SL Button -->
                                    <button
                                        type="button"
                                        @click="trade.result_status = 'SL'"
                                        class="px-2.5 py-1 rounded-md text-xs font-bold transition-all"
                                        :class="trade.result_status === 'SL'
                                            ? 'bg-[#e52521] text-white shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'"
                                    >
                                        SL
                                    </button>
                                </div>

                                <!-- Card Actions: Add row below (+) and Remove row (x) -->
                                <div class="inline-flex items-center gap-1">
                                    <!-- Add Row Below Button -->
                                    <button
                                        type="button"
                                        @click="insertTradeBelow(index)"
                                        class="w-7 h-7 rounded-md bg-gray-100 hover:bg-[#00b050] hover:text-white text-gray-600 text-sm font-bold flex items-center justify-center transition-colors"
                                        title="Add trade row below"
                                    >
                                        +
                                    </button>
                                    <!-- Remove Row Button -->
                                    <button
                                        type="button"
                                        @click="removeTradeRow(index)"
                                        class="w-7 h-7 rounded-md bg-gray-100 hover:bg-[#e52521] hover:text-white text-gray-500 text-sm font-bold flex items-center justify-center transition-colors"
                                        title="Remove trade row"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State within Cards List -->
                <template x-if="trades.length === 0">
                    <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h4 class="text-base font-semibold text-gray-800">No trade entries yet</h4>
                        <p class="text-xs text-gray-500 mt-1">Add your first trade card or upload historical trading data via CSV.</p>
                        <div class="mt-4 flex items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="addTradeRow()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow-sm"
                            >
                                + Add First Trade Row
                            </button>
                            <button
                                type="button"
                                @click="$dispatch('open-upload-modal')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg"
                            >
                                Upload CSV
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Bottom Floating Action Bar -->
            <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="inline-block w-2 h-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
                    <span x-text="isDirty ? 'Unsaved changes in workspace' : 'All changes saved.'"></span>
                </div>

                <div class="flex items-center gap-3 justify-end">
                    <!-- Save Draft Button -->
                    <button
                        type="button"
                        @click="saveWorkspace(false)"
                        :disabled="isSaving"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg transition-colors disabled:opacity-50"
                    >
                        <svg x-show="!isSaving" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span x-text="isSaving ? 'Saving...' : 'Save Draft'"></span>
                    </button>

                    <!-- Save & Calculate Button (Bright Green #00b050) -->
                    <button
                        type="button"
                        @click="calculateAndRedirect()"
                        :disabled="isCalculating || trades.length === 0"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00b050] disabled:opacity-50"
                    >
                        <svg x-show="!isCalculating" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span x-text="isCalculating ? 'Calculating...' : 'Save & Calculate Results'"></span>
                    </button>
                </div>
            </div>

            <!-- Upload CSV Modal (Alpine) -->
            <div
                x-data="{ showModal: false, uploading: false }"
                @open-upload-modal.window="showModal = true"
                x-show="showModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

                <div class="min-h-full flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#00b050]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload Data Trading (CSV)
                            </h3>
                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-lg">×</button>
                        </div>

                        <form @submit.prevent="submitUpload($el); showModal = false" class="mt-4 space-y-4">
                            <p class="text-xs text-gray-600">
                                Select a CSV file formatted according to the template. Columns required:
                                <code class="text-[#0f5132] font-semibold bg-emerald-50 px-1 py-0.5 rounded">symbol, trade_type, entry_date, entry_price, stop_loss, take_profit, exit_date, result_status</code>.
                            </p>

                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#00b050] transition-colors">
                                <input
                                    type="file"
                                    name="csv_file"
                                    accept=".csv,text/csv"
                                    required
                                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-[#0f5132] hover:file:bg-emerald-100 cursor-pointer"
                                />
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('backtests.template.download') }}" class="text-xs text-[#00b050] hover:underline font-semibold flex items-center gap-1">
                                    Download Sample CSV
                                </a>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showModal = false" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-1.5 bg-[#00b050] hover:bg-[#009b46] text-white text-xs font-bold rounded-lg shadow-sm">
                                        Import Trades
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Alpine.js Workspace Logic -->
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
                    // Populate active symbols
                    this.activeSymbols = this.availableSymbols.filter(s =>
                        config.selectedSymbolIds.includes(s.id)
                    );

                    if (this.activeSymbols.length === 0 && this.availableSymbols.length > 0) {
                        this.activeSymbols.push(this.availableSymbols[0]);
                    }

                    // Populate trades
                    if (config.initialTrades && config.initialTrades.length > 0) {
                        this.trades = config.initialTrades.map((t, idx) => ({
                            ...t,
                            _uid: 'trade-' + Math.random().toString(36).substr(2, 9),
                            sequence_number: t.sequence_number || (idx + 1)
                        }));
                    } else {
                        // Start with one clean trade card
                        this.addTradeRow();
                    }

                    // Watch changes for dirty state
                    this.$watch('trades', () => { this.isDirty = true; }, { deep: true });
                    this.$watch('initialCapital', () => { this.isDirty = true; });
                    this.$watch('defaultLot', () => { this.isDirty = true; });
                },

                notify(title, message, type = 'success') {
                    this.notification = {
                        show: true,
                        title,
                        message,
                        type
                    };
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 4000);
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

                    // Reassign trades that used this symbol to the first remaining symbol
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

                        // Update trade IDs with persisted ones
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

                    // First save the current trades state
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

                        // Refresh workspace with imported session
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
