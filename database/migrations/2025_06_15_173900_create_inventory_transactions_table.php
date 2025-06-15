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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('type'); // purchase, sale, adjustment, return, waste, transfer
            $table->integer('quantity'); // Can be negative for sales, waste, etc.
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->nullableMorphs('reference'); // For linking to orders, purchases, etc.
            $table->text('notes')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
