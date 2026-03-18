<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('drinks')) {
            return;
        }

        Schema::table('drinks', function (Blueprint $table) {
            $table->string('submitted_by')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('drinks')) {
            return;
        }

        Schema::table('drinks', function (Blueprint $table) {
            $table->dropColumn('submitted_by');
        });
    }
};

