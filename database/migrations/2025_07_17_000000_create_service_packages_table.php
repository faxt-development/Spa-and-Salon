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
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['service_package_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_package_items');
        Schema::dropIfExists('service_packages');
    }
};
