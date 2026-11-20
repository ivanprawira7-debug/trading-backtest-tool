@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'glass-input w-full px-4 py-2.5 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/25 transition-all duration-200 shadow-inner']) }}>
