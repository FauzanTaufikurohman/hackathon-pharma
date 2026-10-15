<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id', 30)->nullable();
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->uuid('claim_token')->nullable();
            $table->timestampTz('claimed_at')->nullable();
            $table->timestampTz('next_attempt_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->string('provider_message_id', 80)->nullable();
            $table->string('error_code', 60)->nullable();
            $table->index(['status', 'next_attempt_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_attempt_at', 'id']);
            $table->dropColumn(['attempts', 'claim_token', 'claimed_at', 'next_attempt_at', 'sent_at', 'provider_message_id', 'error_code']);
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('telegram_chat_id'));
    }
};
