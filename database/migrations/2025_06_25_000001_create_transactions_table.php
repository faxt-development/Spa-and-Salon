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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); // Service location
            $table->string('payment_method')->nullable();
            $table->string('transaction_type'); // appointment, retail, gift_card, refund, other
            $table->string('reference_type')->nullable(); // For polymorphic relation
            $table->unsignedBigInteger('reference_id')->nullable(); // For polymorphic relation
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tip_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('transaction_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('external_transaction_id')->nullable(); // ID from payment processor
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->nullOnDelete(); // For refunds
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('transaction_type');
            $table->index('status');
            $table->index('transaction_date');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
