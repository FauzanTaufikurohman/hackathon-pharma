<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku', 100)->unique();
            $table->string('name');
            $table->string('category', 100)->nullable();

            // Batas suhu dalam derajat Celsius.
            $table->decimal('min_temperature', 5, 2);
            $table->decimal('max_temperature', 5, 2);

            // Durasi paparan maksimum sesuai protokol tervalidasi.
            $table->unsignedInteger('max_excursion_seconds')->nullable();

            $table->text('temperature_protocol')->nullable();
            $table->string('protocol_reference')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};