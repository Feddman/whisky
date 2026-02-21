<div
    class="mx-auto max-w-4xl space-y-8"
    x-data="{
        participantEmojis: {},
        slainteActive: false,
        slaintePressedCount: 0,
        slainteTotal: 1,
        slainteTimeout: null,
        avatarModalOpen: false,
        avatarModalParticipantId: null,
        avatarModalSeed: null,
        avatarModalSeedName: '',
        avatarSeeds: ['oak','maple','ember','classic','storm','midnight','sunset','moss','pepper','sable','ginger','copper'],
        init() {
            var self = this;
            ['tasting-players-updated','round-started','submission-received','reveal-started','everyone-submitted'].forEach(function(e) {
                window.addEventListener(e, function() { $wire.$refresh(); });
            });
            window.addEventListener('emoji-reaction', function(e) {
                var d = e.detail && e.detail.data ? e.detail.data : (e.detail || {});
                if (d.participantId && d.emoji) self.participantEmojis[d.participantId] = d.emoji;
            });
            window.addEventListener('slainte-pressed', function(e) {
                var d = e.detail && e.detail.data ? e.detail.data : (e.detail || {});
                self.slainteActive = true;
                self.slaintePressedCount = d.pressedCount ?? 0;
                self.slainteTotal = d.total ?? 1;
                if (self.slainteTimeout) clearTimeout(self.slainteTimeout);
                self.slainteTimeout = setTimeout(function() { self.slainteActive = false; }, 3000);
            });
            window.addEventListener('participant-updated', function(e) {
                var d = (e.detail && e.detail.data) ? e.detail.data : (e.detail || {});
                if (! d.participantId) return;
                var el = document.querySelector('[data-participant-id=\u0022' + d.participantId + '\u0022]');
                if (! el) return;
                if (d.avatarSeed !== undefined) {
                    var img = el.querySelector('img[data-avatar-img]');
                    if (img) img.src = 'https://api.dicebear.com/8.x/croodles/svg?seed=' + encodeURIComponent(d.avatarSeed || '');
                }
                if (d.displayName !== undefined) {
                    var nameEl = el.querySelector('[data-participant-name]');
                    if (nameEl) nameEl.textContent = d.displayName;
                }
            });
        },
        openAvatarModalFromEl(el) {
            var d = el.dataset;
            this.avatarModalParticipantId = d.participantId ? parseInt(d.participantId, 10) : null;
            this.avatarModalSeed = d.avatarSeed || '';
            this.avatarModalSeedName = d.displayName || '';
            this.avatarModalOpen = true;
        },
        async selectSeed(seed) {
            if (! this.avatarModalParticipantId) return;
            this.avatarModalSeed = seed;
            await $wire.call('updateParticipant', this.avatarModalParticipantId, null, seed);
        },
        randomizeSeeds() {
            var arr = [];
            for (var i = 0; i < 12; i++) arr.push(Math.random().toString(36).substring(2, 9));
            this.avatarSeeds = arr;
        }
    }"
