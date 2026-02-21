<div class="space-y-6">
    <flux:heading size="xl" class="mt-2">{{ __('join.title') }}</flux:heading>
    <flux:text class="text-zinc-500">{{ __('join.subtitle') }}</flux:text>

    <form wire:submit="join" class="flex flex-col gap-4">
        <flux:input wire:model="code" :label="__('join.code_label')" placeholder="e.g. ABC123" maxlength="6" class="uppercase" required />
        <flux:input wire:model="display_name" :label="__('join.name_label')" placeholder="How others see you" required />
        <flux:button type="submit" variant="primary">{{ __('join.join_button') }}</flux:button>
    </form>
</div>
