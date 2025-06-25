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
        Schema::create('transaction_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // service, product, tax, tip, discount, gift_card, other
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0); // total for this line item
            $table->decimal('tax_rate', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('itemable_type')->nullable(); // For polymorphic relation
            $table->unsignedBigInteger('itemable_id')->nullable(); // For polymorphic relation
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete(); // For commission tracking
            $table->json('metadata')->nullable(); // For additional data
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('item_type');
            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_line_items');
    }
};
