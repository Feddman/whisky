<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex justify-end">
            <flux:button :href="route('tasting.create')" variant="primary" wire:navigate>{{ __('Create tasting session') }}</flux:button>
        </div>

        @php
            $sessions = auth()->check() ? auth()->user()->tastingSessions()->withCount('participants')->orderBy('created_at', 'desc')->get() : collect();
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($sessions as $session)
                <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="text-sm text-zinc-500">{{ $session->created_at->diffForHumans() }}</div>
                            <h3 class="mt-1 font-semibold text-lg text-zinc-900 dark:text-zinc-100">{{ $session->name ?: __('Untitled session') }}</h3>
                            <div class="mt-2 text-xs text-zinc-500">{{ __('Code') }}: <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $session->code }}</span></div>
                            <div class="mt-1 text-xs text-zinc-500">{{ __('Participants') }}: <span class="font-medium">{{ $session->participants_count }}</span></div>
                            <div class="mt-1 text-xs text-zinc-500">{{ __('Status') }}: <span class="font-medium">{{ $session->status }}</span></div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <a href="{{ route('tasting.show', $session) }}" class="inline-flex items-center px-3 py-2 rounded-md bg-[#F8B803] dark:bg-[#F0ACB8] text-neutral-900 font-semibold">Open</a>
                            <a href="{{ route('tasting.leave', $session) }}" class="text-xs text-zinc-400 hover:underline">Leave</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 text-center">
                    <h3 class="font-semibold text-lg mb-2">{{ __('You have no tasting sessions yet') }}</h3>
                    <p class="text-sm text-zinc-500 mb-4">{{ __('Create your first tasting session to get started.') }}</p>
                    <flux:button :href="route('tasting.create')" variant="primary" wire:navigate>{{ __('Create tasting session') }}</flux:button>
                </div>
            @endforelse
        </div>

    </div>
</x-layouts::app>
