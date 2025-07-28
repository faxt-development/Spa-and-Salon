<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'welcome', 'promotional', 'transactional', 'newsletter', 'other'
            ])->default('promotional');
            $table->string('subject');
            $table->longText('content');
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('segment')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', [
                'draft', 'scheduled', 'sending', 'sent', 'cancelled', 'active'
            ])->default('draft');
            $table->boolean('is_template')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
