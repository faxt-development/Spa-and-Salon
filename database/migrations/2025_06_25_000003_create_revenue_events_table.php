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
            $table->string('source_type')->nullable(); // For polymorphic relation
            $table->unsignedBigInteger('source_id')->nullable(); // For polymorphic relation
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete(); // For commission tracking
            $table->json('metadata')->nullable(); // For additional data
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('event_type');
            $table->index('event_date');
            $table->index(['source_type', 'source_id']);
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
