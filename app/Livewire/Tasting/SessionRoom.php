<?php

namespace App\Livewire\Tasting;

use App\Events\EmojiReaction;
use App\Events\EveryoneSubmitted;
use App\Events\RevealCountdownStarted;
use App\Events\RevealStarted;
use App\Events\RoundStarted;
use App\Events\SlaintePressed;
use App\Events\SlainteSuccess;
use App\Events\SubmissionReceived;
use App\Events\PlayerLeft;
use App\Models\SessionParticipant;
use App\Models\TastingSession;
use App\Models\TastingSubmission;
use App\Models\TasteTag;
use App\Models\TasteTagCategory;
use App\Services\TastingScoringService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SessionRoom extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public TastingSession $tastingSession;

    public bool $showAddDrink = false;

    public string $drink_name = '';

    public string $drink_year = '';

    public string $drink_location = '';

    public string $drink_description = '';

    public $drink_image = null;

    public ?int $editing_drink_id = null;

    /** Auto-open avatar editor right after join redirect. */
    public bool $openAvatarModalOnLoad = false;

    public ?int $openAvatarParticipantId = null;

    public string $openAvatarParticipantSeed = '';

    public string $openAvatarParticipantName = '';

    /** Tasting form (when round in progress) */
    public int $formStep = 1;

    /** Selected taste category slug for step 2 (null = show category picker) */
    public ?string $selectedTasteCategory = null;

    /** Whether the current participant is editing an existing submission for this round. */
    public bool $editingSubmission = false;

    public string $tasting_color = '';

    public ?int $color_viscosity = null;

    public array $tasting_nose_tags = [];

    public ?int $nose_intensity = null;

    public ?int $nose_complexity = null;

    public array $tasting_tags = [];

    public ?int $taste_mouthfeel = null;

    public ?int $taste_finish = null;

    public ?int $taste_development = null;

    /** Manual tag search inputs for nose/taste steps. */
    public string $nose_tag_search = '';

    public string $taste_tag_search = '';

    /** Score breakdown modal state (for round_reveal). */
    public bool $showScoreBreakdown = false;

    public ?array $currentRoundBreakdown = null;

    /** Rating (1–10 scale) and free-text note. */
    public ?float $rating_score = null;

    public string $rating_note = '';

    public function mount(TastingSession $tastingSession): void
    {
        $this->tastingSession = $tastingSession;

        if (auth()->check()) {
            $this->authorize('view', $tastingSession);
        } else {
            $participantId = session('tasting_participant_id');
            if (! $participantId) {
                abort(403, __('Please join the session first.'));
            }
            $participant = SessionParticipant::find($participantId);
            if (! $participant || $participant->tasting_session_id !== $tastingSession->id || $participant->left_at !== null) {
                abort(403, __('You are not in this session.'));
            }
        }

        if (session()->pull('tasting_open_avatar_modal', false)) {
            $participant = $this->getCurrentParticipantProperty();
            if ($participant) {
                $this->openAvatarModalOnLoad = true;
                $this->openAvatarParticipantId = $participant->id;
                $this->openAvatarParticipantSeed = $participant->avatar_seed ?? $participant->display_name;
                $this->openAvatarParticipantName = $participant->display_name;
            }
        }
    }

    public function addDrink(): void
    {
        $this->authorize('update', $this->tastingSession);
        $this->validate([
            'drink_name' => ['required', 'string', 'max:255'],
            'drink_year' => ['nullable', 'string', 'max:16'],
            'drink_location' => ['nullable', 'string', 'max:255'],
            'drink_description' => ['nullable', 'string'],
            'drink_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $order = $this->tastingSession->drinks()->max('order') + 1;
        $drink = $this->tastingSession->drinks()->create([
            'name' => $this->drink_name,
            'year' => $this->drink_year ?: null,
            'location' => $this->drink_location ?: null,
            'description' => $this->drink_description ?: null,
            'order' => $order,
        ]);

        if ($this->drink_image) {
            $drink->storeImage($this->drink_image);
        }

        $this->reset(['drink_name', 'drink_year', 'drink_location', 'drink_description', 'drink_image', 'showAddDrink']);
        $this->tastingSession->refresh();
    }

    public function editDrink(int $id): void
    {
        $this->authorize('update', $this->tastingSession);
        $drink = $this->tastingSession->drinks()->findOrFail($id);
        $this->editing_drink_id = $id;
        $this->drink_name = $drink->name;
        $this->drink_year = $drink->year ?? '';
        $this->drink_location = $drink->location ?? '';
        $this->drink_description = $drink->description ?? '';
    }

    public function updateDrink(): void
    {
        $this->authorize('update', $this->tastingSession);
        $drink = $this->tastingSession->drinks()->findOrFail($this->editing_drink_id);
        $this->validate([
            'drink_name' => ['required', 'string', 'max:255'],
            'drink_year' => ['nullable', 'string', 'max:16'],
            'drink_location' => ['nullable', 'string', 'max:255'],
            'drink_description' => ['nullable', 'string'],
            'drink_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $drink->update([
            'name' => $this->drink_name,
            'year' => $this->drink_year ?: null,
            'location' => $this->drink_location ?: null,
            'description' => $this->drink_description ?: null,
        ]);

        if ($this->drink_image) {
            $drink->storeImage($this->drink_image);
        }

        $this->reset(['editing_drink_id', 'drink_name', 'drink_year', 'drink_location', 'drink_description', 'drink_image']);
        $this->tastingSession->refresh();
    }

    public function deleteDrink(int $id): void
    {
        $this->authorize('update', $this->tastingSession);
        $this->tastingSession->drinks()->findOrFail($id)->delete();
        $this->tastingSession->refresh();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editing_drink_id', 'drink_name', 'drink_year', 'drink_location', 'drink_description', 'drink_image']);
    }

    public function startRound(int $drinkId): void
    {
        $this->authorize('update', $this->tastingSession);
        $drink = $this->tastingSession->drinks()->findOrFail($drinkId);
        $round = $this->tastingSession->rounds()->create([
            'drink_id' => $drink->id,
        ]);
        $index = $this->tastingSession->rounds()->count() - 1;
        $this->tastingSession->update([
            'status' => 'in_progress',
            'current_round_index' => $index,
        ]);
        broadcast(new RoundStarted($this->tastingSession->id, $round->id))->toOthers();
        $this->tastingSession->refresh();
        $this->reset([
            'formStep', 'tasting_color', 'color_viscosity',
            'tasting_nose_tags', 'nose_intensity', 'nose_complexity',
            'tasting_tags', 'taste_mouthfeel', 'taste_finish', 'taste_development',
            'nose_tag_search', 'taste_tag_search', 'rating_score', 'rating_note',
        ]);
    }

    public function submitTasting(): void
    {
        $participant = $this->getCurrentParticipantProperty();
        $round = $this->getCurrentRoundProperty();
        if (! $participant || ! $round) {
            return;
        }
        $maxTags = $this->tastingSession->max_taste_tags;
        $this->validate([
            'tasting_color' => ['nullable', 'string', 'max:100'],
            'color_viscosity' => ['required', 'integer', 'between:1,5'],
            'tasting_nose_tags' => ['array', 'max:'.$maxTags],
            'tasting_nose_tags.*' => ['string', 'exists:taste_tags,slug'],
            'nose_intensity' => ['required', 'integer', 'between:1,5'],
            'nose_complexity' => ['required', 'integer', 'between:1,5'],
            'tasting_tags' => ['array', 'max:'.$maxTags],
            'tasting_tags.*' => ['string', 'exists:taste_tags,slug'],
            'taste_mouthfeel' => ['required', 'integer', 'between:1,5'],
            'taste_finish' => ['required', 'integer', 'between:1,5'],
            'taste_development' => ['required', 'integer', 'between:1,5'],
            // rating_score is stored as a 1–10 scale (optioneel)
            'rating_score' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'rating_note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create or update the current participant's submission for this round
        $submission = TastingSubmission::firstOrNew([
            'tasting_round_id' => $round->id,
            'session_participant_id' => $participant->id,
        ]);

        $submission->color = $this->tasting_color ?: null;
        $submission->color_viscosity = $this->color_viscosity;
        $submission->nose_tags = $this->tasting_nose_tags;
        $submission->nose_intensity = $this->nose_intensity;
        $submission->nose_complexity = $this->nose_complexity;
        $submission->taste_tags = $this->tasting_tags;
        $submission->taste_mouthfeel = $this->taste_mouthfeel;
        $submission->taste_finish = $this->taste_finish;
        $submission->taste_development = $this->taste_development;
        $submission->rating_score = $this->rating_score;
        $submission->rating_note = $this->rating_note ?: null;
        $submission->save();

        $count = $round->submissions()->count();
        $total = $this->tastingSession->activeParticipants()->count();
        broadcast(new SubmissionReceived($this->tastingSession->id, $count, $total))->toOthers();

        if ($count >= $total) {
            $this->tastingSession->update(['status' => 'awaiting_reveal']);
            broadcast(new EveryoneSubmitted($this->tastingSession->id))->toOthers();
        }

        $this->tastingSession->refresh();
        $this->editingSubmission = false;
        $this->reset([
            'formStep', 'tasting_color', 'color_viscosity',
            'tasting_nose_tags', 'nose_intensity', 'nose_complexity',
            'tasting_tags', 'taste_mouthfeel', 'taste_finish', 'taste_development',
            'nose_tag_search', 'taste_tag_search', 'rating_score', 'rating_note',
        ]);
    }

    public function goToStep(int $step): void
    {
        // Validate required sliders before moving forward.
        if ($step === 2) {
            $this->validate([
                'color_viscosity' => ['required', 'integer', 'between:1,5'],
            ]);
        } elseif ($step === 3) {
            $this->validate([
                'nose_intensity' => ['required', 'integer', 'between:1,5'],
                'nose_complexity' => ['required', 'integer', 'between:1,5'],
            ]);
        } elseif ($step === 4) {
            $this->validate([
                'taste_mouthfeel' => ['required', 'integer', 'between:1,5'],
                'taste_finish' => ['required', 'integer', 'between:1,5'],
                'taste_development' => ['required', 'integer', 'between:1,5'],
            ]);
        }

        $this->formStep = $step;
        $this->dispatch('wizard-step-changed');
    }

    // Toggle a taste tag for the current participant, enforcing the max tags limit
    public function toggleTasteTag(string $slug): void
    {
        $max = $this->tastingSession->max_taste_tags;
        $selected = $this->tasting_tags ?? [];

        if (in_array($slug, $selected, true)) {
            // remove
            $this->tasting_tags = array_values(array_filter($selected, fn($s) => $s !== $slug));
            return;
        }

        if (count($selected) >= $max) {
            // flash a small error message (session) so UI can show it if desired
            session()->flash('taste_tag_limit', __('session.pick_up_to', ['max' => $max]));
            return;
        }

        $this->tasting_tags = array_values(array_merge($selected, [$slug]));
    }

    public function toggleNoseTag(string $slug): void
    {
        $max = $this->tastingSession->max_taste_tags;
        $selected = $this->tasting_nose_tags ?? [];

        if (in_array($slug, $selected, true)) {
            $this->tasting_nose_tags = array_values(array_filter($selected, fn ($s) => $s !== $slug));
            return;
        }

        if (count($selected) >= $max) {
            session()->flash('taste_tag_limit', __('session.pick_up_to', ['max' => $max]));
            return;
        }

        $this->tasting_nose_tags = array_values(array_merge($selected, [$slug]));
    }

    public function selectNoseTagFromSearch(string $slug): void
    {
        $this->toggleNoseTag($slug);
        $this->nose_tag_search = '';
    }

    public function selectTasteTagFromSearch(string $slug): void
    {
        $this->toggleTasteTag($slug);
        $this->taste_tag_search = '';
    }

    #[Computed]
    public function getCurrentParticipantProperty(): ?SessionParticipant
    {
        if (auth()->check()) {
            return $this->tastingSession->activeParticipants()->where('user_id', auth()->id())->first();
        }
        $id = session('tasting_participant_id');
        if (! $id) {
            return null;
        }
        $p = SessionParticipant::find($id);
        return $p && $p->tasting_session_id === $this->tastingSession->id && $p->left_at === null ? $p : null;
    }

    public function kickParticipant(int $participantId): void
    {
        $this->authorize('update', $this->tastingSession);

        $participant = $this->tastingSession->participants()
            ->whereNull('left_at')
            ->find($participantId);

        if (! $participant || $participant->is_host) {
            return;
        }

        $participant->update(['left_at' => now()]);

        $round = $this->getCurrentRoundProperty();
        if ($round && in_array($this->tastingSession->status, ['in_progress', 'awaiting_reveal'], true)) {
            $count = $round->submissions()->count();
            $total = $this->tastingSession->activeParticipants()->count();

            broadcast(new SubmissionReceived($this->tastingSession->id, $count, $total))->toOthers();

            if ($count >= $total) {
                $this->tastingSession->update(['status' => 'awaiting_reveal']);
                broadcast(new EveryoneSubmitted($this->tastingSession->id))->toOthers();
            }
        }

        broadcast(new PlayerLeft($this->tastingSession->id, $participant->id))->toOthers();
        $this->tastingSession->refresh();
    }

    #[Computed]
    public function getCurrentRoundProperty()
    {
        if ($this->tastingSession->current_round_index === null) {
            return null;
        }
        $allowed = ['in_progress', 'awaiting_reveal', 'round_reveal'];
        if (! in_array($this->tastingSession->status, $allowed, true)) {
            return null;
        }
        return $this->tastingSession->rounds()->orderBy('id')->skip($this->tastingSession->current_round_index)->first();
    }

    #[Computed]
    public function tasteTagsGrouped(): \Illuminate\Support\Collection
    {
        return TasteTag::with('category')->orderBy('order')->get()->groupBy(function($tag) {
            if ($tag->category) return $tag->category->slug;
            return 'uncategorized';
        });
    }

    /** Categories from DB (name + emoji); includes uncategorized if present in grouped tags. */
    #[Computed]
    public function tasteCategoryList(): \Illuminate\Support\Collection
    {
        $grouped = $this->tasteTagsGrouped;
        $fromDb = TasteTagCategory::orderBy('order')->get();
        $list = $fromDb->map(fn ($c) => (object) ['slug' => $c->slug, 'name' => $c->name, 'emoji' => $c->emoji]);
        if ($grouped->has('uncategorized')) {
            $list->push((object) ['slug' => 'uncategorized', 'name' => __('session.uncategorized'), 'emoji' => null]);
        }
        return $list;
    }

    #[Computed]
    public function selectedTasteTagModels(): \Illuminate\Support\Collection
    {
        if (empty($this->tasting_tags)) {
            return collect();
        }
        return TasteTag::whereIn('slug', $this->tasting_tags)->orderBy('name')->get();
    }

    public function goToColorStep(): void
    {
        $this->formStep = 1;
        $this->selectedTasteCategory = null;
    }

    public function openScoreBreakdown(): void
    {
        $round = $this->getCurrentRoundProperty();
        $service = app(TastingScoringService::class);

        // If we are in a round context, show breakdown for the current round.
        if ($round) {
            $details = $service->computeRoundDetails($round);
            $details['ratings'] = $round->submissions()
                ->with('sessionParticipant')
                ->whereNotNull('rating_score')
                ->get()
                ->map(function ($s) {
                    return [
                        'participant' => optional($s->sessionParticipant)->display_name ?? '#'.$s->session_participant_id,
                        'score' => $s->rating_score,
                        'note' => $s->rating_note,
                    ];
                })
                ->sortByDesc('score')
                ->values()
                ->all();
            $this->currentRoundBreakdown = $details;
        } else {
            $this->currentRoundBreakdown = [
                'rounds' => $this->tastingSession->rounds()
                    ->whereNotNull('round_score')
                    ->orderBy('id')
                    ->get()
                    ->map(function ($r) use ($service) {
                        return [
                            'drink' => [
                                'name' => optional($r->drink)->name,
                            ],
                            'team_total' => $r->team_total ?? 0,
                            'avg_rating' => $r->submissions()->whereNotNull('rating_score')->avg('rating_score'),
                            'ratings' => $r->submissions()
                                ->with('sessionParticipant')
                                ->whereNotNull('rating_score')
                                ->get()
                                ->map(function ($s) {
                                    return [
                                        'participant' => optional($s->sessionParticipant)->display_name ?? '#'.$s->session_participant_id,
                                        'score' => $s->rating_score,
                                        'note' => $s->rating_note,
                                    ];
                                })
                                ->sortByDesc('score')
                                ->values()
                                ->all(),
                            'details' => $service->computeRoundDetails($r),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        }

        $this->showScoreBreakdown = true;
    }

    public function closeScoreBreakdown(): void
    {
        $this->showScoreBreakdown = false;
    }

    public function openDrinkScoreBreakdown(int $drinkId): void
    {
        $service = app(TastingScoringService::class);

        $rounds = $this->tastingSession->rounds()
            ->where('drink_id', $drinkId)
            ->whereNotNull('round_score')
            ->orderBy('id')
            ->get();

        $this->currentRoundBreakdown = [
            'rounds' => $rounds
                ->map(function ($r) use ($service) {
                    return [
                        'drink' => [
                            'name' => optional($r->drink)->name,
                        ],
                        'team_total' => $r->team_total ?? 0,
                        'avg_rating' => $r->submissions()->whereNotNull('rating_score')->avg('rating_score'),
                        'ratings' => $r->submissions()
                            ->with('sessionParticipant')
                            ->whereNotNull('rating_score')
                            ->get()
                            ->map(function ($s) {
                                return [
                                    'participant' => optional($s->sessionParticipant)->display_name ?? '#'.$s->session_participant_id,
                                    'score' => $s->rating_score,
                                    'note' => $s->rating_note,
                                ];
                            })
                            ->sortByDesc('score')
                            ->values()
                            ->all(),
                        'details' => $service->computeRoundDetails($r),
                    ];
                })
                ->values()
                ->all(),
        ];

        $this->showScoreBreakdown = true;
    }

    /**
     * Allow the current participant (including the host, if joined) to reopen
     * and edit their own tasting form for the current round.
     */
    public function reopenTastingForm(): void
    {
        $participant = $this->getCurrentParticipantProperty();
        $round = $this->getCurrentRoundProperty();

        if (! $participant || ! $round) {
            return;
        }

        $submission = TastingSubmission::where('tasting_round_id', $round->id)
            ->where('session_participant_id', $participant->id)
            ->first();

        if (! $submission) {
            return;
        }

        $this->tasting_color = $submission->color ?? '';
        $this->color_viscosity = $submission->color_viscosity;
        $this->tasting_nose_tags = $submission->nose_tags ?? [];
        $this->nose_intensity = $submission->nose_intensity;
        $this->nose_complexity = $submission->nose_complexity;
        $this->tasting_tags = $submission->taste_tags ?? [];
        $this->taste_mouthfeel = $submission->taste_mouthfeel;
        $this->taste_finish = $submission->taste_finish;
        $this->taste_development = $submission->taste_development;
        $this->rating_score = $submission->rating_score;
        $this->rating_note = $submission->rating_note ?? '';
        $this->nose_tag_search = '';
        $this->taste_tag_search = '';

        $this->editingSubmission = true;
        $this->formStep = 1;
        $this->selectedTasteCategory = null;
    }

    public function startReveal(): void
    {
        $this->authorize('update', $this->tastingSession);
        if ($this->tastingSession->status !== 'awaiting_reveal') {
            return;
        }
        $round = $this->tastingSession->currentRound();
        if (! $round) {
            return;
        }
        $round->update(['revealed_at' => now()]);
        $scores = app(TastingScoringService::class)->computeRoundScores($round);
        $round->update([
            'round_score' => $scores['round_score'],
            'team_total' => $scores['team_total'],
        ]);
        foreach ($scores['round_score'] as $participantId => $points) {
            SessionParticipant::where('id', $participantId)->increment('total_score', $points);
        }
        $this->tastingSession->update(['status' => 'round_reveal']);

        // Everyone (including host) sees 3, 2, 1 countdown first, then the reveal modal.
        $this->dispatch('reveal-countdown-started');
        broadcast(new RevealCountdownStarted($this->tastingSession->id));
        broadcast(new RevealStarted($this->tastingSession->id))->toOthers();

        $this->tastingSession->refresh();
    }

    public function continueToSetup(): void
    {
        $this->authorize('update', $this->tastingSession);
        $this->tastingSession->update([
            'status' => 'setup',
            'current_round_index' => null,
        ]);
        $this->tastingSession->refresh();
    }

    public function pressSlainte(): void
    {
        $participant = $this->getCurrentParticipantProperty();
        if (! $participant) {
            return;
        }

        $key = 'slainte.'.$this->tastingSession->id;
        $lockKey = 'slainte.lock.'.$this->tastingSession->id;
        $total = $this->tastingSession->activeParticipants()->count();
        $now = time();

        $lock = Cache::lock($lockKey, 5);
        if (! $lock->get()) {
            return;
        }

        try {
            $data = Cache::get($key);
            if (! $data || ($now - $data['started_at']) > 3) {
                $data = ['started_at' => $now, 'pressed' => []];
            }

            if (! in_array($participant->id, $data['pressed'], true)) {
                $data['pressed'][] = $participant->id;
            }

            if (count($data['pressed']) >= $total) {
                Cache::forget($key);
                $lock->release();
                broadcast(new SlainteSuccess($this->tastingSession->id))->toOthers();
                return;
            }

            Cache::put($key, $data, 5);
            $lock->release();
        } catch (\Throwable $e) {
            $lock->release();
            throw $e;
        }

        broadcast(new SlaintePressed(
            $this->tastingSession->id,
            count($data['pressed']),
            $total,
            $data['started_at']
        ))->toOthers();
    }

    public const EMOJI_LIST = ['👍', '👎', '❤️', '🔥', '🥃', '🍷', '😂', '😮', '👏', '🎉', '⭐', '🙌', '😋', '🤔', '💯'];

    public function sendEmoji(string $emoji): void
    {
        $participant = $this->getCurrentParticipantProperty();
        if (! $participant) {
            return;
        }
        if (! in_array($emoji, self::EMOJI_LIST, true)) {
            return;
        }
        broadcast(new EmojiReaction($this->tastingSession->id, $participant->id, $emoji))->toOthers();
    }

    public function updateAvatar(int $participantId, ?string $seed): void
    {
        $p = SessionParticipant::find($participantId);
        if (! $p) return;

        $current = $this->getCurrentParticipantProperty();
        // Only allow changing own avatar unless user is the host
        if (! auth()->check()) {
            if (! $current || $current->id !== $p->id) {
                return;
            }
        } else {
            if (auth()->id() !== $p->user_id && ! auth()->user()->can('update', $this->tastingSession)) {
                return;
            }
        }

        $p->avatar_seed = $seed ?: null;
        $p->save();
        $this->tastingSession->refresh();
        // Broadcast participant update so other clients see changes in real-time.
        broadcast(new \App\Events\ParticipantUpdated($this->tastingSession->id, $p->id, $p->display_name, $p->avatar_seed))->toOthers();
    }

    public function updateParticipant(int $participantId, ?string $displayName = null, ?string $avatarSeed = null): void
    {
        $p = SessionParticipant::find($participantId);
        if (! $p) return;

        $current = $this->getCurrentParticipantProperty();
        // Only allow changing own participant or host/authorized user
        if (! auth()->check()) {
            if (! $current || $current->id !== $p->id) {
                return;
            }
        } else {
            if (auth()->id() !== $p->user_id && ! auth()->user()->can('update', $this->tastingSession)) {
                return;
            }
        }

        if ($displayName !== null) {
            $p->display_name = trim($displayName) ?: $p->display_name;
        }
        if ($avatarSeed !== null) {
            $p->avatar_seed = $avatarSeed ?: null;
        }
        $p->save();
        $this->tastingSession->refresh();

        broadcast(new \App\Events\ParticipantUpdated($this->tastingSession->id, $p->id, $p->display_name, $p->avatar_seed))->toOthers();
    }

    public function getJoinUrlProperty(): string
    {
        return url()->route('tasting.join', ['code' => $this->tastingSession->code]);
    }

    public function render()
    {
        // Always sync the session from the database so guests see state changes
        // (like moving to round_reveal) after a $wire.$refresh() without full page reload.
        $this->tastingSession->refresh();

        return view('livewire.tasting.session-room')
            ->layout('layouts.tasting', [
                'sessionName' => $this->tastingSession->name,
                'title' => $this->tastingSession->name,
                'tastingSessionId' => $this->tastingSession->id,
                'joinUrl' => $this->getJoinUrlProperty(),
                'joinCode' => $this->tastingSession->code,
            ]);
    }
}
