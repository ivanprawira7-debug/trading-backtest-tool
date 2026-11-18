<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#0f5132]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Backtest Session
                </h1>
                <p class="text-sm text-gray-600 mt-1">Configure your session parameters, initial capital, and default lot size.</p>
            </div>
            <a href="{{ route('backtests.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1">
                ← Back to History
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-[#0f5132] text-white">
                    <h2 class="font-semibold text-lg">Session Configuration</h2>
                    <p class="text-xs text-white/80 mt-0.5">You can adjust active symbols and trades anytime inside the workspace.</p>
                </div>

                <form action="{{ route('backtests.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700">
                            Session Title <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="title"
                            id="title"
                            value="{{ old('title', 'Backtest ' . now()->format('Y-m-d H:i')) }}"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#00b050] focus:ring-[#00b050] text-sm"
                            placeholder="e.g. Gold London Breakout Strategy"
                        />
                        @error('title')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700">
                            Strategy Notes / Description <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#00b050] focus:ring-[#00b050] text-sm"
                            placeholder="e.g. Testing 15m Fair Value Gap on XAUUSD with 50 pips SL & 100 pips TP"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modal & Lot -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="initial_capital" class="block text-sm font-semibold text-gray-700">
                                Modal / Initial Capital ($) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-medium">$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="1"
                                    name="initial_capital"
                                    id="initial_capital"
                                    value="{{ old('initial_capital', '300.00') }}"
                                    required
                                    class="block w-full pl-8 rounded-lg border-gray-300 shadow-sm focus:border-[#00b050] focus:ring-[#00b050] text-sm font-semibold text-gray-900"
                                />
                            </div>
                            @error('initial_capital')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="default_lot" class="block text-sm font-semibold text-gray-700">
                                Default Lot Size <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.001"
                                name="default_lot"
                                id="default_lot"
                                value="{{ old('default_lot', '0.01') }}"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#00b050] focus:ring-[#00b050] text-sm font-semibold text-gray-900"
                            />
                            @error('default_lot')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Select Symbols -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Active Symbols for this Session
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($symbols as $sym)
                                <label class="flex items-center gap-2.5 p-3 rounded-lg border border-gray-200 hover:border-[#00b050] cursor-pointer bg-gray-50/50 has-[:checked]:bg-emerald-50/60 has-[:checked]:border-[#00b050] transition-colors">
                                    <input
                                        type="checkbox"
                                        name="symbols[]"
                                        value="{{ $sym->id }}"
                                        @checked(in_array($sym->id, old('symbols', [$symbols->firstWhere('code', 'XAUUSD')?->id ?? $symbols->first()?->id])))
                                        class="rounded text-[#00b050] focus:ring-[#00b050] border-gray-300 w-4 h-4"
                                    />
                                    <div>
                                        <div class="font-bold text-sm text-gray-900">{{ $sym->code }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $sym->name }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('symbols')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('backtests.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-[#00b050] hover:bg-[#009b46] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00b050]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            Create & Open Workspace
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
