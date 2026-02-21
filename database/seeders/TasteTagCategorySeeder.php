<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TasteTagCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['slug' => 'wood', 'name' => 'Wood', 'order' => 10, 'emoji' => '🌲'],
            ['slug' => 'sweet', 'name' => 'Sweet', 'order' => 20, 'emoji' => '🍯'],
            ['slug' => 'floral', 'name' => 'Floral', 'order' => 30, 'emoji' => '🌸'],
            ['slug' => 'fruity', 'name' => 'Fruity', 'order' => 40, 'emoji' => '🍑'],
            ['slug' => 'peat', 'name' => 'Peat', 'order' => 50, 'emoji' => '🔥'],
            ['slug' => 'spice', 'name' => 'Spice', 'order' => 60, 'emoji' => '🌶️'],
            ['slug' => 'nuts', 'name' => 'Nuts', 'order' => 70, 'emoji' => '🥜'],
        ];

        foreach ($cats as $c) {
            DB::table('taste_tag_categories')->updateOrInsert(['slug' => $c['slug']], $c);
        }
    }
}
