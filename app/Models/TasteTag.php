<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TasteTag extends Model
{
    protected $fillable = ['slug', 'name', 'category_id', 'order'];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'category_id' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\TasteTagCategory::class, 'category_id');
    }
}
