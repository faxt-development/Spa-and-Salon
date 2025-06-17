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
        Schema::table('users', function (Blueprint $table) {
            // Personal information fields
            $table->date('birthday')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            
            // Notification preferences
            $table->boolean('sms_notifications')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('appointment_reminders')->default(true);
            $table->boolean('promotional_emails')->default(true);
            
            // Email preferences
            $table->boolean('receive_newsletter')->default(true);
            $table->boolean('receive_special_offers')->default(true);
            $table->boolean('receive_product_updates')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove personal information fields
            $table->dropColumn('birthday');
            $table->dropColumn('phone_number');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip_code');
            
            // Remove notification preferences
            $table->dropColumn('sms_notifications');
            $table->dropColumn('email_notifications');
            $table->dropColumn('appointment_reminders');
            $table->dropColumn('promotional_emails');
            
            // Remove email preferences
            $table->dropColumn('receive_newsletter');
            $table->dropColumn('receive_special_offers');
            $table->dropColumn('receive_product_updates');
        });
    }
};
