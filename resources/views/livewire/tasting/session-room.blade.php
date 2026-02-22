<div
    class="mx-auto max-w-4xl space-y-8"
    x-data="{
        participantEmojis: {},
        showEmojiPicker: false,
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

    {{-- Informational banner shown at the start when session is in setup --}}
    @if($tastingSession->status === 'setup')
        <div class="rounded-md bg-amber-50  p-3 text-center text-sm text-amber-800 ">
            {{ __('session.wait_host_prepare') }}
        </div>
    @endif

    {{-- Players + emojis + Slàinte (always first so button is visible) --}}
    <section class="rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 p-4  ">
        <flux:heading size="lg">{{ __('session.players') }}</flux:heading>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($tastingSession->activeParticipants as $p)
                <div data-participant-id="{{ $p->id }}" x-data="{ emojiVisible:false }" x-init="$watch(() => participantEmojis[{{ $p->id }}], (v) => { if (v) { emojiVisible = true; setTimeout(() => { emojiVisible = false; }, 2000); } })" class="relative flex items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3">
                    <span x-show="emojiVisible" x-transition class="absolute -top-3 -right-3 text-3xl leading-none emoji-floating" style="z-index:30;"> <span x-text="participantEmojis[{{ $p->id }}] || ''"></span></span>
                    <button type="button" class="shrink-0" data-participant-id="{{ $p->id }}" data-avatar-seed="{{ e($p->avatar_seed ?? $p->display_name) }}" data-display-name="{{ e($p->display_name) }}" x-on:click.prevent="openAvatarModalFromEl($event.currentTarget)">
                        <img data-avatar-img="{{ $p->id }}" src="https://api.dicebear.com/8.x/croodles/svg?seed={{ urlencode($p->avatar_seed ?? $p->display_name) }}" alt="{{ $p->display_name }}" class="w-12 h-12 rounded-md" />
                    </button>
                    <div class="flex-1">
                        <div class="font-medium"><span data-participant-name class="text-zinc-800 dark:text-neutral-500">{{ $p->display_name }}</span> @if($p->is_host)<span class="text-zinc-500 text-sm">({{ __('Host') }})</span>@endif</div>
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
            <div class="w-full max-w-lg rounded-lg bg-white p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">{{ __('session.choose_avatar') }}</div>
                    <div class="flex items-center gap-2">
                        <button type="button" x-on:click.prevent="avatarModalOpen = false" class="text-sm text-zinc-500">{{ __('session.close') }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <template x-for="s in avatarSeeds" :key="s">
                        <button
                            type="button"
                            x-on:click.prevent="selectSeed(s)"
                            :aria-pressed="(avatarModalSeed === s) ? 'true' : 'false'"
                            class="relative p-0"
                        >
                            <img
                                :src="`https://api.dicebear.com/8.x/croodles/svg?seed=${encodeURIComponent(s)}`"
                                :alt="s"
                                :title="s"
                                class="w-16 h-16"
                                :class="avatarModalSeed === s ? 'rounded-md ring-4 ring-flux-primary/60' : 'rounded-md hover:opacity-90'"
                            />
                            <span class="sr-only" x-text="s"></span>
                            <span x-show="avatarModalSeed === s" x-cloak class="absolute -top-2 -right-2 bg-sky-600 hover:bg-sky-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">✓</span>
                        </button>
                    </template>
                </div>

                <!-- centered round shuffle button -->
                <div class="mt-4 flex justify-center">
                    <button type="button" x-on:click.prevent="randomizeSeeds()" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200" aria-label="{{ __('session.randomize') }}" title="{{ __('session.randomize') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 0-9 9" />
                            <polyline points="21 3 21 9 15 9" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <label class="block text-sm text-zinc-600 mb-1">{{ __('session.your_name') }}</label>
                    <input x-model="avatarModalSeedName" type="text" class="w-full rounded-md border px-3 py-2" placeholder="Your display name" />
                    <div class="mt-2 text-right">
                        <button type="button" x-on:click.prevent="avatarModalOpen = false" class="text-sm text-zinc-500 mr-2">{{ __('session.close') }}</button>
                        <button type="button" x-on:click.prevent="(async () => { await $wire.call('updateParticipant', avatarModalParticipantId, avatarModalSeedName, avatarModalSeed); avatarModalOpen = false; })()" class="text-sm text-white px-3 py-1 rounded" style="background-color: #2563eb;">{{ __('session.save') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div class="flex items-center justify-end">
                <button
                    type="button"
                    x-on:click="showEmojiPicker = !showEmojiPicker"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-sm font-medium text-zinc-700 hover:bg-zinc-100"
                    aria-pressed="false"
                >
                    <span class="text-lg leading-none">😊</span>
                    <span>{{ __('session.emoji') }}</span>
                </button>
            </div>

            <div class="mt-1 flex flex-wrap gap-1" x-show="showEmojiPicker" x-cloak x-transition>
                @foreach (\App\Livewire\Tasting\SessionRoom::EMOJI_LIST as $emoji)
                    <button type="button" x-on:click="showEmojiPicker = false" wire:click="sendEmoji('{{ $emoji }}')" class="rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-xl transition hover:bg-zinc-100" title="{{ $emoji }}">{{ $emoji }}</button>
                @endforeach
            </div>
        </div>

        <p class="mt-4 text-sm font-medium text-green-600" x-show="slainteActive" x-transition x-cloak style="display: none;">
            <span><span x-text="slaintePressedCount + ' / ' + slainteTotal"></span> {{ __('session.pressed_press_now') }}</span>
        </p>
        <div class="mt-4 flex justify-center">
            <button
                type="button"
                wire:click="pressSlainte"
                class="inline-flex items-center justify-center gap-3 rounded-none px-6 py-3 text-lg font-extrabold text-neutral-900 shadow-2xl transform transition-transform duration-150 hover:scale-105"
                style="min-height:54px; min-width:220px; background-image: linear-gradient(135deg,#F8B803 0%,#FFCD55 100%);"
                :class="slainteActive ? 'animate-wiggle ring-4 ring-amber-300/40' : ''"
                aria-pressed="false"
                title="{{ __('Slàinte Mhath') }}"
            >
                <span class="text-2xl">🥃</span>
                <span>{{ __('Slàinte Mhath') }}</span>
            </button>
        </div>
    </section>

    {{-- All submitted: host starts reveal, others wait --}}
    @if ($tastingSession->status === 'awaiting_reveal')
        <section class="rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 p-6">
            @can('update', $tastingSession)
                <flux:heading size="lg">{{ __('session.everyone_submitted') }}</flux:heading>
                <flux:text class="mt-2">{{ __('session.start_reveal_ready') }}</flux:text>
                <button type="button" wire:click="startReveal" class="mt-4 inline-flex items-center justify-center rounded-lg px-5 py-3 text-base font-semibold text-white shadow-lg" style="background-color: #2563eb; min-height: 48px;">
                    {{ __('session.reveal_countdown') }}
                </button>
            @else
                <flux:heading size="lg">{{ __('session.waiting_host_reveal') }}</flux:heading>
                <flux:text>{{ __('session.everyone_submitted_host') }}</flux:text>
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
            <section class="rounded-lg border border-zinc-200 bg-zinc-50 p-6">
                <flux:heading size="lg">{{ __('Waiting for others') }}</flux:heading>
                <flux:text>{{ $submissionsCount }} / {{ $participantsCount }} {{ __('have submitted') }}</flux:text>
            </section>
        @elseif ($currentParticipant)
            <section class="rounded-lg border border-zinc-200 p-6">
                <flux:heading size="lg">{{ __('session.tasting_notes') }}</flux:heading>
                <form wire:submit="submitTasting" class="mt-4 space-y-6">
                    @if ($formStep === 1)
                        <div>
                            @php
                                $colorOptions = [
                                    ['name' => 'Pale Straw', 'value' => 'Pale Straw', 'hex' => '#F7E7B9'],
                                    ['name' => 'Light Gold', 'value' => 'Light Gold', 'hex' => '#F0D68C'],
                                    ['name' => 'Gold', 'value' => 'Gold', 'hex' => '#E6B04A'],
                                    ['name' => 'Deep Gold', 'value' => 'Deep Gold', 'hex' => '#D19A3A'],
                                    ['name' => 'Amber', 'value' => 'Amber', 'hex' => '#C68B2B'],
                                    ['name' => 'Deep Amber', 'value' => 'Deep Amber', 'hex' => '#A85A1C'],
                                    ['name' => 'Copper', 'value' => 'Copper', 'hex' => '#A65C3C'],
                                    ['name' => 'Mahogany', 'value' => 'Mahogany', 'hex' => '#8B3E2F'],
                                    ['name' => 'Dark Ruby', 'value' => 'Dark Ruby', 'hex' => '#6E2E24'],
                                ];
                            @endphp

                            <label class="block text-sm font-medium text-zinc-700">{{ __('session.color_optional') }}</label>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-md">
                                @foreach ($colorOptions as $c)
                                    @php $isSelected = $tasting_color === $c['value']; @endphp
                                    <button
                                        type="button"
                                        wire:click="$set('tasting_color', '{{ $c['value'] }}')"
                                        class="relative flex items-center gap-3 border p-2 text-sm transition-transform duration-150 hover:shadow {{ $isSelected ? 'ring-4 ring-flux-primary/60 scale-105' : '' }}"
                                        title="{{ $c['name'] }}"
                                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                    >
                                        <span class="inline-block w-10 h-6" style="background-color: {{ $c['hex'] }};"></span>
                                        <span class="truncate">{{ $c['name'] }}</span>
                                        @if($isSelected)
                                            <span class="absolute -top-2 -right-2 bg-sky-600 hover:bg-sky-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✓</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-2 text-xs text-zinc-500">{{ __('session.selected') }}: <strong>{{ $tasting_color ?: __('session.none_selected') }}</strong></div>

                            <flux:button type="button" variant="primary" class="mt-4" wire:click="$set('formStep', 2)">{{ __('session.next') }}</flux:button>
                        </div>
                    @else
                        @php
                            $categories = $this->tasteTagsGrouped;
                            $selectedCount = count($tasting_tags ?? []);
                            $maxTags = $tastingSession->max_taste_tags;
                        @endphp
                        <div>
                            <flux:heading size="sm">{{ __('session.taste_palate') }}</flux:heading>
                            <flux:text class="text-zinc-500">{{ __('session.pick_up_to', ['max' => $tastingSession->max_taste_tags]) }}</flux:text>
                            @if(session('taste_tag_limit'))
                                <div class="mt-2 text-sm text-rose-600">{{ session('taste_tag_limit') }}</div>
                            @endif
                            <div class="mt-3 text-sm text-zinc-600">{{ __('session.pick_up_to', ['max' => $maxTags]) }} — <strong>{{ $selectedCount }}</strong> {{ __('session.selected') }}</div>

                            @php $selectedTags = $this->selectedTasteTagModels; @endphp
                            @if($selectedTags->isNotEmpty())
                                <p class="mt-4 text-sm font-medium text-zinc-600">{{ __('Your chosen tags') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($selectedTags as $tag)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-flux-primary/15 text-flux-primary border border-flux-primary/30">
                                            {{ $tag->name }}
                                            <button type="button" wire:click="toggleTasteTag('{{ $tag->slug }}')" class="rounded-full p-0.5 hover:bg-flux-primary/20" aria-label="{{ __('Remove') }} {{ $tag->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Category grid: from DB (tasteCategoryList); click to open modal with that category's tags --}}
                            <p class="mt-4 text-sm font-medium text-zinc-600">{{ __('session.choose_category') }}</p>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach($this->tasteCategoryList as $cat)
                                    @php $isSelected = $selectedTasteCategory === $cat->slug; @endphp
                                    <button
                                        type="button"
                                        wire:click="$set('selectedTasteCategory', '{{ $cat->slug }}')"
                                        class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 p-6 text-center transition hover:border-flux-primary hover:shadow-md focus:outline-none focus:ring-2 focus:ring-flux-primary/50 {{ $isSelected ? 'border-flux-primary bg-flux-primary/5 ring-2 ring-flux-primary/30' : 'border-zinc-200 bg-white' }}"
                                    >
                                        @if($cat->emoji)
                                            <span class="text-4xl leading-none">{{ $cat->emoji }}</span>
                                        @endif
                                        <span class="text-base font-semibold text-zinc-800">{{ $cat->name }}</span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Modal: tags for the selected category (opens when you click a category) --}}
                            @if($selectedTasteCategory !== null)
                                @php
                                    $tags = $categories->get($selectedTasteCategory, collect());
                                    $modalCat = $this->tasteCategoryList->firstWhere('slug', $selectedTasteCategory);
                                @endphp
                                <div
                                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
                                    wire:click="$set('selectedTasteCategory', null)"
                                    role="dialog"
                                    aria-modal="true"
                                    aria-labelledby="taste-category-modal-title"
                                >
                                    <div
                                        class="w-full max-w-lg max-h-[85vh] overflow-auto rounded-xl border border-zinc-200 bg-white shadow-xl"
                                        x-data
                                        x-on:click.stop
                                    >
                                        <div class="sticky top-0 flex items-center justify-between gap-3 border-b border-zinc-200 bg-white px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                @if($modalCat && $modalCat->emoji)
                                                    <span class="text-2xl">{{ $modalCat->emoji }}</span>
                                                @endif
                                                <h2 id="taste-category-modal-title" class="text-lg font-semibold text-zinc-800">{{ $modalCat ? $modalCat->name : $selectedTasteCategory }}</h2>
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="$set('selectedTasteCategory', null)"
                                                class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800"
                                                aria-label="{{ __('Close') }}"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="p-4 flex flex-wrap gap-2">
                                            @foreach($tags as $tag)
                                                @php
                                                    $isSelected = in_array($tag->slug, $tasting_tags ?? []);
                                                    $disabled = !$isSelected && $selectedCount >= $maxTags;
                                                @endphp
                                                <button
                                                    type="button"
                                                    wire:click="toggleTasteTag('{{ $tag->slug }}')"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm border-2 rounded-full transition {{ $isSelected ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white border-zinc-200 text-zinc-700' }} {{ $disabled ? 'opacity-50 pointer-events-none' : 'hover:shadow hover:border-zinc-300' }}"
                                                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                    title="{{ $tag->name }}"
                                                >
                                                    <span class="font-medium">{{ $tag->name }}</span>
                                                    @if($isSelected)
                                                        <span class="inline-flex bg-white text-zinc-900 rounded-full w-5 h-5 items-center justify-center text-xs">✓</span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 flex gap-2">
                                <flux:button type="button" variant="ghost" wire:click="goToColorStep">{{ __('session.back') }}</flux:button>
                                <flux:button type="submit" variant="primary">{{ __('session.submit') }}</flux:button>
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
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 text-center opacity-0 transition duration-700" x-data x-init="setTimeout(() => $el.classList.add('opacity-100'), 100)">
                <flux:heading size="xl" class="mb-4">{{ __('session.reveal') }}</flux:heading>
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
            <div class="rounded-xl border dark:border-zinc-700 border-zinc-200 p-6">
                <flux:heading size="lg">{{ __('session.scoreboard') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('session.this_round') }}: {{ $revealRound->team_total ?? 0 }} {{ __('session.points') }}</flux:text>
                <ul class="mt-4 space-y-2">
                    @foreach ($tastingSession->activeParticipants as $p)
                        @php
                            $roundPoints = ($revealRound->round_score ?? [])[$p->id] ?? 0;
                        @endphp
                        <li class="flex items-center justify-between rounded-lg bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                            <span>{{ $p->display_name }}</span>
                            <span class="font-medium">{{ $roundPoints }} {{ __('session.pts') }} ({{ __('session.total') }}: {{ $p->total_score }})</span>
                        </li>
                    @endforeach
                </ul>
                @can('update', $tastingSession)
                    <flux:button variant="primary" class="mt-6" wire:click="continueToSetup">{{ __('session.back_to_setup') }}</flux:button>
                @endcan
            </div>
        </section>
    @endif

    {{-- Host: setup drinks (only when session in setup) --}}
    @can('update', $tastingSession)
        @if ($tastingSession->status === 'setup')
        <section>
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('session.drinks') }}</flux:heading>
                <flux:button wire:click="$set('showAddDrink', true)" variant="primary" size="sm">{{ __('session.add_drink') }}</flux:button>
            </div>




            @if ($showAddDrink || $editing_drink_id)
                <div class="mt-4 rounded-lg border border-zinc-200 p-4">
                    <flux:heading size="sm">{{ $editing_drink_id ? __('session.edit_drink') : __('session.new_drink') }}</flux:heading>
                    <form wire:submit="{{ $editing_drink_id ? 'updateDrink' : 'addDrink' }}" class="mt-3 flex flex-col gap-3">
                        <flux:input wire:model="drink_name" :label="__('session.name')" required />
                        <flux:input wire:model="drink_year" :label="__('session.year')" />
                        <flux:input wire:model="drink_location" :label="__('session.location')" />
                        <flux:textarea wire:model="drink_description" :label="__('session.description')" rows="3" />
                        <flux:input wire:model="drink_image" type="file" accept="image/*" :label="__('session.image')" />
                        <div class="flex gap-2">
                            <flux:button type="submit" variant="primary">{{ $editing_drink_id ? __('session.save') : __('session.add') }}</flux:button>
                            <flux:button type="button" wire:click="cancelEdit" variant="ghost">{{ __('session.cancel') }}</flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <ul class="mt-4 space-y-2">
                @forelse ($tastingSession->drinks as $drink)
                    <li class="flex items-center justify-between rounded-lg border border-zinc-200 p-3">
                        <div class="flex items-center gap-3">
                            @if ($drink->image)
                                <img src="{{ $drink->imageUrl() }}" alt="{{ $drink->name }}" class="w-20 h-12 rounded-md object-cover" />
                            @endif
                            <div>
                                <span class="font-medium">{{ $drink->name }}</span>
                                @if ($drink->year)
                                    <span class="text-zinc-500">({{ $drink->year }})</span>
                                @endif
                                @if ($drink->location)
                                    <div class="text-xs text-zinc-500">{{ $drink->location }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" wire:click="startRound({{ $drink->id }})">{{ __('Start round') }}</flux:button>
                            <flux:button size="sm" wire:click="editDrink({{ $drink->id }})">{{ __('Edit') }}</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="deleteDrink({{ $drink->id }})" wire:confirm="{{ __('Delete this drink?') }}">{{ __('Delete') }}</flux:button>
                        </div>
                    </li>
                @empty
                    <li class="rounded-lg border border-dashed border-zinc-300 p-4 text-center text-zinc-500">{{ __('session.no_drinks') }}</li>
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
