<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thermal_checks', function (Blueprint $table) {
            $table->jsonb('ai_debug')->nullable();
        });

        Schema::table('thermal_incidents', function (Blueprint $table) {
            $table->jsonb('ai_debug')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('thermal_incidents', function (Blueprint $table) {
            $table->dropColumn('ai_debug');
        });

        Schema::table('thermal_checks', function (Blueprint $table) {
            $table->dropColumn('ai_debug');
        });
    }
};
