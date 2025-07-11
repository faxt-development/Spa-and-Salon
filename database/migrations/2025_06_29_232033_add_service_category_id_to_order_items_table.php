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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')->nullable()->after('itemable_type');
            
            // Add foreign key constraint
            $table->foreign('service_category_id')
                  ->references('id')
                  ->on('service_categories')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['service_category_id']);
            // Then drop the column
            $table->dropColumn('service_category_id');
        });
    }
};
