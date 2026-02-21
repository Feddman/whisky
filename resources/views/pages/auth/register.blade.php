<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('auth.create_account')" :description="__('auth.enter_details')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('auth.name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('auth.full_name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('auth.email_address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('auth.password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('auth.password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('auth.confirm_password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('auth.confirm_password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('auth.create_account_button') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('auth.already_have') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('auth.log_in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
