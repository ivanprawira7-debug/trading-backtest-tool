<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 glass-panel-subtle hover:bg-white/10 text-slate-200 border border-white/10 font-bold text-xs uppercase tracking-wider rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-white/20 active:scale-95 transition-all duration-200 cursor-pointer disabled:opacity-50']) }}>
    {{ $slot }}
</button>
