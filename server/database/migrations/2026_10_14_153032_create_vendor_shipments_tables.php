<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 100)->unique();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('sender_name');
            $table->string('recipient_name');
            $table->string('recipient_phone', 50);
            $table->text('address');
            $table->string('origin_city');
            $table->string('destination_city');
            $table->string('item_summary');
            $table->unsignedInteger('package_count')->default(1);
            $table->decimal('weight_kg', 10, 3)->default(1);
            $table->string('package_type')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('handling_instructions');
            $table->string('temperature_profile', 20)->default('controlled');
            $table->decimal('temperature_min', 6, 2)->nullable();
            $table->decimal('temperature_max', 6, 2)->nullable();
            $table->timestampTz('scheduled_at')->nullable();
            $table->string('status', 40)->default('inspection');
            $table->string('last_location')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('recipient_ready')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->index(['buyer_id', 'status', 'scheduled_at']);
        });
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->boolean('packaging');
            $table->boolean('seal');
            $table->boolean('label');
            $table->boolean('passed');
            $table->decimal('temperature', 6, 2)->nullable();
            $table->text('note');
            $table->string('sensor_device')->nullable();
            $table->string('hub_name')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestampTz('inspected_at');
            $table->timestampsTz();
            $table->index(['shipment_id', 'id']);
        });
        Schema::create('telemetry_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->decimal('temperature', 6, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('speed_kmh', 7, 2)->nullable();
            $table->boolean('is_simulated')->default(false);
            $table->timestampTz('recorded_at');
            $table->timestampsTz();
            $table->index(['shipment_id', 'recorded_at', 'id']);
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 60);
            $table->string('title');
            $table->text('reason')->nullable();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->json('metadata')->nullable();
            $table->uuid('request_id')->nullable()->unique();
            $table->timestampTz('created_at');
            $table->index(['shipment_id', 'created_at']);
        });
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->string('type', 60);
            $table->string('status', 20)->default('open');
            $table->text('description');
            $table->text('resolution_reason')->nullable();
            $table->timestampsTz();
            $table->index(['shipment_id', 'status']);
        });
        Schema::create('shipment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->unsignedInteger('revision')->default(1);
            $table->json('snapshot');
            $table->string('sha256', 64);
            $table->boolean('is_demo')->default(false);
            $table->timestampTz('published_at');
            $table->timestampsTz();
            $table->unique(['shipment_id', 'revision']);
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('status', 30)->default('pending');
            $table->string('dedupe_key')->unique();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        foreach (['messages', 'shipment_reports', 'issues', 'events', 'telemetry_samples', 'inspections', 'shipments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
