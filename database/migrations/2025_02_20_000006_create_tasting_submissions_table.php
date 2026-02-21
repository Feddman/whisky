<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasting_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasting_round_id')->constrained('tasting_rounds')->cascadeOnDelete();
            $table->foreignId('session_participant_id')->constrained('session_participants')->cascadeOnDelete();
            $table->string('color')->nullable();
            $table->json('taste_tags'); // array of tag slugs
            $table->timestamps();

            $table->unique(['tasting_round_id', 'session_participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasting_submissions');
    }
};
