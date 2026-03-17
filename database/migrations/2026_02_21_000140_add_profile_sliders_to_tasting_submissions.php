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
            if (! Schema::hasColumn('tasting_submissions', 'color_viscosity')) {
                $table->unsignedTinyInteger('color_viscosity')->nullable()->after('color');
            }
            if (! Schema::hasColumn('tasting_submissions', 'nose_intensity')) {
                $table->unsignedTinyInteger('nose_intensity')->nullable()->after('nose_tags');
            }
            if (! Schema::hasColumn('tasting_submissions', 'nose_complexity')) {
                $table->unsignedTinyInteger('nose_complexity')->nullable()->after('nose_intensity');
            }
            if (! Schema::hasColumn('tasting_submissions', 'taste_mouthfeel')) {
                $table->unsignedTinyInteger('taste_mouthfeel')->nullable()->after('taste_tags');
            }
            if (! Schema::hasColumn('tasting_submissions', 'taste_finish')) {
                $table->unsignedTinyInteger('taste_finish')->nullable()->after('taste_mouthfeel');
            }
            if (! Schema::hasColumn('tasting_submissions', 'taste_development')) {
                $table->unsignedTinyInteger('taste_development')->nullable()->after('taste_finish');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasting_submissions')) {
            return;
        }

        Schema::table('tasting_submissions', function (Blueprint $table) {
            foreach ([
                'color_viscosity',
                'nose_intensity',
                'nose_complexity',
                'taste_mouthfeel',
                'taste_finish',
                'taste_development',
            ] as $column) {
                if (Schema::hasColumn('tasting_submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

