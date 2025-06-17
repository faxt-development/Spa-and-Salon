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
        Schema::create('drip_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drip_campaign_id');
            $table->unsignedBigInteger('client_id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token', 64)->unique(); // For tracking opens/clicks
            $table->string('unsubscribe_token', 64)->unique(); // For unsubscribe links
            $table->string('preferences_token', 64)->unique(); // For preferences page
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('last_clicked_url')->nullable();
            $table->string('unsubscribed_ip')->nullable();
            $table->json('merge_data')->nullable(); // For personalization
            $table->timestamps();
            
            $table->foreign('drip_campaign_id')->references('id')->on('drip_campaigns')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drip_campaign_recipients');
    }
};
