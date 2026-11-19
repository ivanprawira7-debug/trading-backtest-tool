<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-xs font-semibold text-blue-400 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Session Setup
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    New Backtest Session
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Configure your strategy parameters, initial modal, and default lot size.</p>
            </div>
            <a href="{{ route('backtests.index') }}" class="text-xs text-slate-400 hover:text-white font-semibold flex items-center gap-1">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="pb-16 pt-2">
        <div class="max-w-xl mx-auto px-4 sm:px-6">
            <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                <!-- Atmospheric Top Glow -->
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-80 h-40 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <form action="{{ route('backtests.store') }}" method="POST" class="space-y-5 relative z-10">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Session Title <span class="text-rose-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="title"
                            id="title"
                            value="{{ old('title', 'Backtest ' . now()->format('Y-m-d H:i')) }}"
                            required
                            class="glass-input block w-full px-4 py-2.5 rounded-2xl text-sm font-semibold text-white"
                            placeholder="e.g. Gold London Breakout Strategy"
                        />
                        @error('title')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Strategy Notes <span class="text-slate-500 font-normal">(Optional)</span>
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            class="glass-input block w-full px-4 py-2.5 rounded-2xl text-xs text-slate-200"
                            placeholder="e.g. 15m Fair Value Gap on XAUUSD with 50 pips SL & 100 pips TP"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modal & Lot -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="initial_capital" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                Modal / Initial Capital ($) <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    name="initial_capital"
                                    id="initial_capital"
                                    value="{{ old('initial_capital', '300.00') }}"
                                    required
                                    class="glass-input block w-full pl-8 pr-4 py-2.5 rounded-2xl text-sm font-bold text-white"
                                />
                            </div>
                            @error('initial_capital')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="default_lot" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                Default Lot Size <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.001"
                                name="default_lot"
                                id="default_lot"
                                value="{{ old('default_lot', '0.01') }}"
                                required
                                class="glass-input block w-full px-4 py-2.5 rounded-2xl text-sm font-bold text-white"
                            />
                            @error('default_lot')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Active Symbols -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                            Select Active Symbols
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach ($symbols as $sym)
                                <label class="flex items-center gap-2.5 p-3 rounded-2xl glass-panel-subtle hover:border-blue-500/40 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-500/10 transition-all">
                                    <input
                                        type="checkbox"
                                        name="symbols[]"
                                        value="{{ $sym->id }}"
                                        @checked(in_array($sym->id, old('symbols', [$symbols->firstWhere('code', 'XAUUSD')?->id ?? $symbols->first()?->id])))
                                        class="rounded-md border-white/20 bg-slate-900/50 text-blue-500 focus:ring-blue-500 w-4 h-4"
                                    />
                                    <div>
                                        <div class="font-bold text-xs text-white">{{ $sym->code }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[90px]">{{ $sym->name }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('symbols')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="pt-5 border-t border-white/10 flex items-center justify-end gap-3">
                        <a href="{{ route('backtests.index') }}" class="px-5 py-2 rounded-full text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                            Cancel
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95"
                        >
                            <span>Open Workspace</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
