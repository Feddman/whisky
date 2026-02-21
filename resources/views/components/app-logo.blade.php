@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="{{ config('app.name') }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md  ">
            <x-app-logo-icon class="w-8 h-8" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="w-10 h-10 size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
