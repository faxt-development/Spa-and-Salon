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
        Schema::create('revenue_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->timestamp('event_date');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();

            // For polymorphic relation
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // For line item tracking
            $table->string('line_item_type')->nullable();
            $table->unsignedBigInteger('line_item_id')->nullable();

            // Location and staff tracking
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();

            // Payment method
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            // Additional metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('event_type');
            $table->index('event_date');
            $table->index('location_id');
            $table->index('staff_id');
            $table->index('payment_method_id');
            $table->index('transaction_id');
            $table->index(['source_type', 'source_id']);
            $table->index(['line_item_type', 'line_item_id']);
            $table->index(['event_date', 'location_id']);
            $table->index(['event_date', 'staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_events');
    }
};
