<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TastingSession extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'code',
        'max_taste_tags',
        'status',
        'current_round_index',
    ];

    protected function casts(): array
    {
        return [
            'max_taste_tags' => 'integer',
            'current_round_index' => 'integer',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function drinks(): HasMany
    {
        return $this->hasMany(Drink::class, 'tasting_session_id')->orderBy('order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'tasting_session_id');
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'tasting_session_id')->whereNull('left_at');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(TastingRound::class, 'tasting_session_id');
    }

    public function currentRound(): ?TastingRound
    {
        if ($this->current_round_index === null) {
            return null;
        }

        return $this->rounds()->orderBy('id')->skip($this->current_round_index)->first();
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function isHost(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
