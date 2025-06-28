<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_structure_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('applicable_type')->nullable(); // staff, service, product, category
            $table->unsignedBigInteger('applicable_id')->nullable();
            $table->string('condition_type')->default('sales_volume'); // sales_volume, item_count, etc.
            $table->decimal('min_value', 15, 2)->nullable();
            $table->decimal('max_value', 15, 2)->nullable();
            $table->decimal('rate', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['applicable_type', 'applicable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('commission_rules');
    }
};
