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
        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // welcome_series, birthday_promotion, reengagement
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('configuration'); // Store campaign configuration as JSON
            $table->unsignedInteger('delay_days')->default(0); // Days to wait before sending
            $table->string('subject');
            $table->text('content');
            $table->string('from_email');
            $table->string('from_name');
            $table->string('reply_to')->nullable();
            $table->string('preview_text')->nullable();
            $table->unsignedInteger('sequence_order')->default(0); // Order in the sequence
            $table->unsignedBigInteger('user_id'); // Admin who created the campaign
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drip_campaigns');
    }
};
