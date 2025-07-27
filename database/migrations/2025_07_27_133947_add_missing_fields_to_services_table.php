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
        Schema::table('services', function (Blueprint $table) {
            $table->integer('max_capacity')->default(1)->comment('Maximum number of clients that can book this service at once');
            $table->integer('min_staff_required')->default(1)->comment('Minimum number of staff required to provide this service');
            $table->string('color', 20)->nullable()->comment('Color code for the service (e.g., #FF0000)');
            $table->boolean('requires_approval')->default(false)->comment('Whether this service requires admin approval');
            $table->integer('cancellation_policy_hours')->default(24)->comment('Minimum hours notice required for cancellation');
            $table->decimal('tax_rate', 5, 2)->default(0.00)->comment('Tax rate percentage for this service');
            $table->decimal('commission_rate', 5, 2)->default(0.00)->comment('Commission rate percentage for staff');
            $table->json('resource_requirements')->nullable()->comment('JSON of required resources for this service');
            $table->text('pre_requisites')->nullable()->comment('Any prerequisites for this service');
            $table->text('aftercare_instructions')->nullable()->comment('Aftercare instructions for clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
