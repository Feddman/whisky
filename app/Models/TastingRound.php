<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TastingRound extends Model
{
    protected $fillable = [
        'tasting_session_id',
        'drink_id',
        'started_at',
        'revealed_at',
        'round_score',
        'team_total',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'revealed_at' => 'datetime',
            'round_score' => 'array',
            'team_total' => 'integer',
        ];
    }

    public function tastingSession(): BelongsTo
    {
        return $this->belongsTo(TastingSession::class);
    }

    public function drink(): BelongsTo
    {
        return $this->belongsTo(Drink::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TastingSubmission::class, 'tasting_round_id');
    }
}
