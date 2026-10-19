<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('origin_customer_user_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('destination_customer_user_id')
                ->nullable()
                ->after('origin_customer_user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['origin_customer_user_id']);
            $table->dropForeign(['destination_customer_user_id']);
            $table->dropColumn([
                'origin_customer_user_id',
                'destination_customer_user_id',
            ]);
        });
    }
};
