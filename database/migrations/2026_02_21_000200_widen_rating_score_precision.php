<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasting_submissions')) {
            return;
        }

        Schema::table('tasting_submissions', function (Blueprint $table) {
            // Allow storing 10.0 instead of only up to 9.9
            $table->decimal('rating_score', 3, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasting_submissions')) {
            return;
        }

        Schema::table('tasting_submissions', function (Blueprint $table) {
            // Revert to the original precision
            $table->decimal('rating_score', 2, 1)->nullable()->change();
        });
    }
};

