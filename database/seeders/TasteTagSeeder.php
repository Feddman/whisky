<?php

namespace Database\Seeders;

use App\Models\TasteTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasteTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // wood
            ['slug' => 'oak', 'name' => 'Oak', 'category' => 'wood', 'order' => 10],
            ['slug' => 'cedar', 'name' => 'Cedar', 'category' => 'wood', 'order' => 11],
            ['slug' => 'char', 'name' => 'Char', 'category' => 'wood', 'order' => 12],
            ['slug' => 'pine', 'name' => 'Pine', 'category' => 'wood', 'order' => 13],

            // sweet
            ['slug' => 'honey', 'name' => 'Honey', 'category' => 'sweet', 'order' => 20],
            ['slug' => 'caramel', 'name' => 'Caramel', 'category' => 'sweet', 'order' => 21],
            ['slug' => 'toffee', 'name' => 'Toffee', 'category' => 'sweet', 'order' => 22],
            ['slug' => 'vanilla', 'name' => 'Vanilla', 'category' => 'sweet', 'order' => 23],

            // floral
            ['slug' => 'rose', 'name' => 'Rose', 'category' => 'floral', 'order' => 30],
            ['slug' => 'lavender', 'name' => 'Lavender', 'category' => 'floral', 'order' => 31],
            ['slug' => 'heather', 'name' => 'Heather', 'category' => 'floral', 'order' => 32],
            ['slug' => 'violet', 'name' => 'Violet', 'category' => 'floral', 'order' => 33],

            // fruity
            ['slug' => 'apple', 'name' => 'Apple', 'category' => 'fruity', 'order' => 40],
            ['slug' => 'pear', 'name' => 'Pear', 'category' => 'fruity', 'order' => 41],
            ['slug' => 'peach', 'name' => 'Peach', 'category' => 'fruity', 'order' => 42],
            ['slug' => 'citrus', 'name' => 'Citrus', 'category' => 'fruity', 'order' => 43],

            // peat
            ['slug' => 'smoke', 'name' => 'Smoke', 'category' => 'peat', 'order' => 50],
            ['slug' => 'medicinal', 'name' => 'Medicinal', 'category' => 'peat', 'order' => 51],
            ['slug' => 'seaweed', 'name' => 'Seaweed', 'category' => 'peat', 'order' => 52],
            ['slug' => 'ash', 'name' => 'Ash', 'category' => 'peat', 'order' => 53],

            // spice
            ['slug' => 'black-pepper', 'name' => 'Black pepper', 'category' => 'spice', 'order' => 60],
            ['slug' => 'clove', 'name' => 'Clove', 'category' => 'spice', 'order' => 61],
            ['slug' => 'nutmeg', 'name' => 'Nutmeg', 'category' => 'spice', 'order' => 62],
            ['slug' => 'ginger', 'name' => 'Ginger', 'category' => 'spice', 'order' => 63],

            // nuts
            ['slug' => 'almond', 'name' => 'Almond', 'category' => 'nuts', 'order' => 70],
            ['slug' => 'walnut', 'name' => 'Walnut', 'category' => 'nuts', 'order' => 71],
            ['slug' => 'hazelnut', 'name' => 'Hazelnut', 'category' => 'nuts', 'order' => 72],
            ['slug' => 'bitter-almond', 'name' => 'Bitter almond', 'category' => 'nuts', 'order' => 73],
        ];

        foreach ($tags as $tag) {
            $category = DB::table('taste_tag_categories')->where('slug', $tag['category'])->first();
            if (! $category) continue; // skip if category missing

            $data = [
                'name' => $tag['name'],
                'order' => $tag['order'],
                'category_id' => $category->id,
            ];
            TasteTag::updateOrCreate(['slug' => $tag['slug']], array_merge(['slug' => $tag['slug']], $data));
        }
    }
}
