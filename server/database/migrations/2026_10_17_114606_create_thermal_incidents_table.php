<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thermal_incidents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->restrictOnDelete();

            $table->foreignId('thermal_check_id')
                ->nullable()
                ->constrained('thermal_checks')
                ->nullOnDelete();

            $table->decimal('temperature_celsius', 5, 2);
            $table->decimal('allowed_min_temperature', 5, 2);
            $table->decimal('allowed_max_temperature', 5, 2);

            $table->unsignedInteger('exposure_duration_seconds')->nullable();

            $table->string('severity', 30)->default('pending_assessment');
            $table->string('incident_status', 30)->default('open');

            $table->text('description')->nullable();
            $table->text('corrective_action')->nullable();

            $table->timestampTz('detected_at');
            $table->timestampTz('recovered_at')->nullable();

            $table->timestamps();

            $table->index(['shipment_id', 'detected_at']);
            $table->index(['incident_status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thermal_incidents');
    }
};