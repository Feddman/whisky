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
        if (! Schema::hasTable('taste_tag_categories')) return;

        Schema::table('taste_tag_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('taste_tag_categories', 'emoji')) {
                $table->string('emoji')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('taste_tag_categories')) return;

        Schema::table('taste_tag_categories', function (Blueprint $table) {
            if (Schema::hasColumn('taste_tag_categories', 'emoji')) {
                $table->dropColumn('emoji');
            }
        });
    }
};
