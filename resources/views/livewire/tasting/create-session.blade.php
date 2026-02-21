<div class="mx-auto max-w-md space-y-6">
    <flux:heading size="xl">{{ __('Create a tasting session') }}</flux:heading>
    <flux:text class="text-zinc-500">{{ __('Set a name and how many taste tags players can pick per round.') }}</flux:text>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:input wire:model="name" :label="__('Session name')" placeholder="e.g. Whisky Night" required />
        <flux:input wire:model="max_taste_tags" type="number" min="1" max="10" :label="__('Max taste tags per round')" required />
        <flux:button type="submit" variant="primary">{{ __('Create session') }}</flux:button>
    </form>
</div>
