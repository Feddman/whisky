<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasting_session_id')->constrained('tasting_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->boolean('is_host')->default(false);
            $table->unsignedInteger('total_score')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_participants');
    }
};
