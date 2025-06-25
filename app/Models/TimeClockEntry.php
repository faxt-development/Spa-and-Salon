<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeClockEntry extends Model
{
    use SoftDeletes;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'hours',
        'is_approved',
        'approved_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'hours' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the employee that owns the time clock entry.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the time clock entry.
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include entries that are still clocked in.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('clock_in')->whereNull('clock_out');
    }

    /**
     * Scope a query to only include completed entries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('clock_in')->whereNotNull('clock_out');
    }

    /**
     * Scope a query to only include approved entries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include unapproved entries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnapproved($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Calculate hours worked when clocking out.
     *
     * @return float
     */
    public function calculateHours()
    {
        if ($this->clock_in && $this->clock_out) {
            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;
            
            // Calculate the difference in hours
            $hours = $clockOut->diffInMinutes($clockIn) / 60;
            
            // Round to 2 decimal places
            return round($hours, 2);
        }
        
        return 0;
    }
}
