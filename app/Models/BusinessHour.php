<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class BusinessHour extends Model
{
    protected $fillable = [
        'location_id',
        'company_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($businessHour) {
            // Ensure at least one of location_id or company_id is set
            if (empty($businessHour->location_id) && empty($businessHour->company_id)) {
                throw ValidationException::withMessages([
                    'location_id' => ['Either location or company must be specified for business hours.']
                ]);
            }
        });
    }

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed' => 'boolean',
    ];

    /**
     * Get the location this business hour belongs to.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the company this business hour belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the day name.
     */
    public function getDayNameAttribute(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week];
    }

    /**
     * Format the open time for display.
     */
    public function getFormattedOpenTimeAttribute(): string
    {
        return date('g:i A', strtotime($this->open_time));
    }

    /**
     * Format the close time for display.
     */
    public function getFormattedCloseTimeAttribute(): string
    {
        return date('g:i A', strtotime($this->close_time));
    }

    /**
     * Get formatted hours (e.g., "9:00 AM - 5:00 PM" or "Closed").
     */
    public function getFormattedHoursAttribute(): string
    {
        if ($this->is_closed) {
            return 'Closed';
        }

        return $this->formatted_open_time . ' - ' . $this->formatted_close_time;
    }
}
