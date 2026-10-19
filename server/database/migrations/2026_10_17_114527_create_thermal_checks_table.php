<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thermal_checks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->restrictOnDelete();

            $table->foreignId('checker_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('temperature_celsius', 5, 2);
            $table->string('packaging_condition', 30);
            $table->string('physical_condition', 30);
            $table->text('notes')->nullable();

            $table->string('photo_path')->nullable();

            $table->timestampTz('checked_at');
            $table->timestamps();

            $table->index(['shipment_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thermal_checks');
    }
};