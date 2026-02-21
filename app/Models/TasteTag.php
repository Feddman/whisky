<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TasteTag extends Model
{
    protected $fillable = ['slug', 'name', 'category', 'order'];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }
}
