<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionParticipant extends Model
{
    protected $fillable = [
        'tasting_session_id',
        'user_id',
        'display_name',
        'avatar_seed',
        'is_host',
        'total_score',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'is_host' => 'boolean',
            'total_score' => 'integer',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'avatar_seed' => 'string',
        ];
    }

    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function tastingSession(): BelongsTo
    {
        return $this->belongsTo(TastingSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TastingSubmission::class, 'session_participant_id');
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}
