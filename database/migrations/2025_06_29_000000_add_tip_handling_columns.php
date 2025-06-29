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
        Schema::table('transactions', function (Blueprint $table) {
            // Add tip distribution method (individual, pooled, split)
            $table->string('tip_distribution_method')->nullable()->after('tip_amount');

            // Track if tips have been distributed
            $table->boolean('tips_distributed')->default(false)->after('tip_distribution_method');

            // Track when tips were last distributed
            $table->timestamp('tips_distributed_at')->nullable()->after('tips_distributed');
        });

        // Create tip distribution table
        Schema::create('tip_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2)->nullable(); // For split distribution
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Indexes
            $table->index(['transaction_id', 'staff_id']);
            $table->index('is_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_distributions');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'tip_distribution_method',
                'tips_distributed',
                'tips_distributed_at',
            ]);
        });
    }
};
