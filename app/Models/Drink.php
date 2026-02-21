<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Drink extends Model
{
    protected $fillable = [
        'tasting_session_id',
        'name',
        'year',
        'location',
        'description',
        'image',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function tastingSession(): BelongsTo
    {
        return $this->belongsTo(TastingSession::class);
    }

    public function tastingRounds(): HasMany
    {
        return $this->hasMany(TastingRound::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function storeImage(UploadedFile $file): string
    {
        $path = $file->store('drinks', 'public');
        if ($this->image) {
            Storage::disk('public')->delete($this->image);
        }
        $this->update(['image' => $path]);

        return $path;
    }
}
