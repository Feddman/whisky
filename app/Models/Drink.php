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

        // Prefer uploads disk for images stored in uploads, otherwise fallback to public
        if (Storage::disk('uploads')->exists($this->image)) {
            return Storage::disk('uploads')->url($this->image);
        }

        return Storage::disk('public')->url($this->image);
    }

    public function storeImage(UploadedFile $file): string
    {
        // store on uploads disk inside /drinks
        $path = $file->store('drinks', 'uploads');
        if ($this->image) {
            // attempt to delete previous on both disks
            Storage::disk('uploads')->delete($this->image);
            Storage::disk('public')->delete($this->image);
        }
        $this->update(['image' => $path]);

        return $path;
    }

    /**
     * Ensure image files are deleted from storage when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $drink) {
            if (! $drink->image) {
                return;
            }

            // try removing from both disks (uploads preferred)
            try {
                Storage::disk('uploads')->delete($drink->image);
            } catch (\Throwable $e) {
                // noop
            }

            try {
                Storage::disk('public')->delete($drink->image);
            } catch (\Throwable $e) {
                // noop
            }
        });
    }
}
