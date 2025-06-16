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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('rate', 8, 4); // Store rate as decimal (e.g., 8.25 for 8.25%)
            $table->string('type')->default('sales'); // sales, vat, gst, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inclusive')->default(false); // Whether the tax is included in the price
            $table->text('description')->nullable();
            $table->json('applies_to')->nullable(); // JSON array of product/service IDs or categories
            $table->dateTime('effective_from')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
