<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasting_session_id')->constrained('tasting_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->string('year', 16)->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drinks');
    }
};
