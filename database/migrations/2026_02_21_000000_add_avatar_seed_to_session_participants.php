<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_participants', function (Blueprint $table) {
            $table->string('avatar_seed')->nullable()->after('display_name');
        });
    }

    public function down(): void
    {
        Schema::table('session_participants', function (Blueprint $table) {
            $table->dropColumn('avatar_seed');
        });
    }
};
