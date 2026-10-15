<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->json('courier_profile')->nullable();
        });
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('inspection_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('decision', 20);
            $table->text('reason');
            $table->timestampsTz();
            $table->index(['shipment_id', 'id']);
        });
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('courier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->json('pre_trip')->nullable();
            $table->text('reason')->nullable();
            $table->timestampTz('responded_at')->nullable();
            $table->timestampsTz();
            $table->index(['courier_id', 'status', 'id']);
            $table->index(['shipment_id', 'id']);
        });
        Schema::create('courier_action_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 50);
            $table->string('payload_hash', 64);
            $table->json('result');
            $table->timestampsTz();
        });
        Schema::table('issues', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('previous_status', 40)->nullable();
            $table->decimal('temperature', 6, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('photo_path')->nullable();
        });
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 20);
            $table->text('reason');
            $table->timestampsTz();
            $table->index(['issue_id', 'id']);
        });
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('courier_id')->constrained('users')->restrictOnDelete();
            $table->string('mode', 20);
            $table->string('recipient_name');
            $table->string('recipient_department')->nullable();
            $table->string('recipient_identity')->nullable();
            $table->decimal('temperature', 6, 2);
            $table->boolean('seal_intact');
            $table->string('photo_path');
            $table->string('photo_sha256', 64);
            $table->string('signature_path')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestampTz('handed_over_at');
            $table->timestampsTz();
        });
        Schema::table('telemetry_samples', function (Blueprint $table) {
            $table->string('sensor_id', 100)->nullable();
            $table->unsignedSmallInteger('battery_pct')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('telemetry_samples', fn (Blueprint $table) => $table->dropColumn(['sensor_id', 'battery_pct']));
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('decisions');
        Schema::table('issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['previous_status', 'temperature', 'latitude', 'longitude', 'photo_path']);
        });
        foreach (['courier_action_receipts', 'assignments', 'approvals'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['is_active', 'courier_profile']));
    }
};
