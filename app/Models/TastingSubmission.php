<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TastingSubmission extends Model
{
    protected $fillable = [
        'tasting_round_id',
        'session_participant_id',
        'color',
        'taste_tags',
    ];

    protected function casts(): array
    {
        return [
            'taste_tags' => 'array',
        ];
    }

    public function tastingRound(): BelongsTo
    {
        return $this->belongsTo(TastingRound::class);
    }

    public function sessionParticipant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class);
    }
}
