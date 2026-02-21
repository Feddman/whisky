<?php

namespace Database\Seeders;

use App\Models\TasteTag;
use Illuminate\Database\Seeder;

class TasteTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['slug' => 'woody', 'name' => 'Woody', 'category' => 'Wood & Oak', 'order' => 1],
            ['slug' => 'vanilla', 'name' => 'Vanilla', 'category' => 'Sweet', 'order' => 2],
            ['slug' => 'caramel', 'name' => 'Caramel', 'category' => 'Sweet', 'order' => 3],
            ['slug' => 'honey', 'name' => 'Honey', 'category' => 'Sweet', 'order' => 4],
            ['slug' => 'chocolate', 'name' => 'Chocolate', 'category' => 'Sweet', 'order' => 5],
            ['slug' => 'toffee', 'name' => 'Toffee', 'category' => 'Sweet', 'order' => 6],
            ['slug' => 'floral', 'name' => 'Floral', 'category' => 'Floral', 'order' => 10],
            ['slug' => 'heather', 'name' => 'Heather', 'category' => 'Floral', 'order' => 11],
            ['slug' => 'rose', 'name' => 'Rose', 'category' => 'Floral', 'order' => 12],
            ['slug' => 'fruity', 'name' => 'Fruity', 'category' => 'Fruit', 'order' => 20],
            ['slug' => 'citrus', 'name' => 'Citrus', 'category' => 'Fruit', 'order' => 21],
            ['slug' => 'apple', 'name' => 'Apple', 'category' => 'Fruit', 'order' => 22],
            ['slug' => 'pear', 'name' => 'Pear', 'category' => 'Fruit', 'order' => 23],
            ['slug' => 'dried-fruit', 'name' => 'Dried fruit', 'category' => 'Fruit', 'order' => 24],
            ['slug' => 'smoky', 'name' => 'Smoky', 'category' => 'Peat & Smoke', 'order' => 30],
            ['slug' => 'peat', 'name' => 'Peaty', 'category' => 'Peat & Smoke', 'order' => 31],
            ['slug' => 'medicinal', 'name' => 'Medicinal', 'category' => 'Peat & Smoke', 'order' => 32],
            ['slug' => 'spicy', 'name' => 'Spicy', 'category' => 'Spice', 'order' => 40],
            ['slug' => 'pepper', 'name' => 'Pepper', 'category' => 'Spice', 'order' => 41],
            ['slug' => 'cinnamon', 'name' => 'Cinnamon', 'category' => 'Spice', 'order' => 42],
            ['slug' => 'nutty', 'name' => 'Nutty', 'category' => 'Nuts & Cereal', 'order' => 50],
            ['slug' => 'cereal', 'name' => 'Cereal', 'category' => 'Nuts & Cereal', 'order' => 51],
            ['slug' => 'malty', 'name' => 'Malty', 'category' => 'Nuts & Cereal', 'order' => 52],
        ];

        foreach ($tags as $tag) {
            TasteTag::updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
