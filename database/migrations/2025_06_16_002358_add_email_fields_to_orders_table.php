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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('email_sent')->default(false)->after('notes');
            $table->timestamp('email_sent_at')->nullable()->after('email_sent');
            $table->string('customer_email')->nullable()->after('email_sent_at');
            
            // Add index for faster lookups
            $table->index('email_sent');
            $table->index('customer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['email_sent']);
            $table->dropIndex(['customer_email']);
            
            // Then drop columns
            $table->dropColumn(['email_sent', 'email_sent_at', 'customer_email']);
        });
    }
};
