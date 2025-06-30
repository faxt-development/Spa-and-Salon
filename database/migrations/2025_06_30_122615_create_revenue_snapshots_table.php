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
        Schema::create('revenue_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->index();
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('location_id')->nullable()->index();
            // Removed foreign key constraint to avoid dependency on locations table
            $table->json('breakdown')->nullable()->comment('JSON breakdown of revenue by service, product, etc.');
            $table->timestamps();
            
            $table->unique(['snapshot_date', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_snapshots');
    }
};
