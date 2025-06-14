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
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
                $table->dateTime('start_time');
                $table->dateTime('end_time');
                $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
                $table->text('notes')->nullable();
                $table->decimal('total_price', 10, 2)->default(0);
                $table->boolean('is_paid')->default(false);
                $table->string('cancellation_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
