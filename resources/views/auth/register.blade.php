<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold text-white tracking-tight">
            Create Account
        </h2>
        <p class="text-xs text-slate-400 mt-1">
            Start backtesting your trading strategies with ease
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                placeholder="John Doe"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

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
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                placeholder="Minimum 8 characters"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="Repeat your password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                class="w-full py-3 px-6 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold text-sm tracking-wide shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <span>{{ __('Register Account') }}</span>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>

    <!-- Login Link -->
    <div class="mt-6 pt-5 border-t border-white/10 text-center">
        <p class="text-xs text-slate-400">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-bold ml-1 transition-colors">
                {{ __('Sign in here') }}
            </a>
        </p>
    </div>
</x-guest-layout>
