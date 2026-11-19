<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-[#080e1e]/80 backdrop-blur-xl border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <a href="{{ route('backtests.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 p-[1px] shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/40 transition-all">
                        <div class="w-full h-full bg-[#091124] rounded-2xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-base tracking-tight text-white flex items-center gap-1">
                            Backtest<span class="text-blue-400">Tool</span>
                        </span>
                        <span class="text-[9px] font-semibold uppercase tracking-widest text-slate-400 -mt-1">
                            Trading Analytics
                        </span>
                    </div>
                </a>

                <!-- Desktop Navigation Pills -->
                <div class="hidden sm:flex items-center gap-1.5 p-1 rounded-full bg-white/5 border border-white/5">
                    <a
                        href="{{ route('backtests.index') }}"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 {{ request()->routeIs('backtests.index') || request()->routeIs('dashboard') ? 'bg-white text-slate-900 shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                    >
                        <svg class="w-3.5 h-3.5 {{ request()->routeIs('backtests.index') || request()->routeIs('dashboard') ? 'text-slate-900' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        History
                    </a>

                    <a
                        href="{{ route('backtests.create') }}"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 {{ request()->routeIs('backtests.create') ? 'bg-white text-slate-900 shadow-md' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                    >
                        <svg class="w-3.5 h-3.5 {{ request()->routeIs('backtests.create') ? 'text-slate-900' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Backtest
                    </a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <div class="relative" x-data="{ userMenu: false }">
                    <button
                        @click="userMenu = !userMenu"
                        class="inline-flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-full bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all text-xs font-medium text-slate-200"
                    >
                        <span>{{ Auth::user()->name }}</span>
                        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white font-bold flex items-center justify-center text-[10px]">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </button>

                    <div
                        x-show="userMenu"
                        @click.away="userMenu = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-2xl shadow-2xl bg-[#0b1328]/95 backdrop-blur-xl border border-white/10 py-1.5 z-50 divide-y divide-white/5"
                        style="display: none;"
                    >
                        <div class="px-4 py-2 text-[11px] text-slate-400">
                            Signed in as <br><span class="font-bold text-white text-xs truncate block">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                                Profile Settings
                            </a>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center sm:hidden">
                <button
                    @click="open = !open"
                    class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-white hover:bg-white/10 transition-all"
                >
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="sm:hidden border-b border-white/10 bg-[#080e1e]/95 backdrop-blur-2xl px-4 py-3 space-y-2"
        style="display: none;"
    >
        <a
            href="{{ route('backtests.index') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('backtests.index') || request()->routeIs('dashboard') ? 'bg-white text-slate-900 font-bold' : 'text-slate-300 hover:bg-white/5' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Backtest History
        </a>

        <a
            href="{{ route('backtests.create') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('backtests.create') ? 'bg-white text-slate-900 font-bold' : 'text-slate-300 hover:bg-white/5' }}"
        >
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Backtest Session
        </a>

        <div class="pt-3 border-t border-white/10">
            <div class="px-4 py-1 text-xs text-slate-400">
                Logged in as <span class="font-bold text-white">{{ Auth::user()->name }}</span>
            </div>
            <div class="mt-2 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 rounded-lg text-xs text-slate-300 hover:bg-white/5">
                    Profile Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-xs text-rose-400 hover:bg-rose-500/10 font-semibold">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
