<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasteTagCategory extends Model
{
    protected $fillable = ['slug', 'name', 'order', 'emoji'];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }
}
