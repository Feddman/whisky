<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed taste tag categories first, then tags so tags can reference category IDs
        $this->call(\Database\Seeders\TasteTagCategorySeeder::class);
        $this->call(\Database\Seeders\TasteTagSeeder::class);
    }
}
