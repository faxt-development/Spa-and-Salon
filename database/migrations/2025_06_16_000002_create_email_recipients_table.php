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
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email');
            $table->string('name');
            $table->enum('status', [
                'pending', 'sending', 'sent', 'opened', 'clicked', 'bounced', 'complained', 'failed'
            ])->default('pending');
            $table->string('token');
            $table->string('unsubscribe_token');
            $table->string('preferences_token');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('complained_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('email');
            $table->index('status');
        });
        
        // Add a fulltext index for searching
        DB::statement('ALTER TABLE email_recipients ADD FULLTEXT fulltext_email (email)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_recipients');
    }
};
