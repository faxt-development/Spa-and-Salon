<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'location_id',
        'time_slot_interval',
        'booking_lead_time',
        'cancellation_notice',
        'enforce_cancellation_fee',
        'cancellation_fee',
        'default_padding_time',
        'allow_sequential_booking',
        'allow_time_based_pricing',
        'auto_confirm_appointments',
        'send_customer_reminders',
        'reminder_hours_before',
        'send_staff_notifications',
        'max_future_booking_days',
        'require_customer_login',
        'allow_customer_reschedule',
        'reschedule_notice_hours',
        'enable_waitlist',
        'prevent_double_booking',
        'track_no_shows',
        'max_no_shows_before_warning',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enforce_cancellation_fee' => 'boolean',
        'allow_sequential_booking' => 'boolean',
        'allow_time_based_pricing' => 'boolean',
        'auto_confirm_appointments' => 'boolean',
        'send_customer_reminders' => 'boolean',
        'send_staff_notifications' => 'boolean',
        'require_customer_login' => 'boolean',
        'allow_customer_reschedule' => 'boolean',
        'enable_waitlist' => 'boolean',
        'prevent_double_booking' => 'boolean',
        'track_no_shows' => 'boolean',
    ];

    /**
     * Get the company that owns the appointment settings.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the location that owns the appointment settings.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the formatted time slot interval.
     */
    public function getFormattedTimeSlotIntervalAttribute(): string
    {
        return "{$this->time_slot_interval} minutes";
    }

    /**
     * Get the formatted booking lead time.
     */
    public function getFormattedBookingLeadTimeAttribute(): string
    {
        if ($this->booking_lead_time < 60) {
            return "{$this->booking_lead_time} minutes";
        }
        
        $hours = floor($this->booking_lead_time / 60);
        return "{$hours} " . ($hours === 1 ? 'hour' : 'hours');
    }

    /**
     * Get the formatted cancellation notice.
     */
    public function getFormattedCancellationNoticeAttribute(): string
    {
        return "{$this->cancellation_notice} " . ($this->cancellation_notice === 1 ? 'hour' : 'hours');
    }
}
