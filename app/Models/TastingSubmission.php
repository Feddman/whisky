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
        'color_viscosity',
        'taste_tags',
        'nose_tags',
        'nose_intensity',
        'nose_complexity',
        'taste_mouthfeel',
        'taste_finish',
        'taste_development',
        'rating_score',
        'rating_note',
    ];

    protected function casts(): array
    {
        return [
            'taste_tags' => 'array',
            'nose_tags' => 'array',
            'color_viscosity' => 'integer',
            'nose_intensity' => 'integer',
            'nose_complexity' => 'integer',
            'taste_mouthfeel' => 'integer',
            'taste_finish' => 'integer',
            'taste_development' => 'integer',
            'rating_score' => 'float',
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
