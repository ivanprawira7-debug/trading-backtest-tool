<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            {{ __('Account Profile') }}
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 glass-panel rounded-3xl border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 glass-panel rounded-3xl border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 glass-panel rounded-3xl border border-white/10 shadow-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
