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
        Schema::create('appointment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');
            
            // Core Appointment Settings
            $table->integer('time_slot_interval')->default(30); // in minutes
            $table->integer('booking_lead_time')->default(60); // in minutes
            $table->integer('cancellation_notice')->default(24); // in hours
            $table->boolean('enforce_cancellation_fee')->default(false);
            $table->decimal('cancellation_fee', 8, 2)->nullable();
            
            // Service Configuration
            $table->integer('default_padding_time')->default(0); // in minutes
            $table->boolean('allow_sequential_booking')->default(true);
            $table->boolean('allow_time_based_pricing')->default(false);
            
            // Notifications & Reminders
            $table->boolean('auto_confirm_appointments')->default(true);
            $table->boolean('send_customer_reminders')->default(true);
            $table->integer('reminder_hours_before')->default(24);
            $table->boolean('send_staff_notifications')->default(true);
            
            // Booking Experience
            $table->integer('max_future_booking_days')->default(60);
            $table->boolean('require_customer_login')->default(false);
            $table->boolean('allow_customer_reschedule')->default(true);
            $table->integer('reschedule_notice_hours')->default(24);
            
            // Analytics & Safeguards
            $table->boolean('enable_waitlist')->default(false);
            $table->boolean('prevent_double_booking')->default(true);
            $table->boolean('track_no_shows')->default(true);
            $table->integer('max_no_shows_before_warning')->default(2);
            
            $table->timestamps();
            
            // Only one setting per company-location combination
            $table->unique(['company_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_settings');
    }
};
