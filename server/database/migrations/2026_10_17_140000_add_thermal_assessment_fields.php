<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thermal_checks', function (Blueprint $table) {
            $table->jsonb('ai_assessment')->nullable();
            $table->string('ai_assessment_status', 20)->default('pending');
            $table->string('ai_model', 100)->nullable();
            $table->timestampTz('ai_assessed_at')->nullable();
        });

        Schema::table('thermal_incidents', function (Blueprint $table) {
            $table->jsonb('ai_assessment')->nullable();
            $table->string('ai_assessment_status', 20)->default('pending');
            $table->string('ai_model', 100)->nullable();
            $table->timestampTz('ai_assessed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('thermal_incidents', function (Blueprint $table) {
            $table->dropColumn([
                'ai_assessment',
                'ai_assessment_status',
                'ai_model',
                'ai_assessed_at',
            ]);
        });

        Schema::table('thermal_checks', function (Blueprint $table) {
            $table->dropColumn([
                'ai_assessment',
                'ai_assessment_status',
                'ai_model',
                'ai_assessed_at',
            ]);
        });
    }
};
