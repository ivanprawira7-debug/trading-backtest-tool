<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-white tracking-tight">
            Welcome Back
        </h2>
        <p class="text-xs text-slate-400 mt-1">
            Sign in to access your trading backtest workspace
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="name@example.com"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')" class="!mb-0" />
                @if (Route::has('password.request'))
                    <a
                        class="text-xs font-semibold text-blue-400 hover:text-blue-300 transition-colors"
                        href="{{ route('password.request') }}"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-text-input
                id="password"
                type="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="w-4 h-4 rounded-lg bg-[#0a1226] border-white/20 text-blue-500 focus:ring-blue-500/30 focus:ring-offset-0 focus:ring-offset-transparent cursor-pointer"
                    name="remember"
                >
                <span class="text-xs text-slate-300 font-medium">{{ __('Remember me on this device') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                class="w-full py-3 px-6 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold text-sm tracking-wide shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <span>{{ __('Log In') }}</span>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>

    <!-- Register Link -->
    <div class="mt-6 pt-5 border-t border-white/10 text-center">
        <p class="text-xs text-slate-400">
            {{ __("Don't have an account yet?") }}
            <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-bold ml-1 transition-colors">
                {{ __('Create an account') }}
            </a>
        </p>
    </div>

    <!-- Quick Demo Credential Helper -->
    <div class="mt-4 p-3 rounded-2xl bg-white/[0.03] border border-white/5 text-center">
        <span class="text-[11px] text-slate-400">
            Demo account: <code class="text-blue-300 font-semibold">test@example.com</code> / <code class="text-blue-300 font-semibold">password</code>
        </span>
    </div>
</x-guest-layout>
