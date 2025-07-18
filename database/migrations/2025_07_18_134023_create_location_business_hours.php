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
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, etc.
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            // Ensure we don't have duplicate entries for the same day at the same location
            $table->unique(['location_id', 'day_of_week']);

            // Ensure we don't have duplicate entries for the same day company-wide
            $table->unique(['company_id', 'day_of_week', 'location_id'], 'company_day_location_unique');
            
            // Note: Business rule - at least one of location_id or company_id must be set
            // We'll enforce this in the model and controller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
