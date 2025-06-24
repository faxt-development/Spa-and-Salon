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
        Schema::table('appointment_product', function (Blueprint $table) {
            // Drop existing columns that might be in the way
            $table->dropColumn(['id']);

            // Add foreign keys
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);

            $table->primary(['appointment_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_product', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['product_id']);

            // Drop added columns
            $table->dropColumn([
                'appointment_id',
                'product_id',
                'quantity',
                'price',
                'created_at',
                'updated_at'
            ]);

            // Add back the id column
            $table->id();
        });
    }
};
