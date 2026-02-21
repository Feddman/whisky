<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('taste_tags')) return;

        Schema::create('taste_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->foreign('category_id')->references('id')->on('taste_tag_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taste_tags');
    }
};
