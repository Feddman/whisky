<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasting_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasting_session_id')->constrained('tasting_sessions')->cascadeOnDelete();
            $table->foreignId('drink_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('revealed_at')->nullable();
            $table->json('round_score')->nullable(); // { "participant_id": points, ... }
            $table->unsignedInteger('team_total')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasting_rounds');
    }
};
