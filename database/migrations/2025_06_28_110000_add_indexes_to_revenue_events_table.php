<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add payment method foreign key to revenue_events
        Schema::table('revenue_events', function (Blueprint $table) {
            if (!Schema::hasColumn('revenue_events', 'payment_method_id')) {
                $table->foreignId('payment_method_id')
                      ->nullable()
                      ->constrained('payment_methods')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            if (Schema::hasColumn('revenue_events', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }
        });
    }
};
