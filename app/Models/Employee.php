<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use SoftDeletes;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'staff_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'employment_type',
        'hire_date',
        'termination_date',
        'hourly_rate',
        'salary',
        'payment_frequency',
        'tax_id',
        'address',
        'emergency_contact',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with the employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff member associated with the employee.
     * This is the inverse of the one-to-one relationship defined in the Staff model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the payroll records for the employee.
     */
    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    /**
     * Get the time clock entries for the employee.
     */
    public function timeClockEntries()
    {
        return $this->hasMany(TimeClockEntry::class);
    }

    /**
     * Get the full name of the employee.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include active employees.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the employee's current time clock entry (if clocked in).
     *
     * @return \App\Models\TimeClockEntry|null
     */
    public function getCurrentTimeClockEntry()
    {
        return $this->timeClockEntries()
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
    }

    /**
     * Check if the employee is currently clocked in.
     *
     * @return bool
     */
    public function isClockedIn()
    {
        return $this->getCurrentTimeClockEntry() !== null;
    }
}
