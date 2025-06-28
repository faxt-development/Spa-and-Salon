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
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('period_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'paid', 'cancelled'])->default('pending');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index(['staff_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
        
        // Add commission_payment_id to staff_performance_metrics table
        Schema::table('staff_performance_metrics', function (Blueprint $table) {
            $table->foreignId('commission_payment_id')
                ->after('staff_id')
                ->nullable()
                ->constrained('commission_payments')
                ->nullOnDelete();
                
            $table->index('commission_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_performance_metrics', function (Blueprint $table) {
            $table->dropForeign(['commission_payment_id']);
            $table->dropIndex(['commission_payment_id']);
            $table->dropColumn('commission_payment_id');
        });
        
        Schema::dropIfExists('commission_payments');
    }
};