>
    <style>
        @keyframes emoji-pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(1); opacity: 0; }
        }
        .emoji-floating {
            pointer-events: none;
            animation: emoji-pulse 0.6s ease-in-out 0s 3 forwards;
        }
    </style>

    {{-- Players + emojis + Slàinte (always first so button is visible) --}}
    <section class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Players') }}</flux:heading>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($tastingSession->activeParticipants as $p)
                <div data-participant-id="{{ $p->id }}" x-data="{ emojiVisible:false }" x-init="$watch(() => participantEmojis[{{ $p->id }}], (v) => { if (v) { emojiVisible = true; setTimeout(() => { emojiVisible = false; }, 2000); } })" class="relative flex items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <span x-show="emojiVisible" x-transition class="absolute -top-3 -right-3 text-3xl leading-none emoji-floating" style="z-index:30;"> <span x-text="participantEmojis[{{ $p->id }}] || ''"></span></span>
                    <button type="button" class="shrink-0" data-participant-id="{{ $p->id }}" data-avatar-seed="{{ e($p->avatar_seed ?? $p->display_name) }}" data-display-name="{{ e($p->display_name) }}" x-on:click.prevent="openAvatarModalFromEl($event.currentTarget)">
                        <img data-avatar-img="{{ $p->id }}" src="https://api.dicebear.com/8.x/croodles/svg?seed={{ urlencode($p->avatar_seed ?? $p->display_name) }}" alt="{{ $p->display_name }}" class="w-12 h-12 rounded-md" />
                    </button>
                    <div class="flex-1">
                        <div class="font-medium"><span data-participant-name>{{ $p->display_name }}</span> @if($p->is_host)<span class="text-zinc-500 text-sm">({{ __('Host') }})</span>@endif</div>
                        <div class="text-xs text-zinc-500">{{ $p->total_score }} {{ __('pts') }}</div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @php $current = $this->getCurrentParticipantProperty(); @endphp
                        @if(($current && $current->id === $p->id) || (auth()->check() && auth()->user()->can('update', $tastingSession)))
                            <button type="button" data-participant-id="{{ $p->id }}" data-avatar-seed="{{ e($p->avatar_seed ?? $p->display_name) }}" data-display-name="{{ e($p->display_name) }}" x-on:click.prevent="openAvatarModalFromEl($event.currentTarget)" class="text-xs text-zinc-500 hover:underline">{{ __('join.change') }}</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Avatar picker modal -->
        <div x-show="avatarModalOpen" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="w-full max-w-lg rounded-lg bg-white p-4 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">{{ __('Choose avatar') }}</div>
                    <div class="flex items-center gap-2">
                        <button type="button" x-on:click.prevent="randomizeSeeds()" class="text-sm text-zinc-600 hover:underline">{{ __('Randomize') }}</button>
                        <button type="button" x-on:click.prevent="avatarModalOpen = false" class="text-sm text-zinc-500">{{ __('Close') }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <template x-for="s in avatarSeeds" :key="s">
                        <button type="button" x-on:click.prevent="selectSeed(s)" class="flex items-center justify-center">
                            <img :src="`https://api.dicebear.com/8.x/croodles/svg?seed=${encodeURIComponent(s)}`" :alt="s" :title="s" class="w-16 h-16 rounded-md" />
                            <span class="sr-only" x-text="s"></span>
                        </button>
                    </template>
                </div>

                <div class="mt-4">
                    <label class="block text-sm text-zinc-600 mb-1">{{ __('Your name') }}</label>
                    <input x-model="avatarModalSeedName" type="text" class="w-full rounded-md border px-3 py-2" placeholder="Your display name" />
                    <div class="mt-2 text-right">
                        <button type="button" x-on:click.prevent="avatarModalOpen = false" class="text-sm text-zinc-500 mr-2">{{ __('Close') }}</button>
                        <button type="button" x-on:click.prevent="(async () => { await $wire.call('updateParticipant', avatarModalParticipantId, avatarModalSeedName, avatarModalSeed); avatarModalOpen = false; })()" class="text-sm text-white px-3 py-1 rounded" style="background-color: #2563eb;">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Send an emoji') }}</p>
        <div class="mt-1 flex flex-wrap gap-1">
            @foreach (\App\Livewire\Tasting\SessionRoom::EMOJI_LIST as $emoji)
                <button type="button" wire:click="sendEmoji('{{ $emoji }}')" class="rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-xl transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700" title="{{ $emoji }}">{{ $emoji }}</button>
            @endforeach
        </div>

        <p class="mt-4 text-sm text-zinc-500" x-show="!slainteActive" x-transition>{{ __('Everyone must press within 3 seconds to celebrate!') }}</p>
        <p class="mt-4 text-sm font-medium text-green-600 dark:text-green-400" x-show="slainteActive" x-transition x-cloak style="display: none;">
            <span><span x-text="slaintePressedCount + ' / ' + slainteTotal"></span> {{ __('pressed — press now!') }}</span>
        </p>
        <button
            type="button"
            wire:click="pressSlainte"
            class="mt-3 inline-flex items-center justify-center rounded-lg px-5 py-3 text-base font-semibold text-white shadow-lg"
            style="background-color: #2563eb; min-height: 48px; min-width: 180px;"
            :style="slainteActive ? 'background-color: #16a34a;' : 'background-color: #2563eb;'"
            :class="slainteActive && 'animate-wiggle'"
        >
            {{ __('Slàinte Mhath') }}
        </button>
    </section>

    {{-- All submitted: host starts reveal, others wait --}}
    @if ($tastingSession->status === 'awaiting_reveal')
        <section class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
            @can('update', $tastingSession)
                <flux:heading size="lg">{{ __('Everyone has submitted!') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Start the reveal when ready.') }}</flux:text>
                <button type="button" wire:click="startReveal" class="mt-4 inline-flex items-center justify-center rounded-lg px-5 py-3 text-base font-semibold text-white shadow-lg" style="background-color: #2563eb; min-height: 48px;">
                    {{ __('Reveal in 3, 2, 1…') }}
                </button>
            @else
                <flux:heading size="lg">{{ __('Waiting for host to reveal') }}</flux:heading>
                <flux:text>{{ __('Everyone has submitted. The host will start the reveal.') }}</flux:text>
            @endcan
        </section>
    @endif

    {{-- Round in progress: tasting form or waiting for others --}}
    @if ($tastingSession->status === 'in_progress' && $this->getCurrentRoundProperty())
        @php
            $currentRound = $this->getCurrentRoundProperty();
            $currentParticipant = $this->getCurrentParticipantProperty();
            $hasSubmitted = $currentParticipant && $currentRound->submissions()->where('session_participant_id', $currentParticipant->id)->exists();
            $submissionsCount = $currentRound->submissions()->count();
            $participantsCount = $tastingSession->activeParticipants()->count();
        @endphp
        @if ($hasSubmitted)
            <section class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Waiting for others') }}</flux:heading>
                <flux:text>{{ $submissionsCount }} / {{ $participantsCount }} {{ __('have submitted') }}</flux:text>
            </section>
        @elseif ($currentParticipant)
            <section class="rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Tasting notes') }}</flux:heading>
                <form wire:submit="submitTasting" class="mt-4 space-y-6">
                    @if ($formStep === 1)
                        <div>
                            <flux:input wire:model="tasting_color" :label="__('Color (optional)')" placeholder="e.g. Amber, Gold" />
                            <flux:button type="button" variant="primary" class="mt-4" wire:click="$set('formStep', 2)">{{ __('Next') }}</flux:button>
                        </div>
                    @else
                        <div>
                            <flux:heading size="sm">{{ __('Taste / palate') }}</flux:heading>
                            <flux:text class="text-zinc-500">{{ __('Pick up to :max tags.', ['max' => $tastingSession->max_taste_tags]) }}</flux:text>
                            <div class="mt-3 space-y-4">
                                @foreach ($this->tasteTagsGrouped as $category => $tags)
                                    <div>
                                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $category }}</span>
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach ($tags as $tag)
                                                <label class="inline-flex cursor-pointer items-center gap-1 rounded-full border px-3 py-1.5 text-sm transition dark:border-zinc-600 {{ in_array($tag->slug, $tasting_tags) ? 'border-flux-primary bg-flux-primary/10' : 'border-zinc-300 hover:border-zinc-400 dark:hover:border-zinc-500' }}">
                                                    <input type="checkbox" wire:model="tasting_tags" value="{{ $tag->slug }}" class="sr-only" />
                                                    <span>{{ $tag->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 flex gap-2">
                                <flux:button type="button" variant="ghost" wire:click="$set('formStep', 1)">{{ __('Back') }}</flux:button>
                                <flux:button type="submit" variant="primary">{{ __('Submit') }}</flux:button>
                            </div>
                        </div>
                    @endif
                </form>
            </section>
        @endif
    @endif

    {{-- Reveal + Scoreboard (when round just finished) --}}
    @if ($tastingSession->status === 'round_reveal' && $this->getCurrentRoundProperty())
        @php
            $revealRound = $this->getCurrentRoundProperty();
            $revealDrink = $revealRound->drink;
        @endphp
        <section class="space-y-8">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 text-center opacity-0 transition duration-700 dark:border-zinc-700 dark:bg-zinc-900" x-data x-init="setTimeout(() => $el.classList.add('opacity-100'), 100)">
                <flux:heading size="xl" class="mb-4">{{ __('Reveal') }}</flux:heading>
                @if ($revealDrink->image)
                    <img src="{{ $revealDrink->imageUrl() }}" alt="{{ $revealDrink->name }}" class="mx-auto max-h-64 rounded-lg object-contain" />
                @endif
                <flux:heading size="lg" class="mt-4">{{ $revealDrink->name }}</flux:heading>
                @if ($revealDrink->year || $revealDrink->location)
                    <flux:text class="text-zinc-500">{{ $revealDrink->year }} {{ $revealDrink->location }}</flux:text>
                @endif
                @if ($revealDrink->description)
                    <flux:text class="mt-2 max-w-xl mx-auto">{{ $revealDrink->description }}</flux:text>
                @endif
            </div>
            <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Scoreboard') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('This round') }}: {{ $revealRound->team_total ?? 0 }} {{ __('points') }}</flux:text>
                <ul class="mt-4 space-y-2">
                    @foreach ($tastingSession->activeParticipants as $p)
                        @php
                            $roundPoints = ($revealRound->round_score ?? [])[$p->id] ?? 0;
                        @endphp
                        <li class="flex items-center justify-between rounded-lg bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                            <span>{{ $p->display_name }}</span>
                            <span class="font-medium">{{ $roundPoints }} {{ __('pts') }} ({{ __('total') }}: {{ $p->total_score }})</span>
                        </li>
                    @endforeach
                </ul>
                @can('update', $tastingSession)
                    <flux:button variant="primary" class="mt-6" wire:click="continueToSetup">{{ __('Back to setup / Next round') }}</flux:button>
                @endcan
            </div>
        </section>
    @endif

    {{-- Host: setup drinks (only when session in setup) --}}
    @can('update', $tastingSession)
        @if ($tastingSession->status === 'setup')
        <section>
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('Drinks') }}</flux:heading>
                <flux:button wire:click="$set('showAddDrink', true)" variant="primary" size="sm">{{ __('Add drink') }}</flux:button>
            </div>

            {{-- Join link --}}
            <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text class="font-medium">{{ __('Share this link so others can join') }}</flux:text>
                <div class="mt-2 flex items-center gap-2">
                    <flux:input value="{{ $this->joinUrl }}" readonly class="font-mono text-sm" />
                    <flux:button size="sm" type="button" data-url="{{ $this->joinUrl }}" x-data x-on:click="navigator.clipboard.writeText($el.dataset.url)">{{ __('Copy') }}</flux:button>
                </div>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Or share the code') }}: <strong>{{ $tastingSession->code }}</strong></flux:text>
            </div>

            @if ($showAddDrink || $editing_drink_id)
                <div class="mt-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm">{{ $editing_drink_id ? __('Edit drink') : __('New drink') }}</flux:heading>
                    <form wire:submit="{{ $editing_drink_id ? 'updateDrink' : 'addDrink' }}" class="mt-3 flex flex-col gap-3">
                        <flux:input wire:model="drink_name" :label="__('Name')" required />
                        <flux:input wire:model="drink_year" :label="__('Year')" />
                        <flux:input wire:model="drink_location" :label="__('Location')" />
                        <flux:textarea wire:model="drink_description" :label="__('Description')" rows="3" />
                        <flux:input wire:model="drink_image" type="file" accept="image/*" :label="__('Image')" />
                        <div class="flex gap-2">
                            <flux:button type="submit" variant="primary">{{ $editing_drink_id ? __('Save') : __('Add') }}</flux:button>
                            <flux:button type="button" wire:click="cancelEdit" variant="ghost">{{ __('Cancel') }}</flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <ul class="mt-4 space-y-2">
                @forelse ($tastingSession->drinks as $drink)
                    <li class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div>
                            <span class="font-medium">{{ $drink->name }}</span>
                            @if ($drink->year)
                                <span class="text-zinc-500">({{ $drink->year }})</span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" wire:click="startRound({{ $drink->id }})">{{ __('Start round') }}</flux:button>
                            <flux:button size="sm" wire:click="editDrink({{ $drink->id }})">{{ __('Edit') }}</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="deleteDrink({{ $drink->id }})" wire:confirm="{{ __('Delete this drink?') }}">{{ __('Delete') }}</flux:button>
                        </div>
                    </li>
                @empty
                    <li class="rounded-lg border border-dashed border-zinc-300 p-4 text-center text-zinc-500 dark:border-zinc-600">{{ __('No drinks yet. Add one to get started.') }}</li>
                @endforelse
            </ul>
        </section>
        @endif
    @endcan

    {{-- Reveal countdown overlay: 3, 2, 1 then refresh to show reveal + score --}}
    <div
        x-data="{
            show: false,
            num: 0,
            init() {
                var self = this;
                window.addEventListener('reveal-countdown-started', function() {
                    self.show = true;
                    self.num = 3;
                    var n = 3;
                    var iv = setInterval(function() {
                        n--;
                        self.num = n;
                        if (n <= 0) {
                            clearInterval(iv);
                            setTimeout(function() {
                                self.show = false;
                                $wire.$refresh();
                            }, 500);
                        }
                    }, 1000);
                });
            }
        }"
        x-show="show"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
        style="display: none;"
    >
        <p class="text-9xl font-bold text-white drop-shadow-2xl" x-text="num" x-show="num > 0"></p>
    </div>

    {{-- Slàinte success overlay: confetti + text --}}
    <div
        x-data="{
            show: false,
            init() {
                var self = this;
                window.addEventListener('slainte-success', function() {
                    self.show = true;
                    if (window.confetti) {
                        var end = Date.now() + 3000;
                        (function frame() {
                            window.confetti({ startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999, origin: { x: Math.random(), y: Math.random() - 0.2 } });
                            if (Date.now() < end) requestAnimationFrame(frame);
                        }());
                    }
                    setTimeout(function() { self.show = false; }, 4500);
                });
            }
        }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
        style="display: none;"
    >
        <div class="text-center">
            <p class="text-4xl font-bold text-amber-400 drop-shadow-lg md:text-6xl">Slàinte Mhath</p>
            <p class="mt-2 text-xl text-white/90">🥃</p>
        </div>
    </div>
</div>
