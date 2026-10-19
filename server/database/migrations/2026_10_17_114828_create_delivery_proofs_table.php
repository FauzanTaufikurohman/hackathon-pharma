<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->restrictOnDelete();

            $table->foreignId('courier_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('photo_path');
            $table->string('recipient_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('delivered_at');

            $table->timestamps();

            $table->index(['shipment_id', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
    }
};