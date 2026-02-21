<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->unsignedTinyInteger('max_taste_tags')->default(5);
            $table->string('status', 32)->default('setup'); // setup, in_progress, round_reveal, round_scored
            $table->unsignedInteger('current_round_index')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasting_sessions');
    }
};
