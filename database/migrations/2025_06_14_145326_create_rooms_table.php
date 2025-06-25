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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('room_number')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('room_type')->default('treatment');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
