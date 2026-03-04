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
            if (! Schema::hasColumn('tasting_submissions', 'nose_tags')) {
                $table->json('nose_tags')->nullable()->after('taste_tags');
            }
            if (! Schema::hasColumn('tasting_submissions', 'rating_score')) {
                $table->decimal('rating_score', 2, 1)->nullable()->after('nose_tags');
            }
            if (! Schema::hasColumn('tasting_submissions', 'rating_note')) {
                $table->text('rating_note')->nullable()->after('rating_score');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasting_submissions')) {
            return;
        }

        Schema::table('tasting_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('tasting_submissions', 'nose_tags')) {
                $table->dropColumn('nose_tags');
            }
            if (Schema::hasColumn('tasting_submissions', 'rating_score')) {
                $table->dropColumn('rating_score');
            }
            if (Schema::hasColumn('tasting_submissions', 'rating_note')) {
                $table->dropColumn('rating_note');
            }
        });
    }
};

