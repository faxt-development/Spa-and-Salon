<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Staff extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'bio',
        'profile_image',
        'active',
        'work_start_time',
        'work_end_time',
        'work_days',
        'user_id',
        'termination_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'salary',
        'commission_rate',
        'specialties',
        'certifications',
        'languages',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'work_days' => 'array',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'specialties' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'termination_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Default work days if not specified.
     *
     * @var array
     */
    protected $defaultWorkDays = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
    ];

    /**
     * Get the user account associated with the staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee record associated with the staff member.
     * This is a one-to-one relationship where the employees table has a staff_id foreign key.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'staff_id');
    }

    /**
     * Get the appointments for the staff member.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    /**
     * Get the services this staff member can perform.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->withPivot('price_override', 'duration_override', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the rooms assigned to this staff member.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_staff')
            ->withTimestamps();
    }

    /**
     * Get the payments processed by this staff member.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the staff member's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the staff member's work schedule for a given date.
     *
     * @param Carbon|null $date
     * @return array
     */
    public function getScheduleForDate(?Carbon $date = null): array
    {
        $date = $date ?: now();
        $dayName = strtolower($date->format('l'));

        $workDays = $this->work_days ?: $this->defaultWorkDays;
        $isWorkDay = in_array($dayName, $workDays);

        return [
            'is_working' => $isWorkDay,
            'start_time' => $isWorkDay ? $this->work_start_time : null,
            'end_time' => $isWorkDay ? $this->work_end_time : null,
            'day_name' => $dayName,
        ];
    }

    /**
     * Check if the staff member is available for an appointment at a given time.
     *
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $excludeAppointmentId
     * @return bool
     */
    public function isAvailable(Carbon $startTime, Carbon $endTime, ?int $excludeAppointmentId = null): bool
    {
        if (!$this->active) {
            return false;
        }

        // Check if within working hours
        $schedule = $this->getScheduleForDate($startTime);
        if (!$schedule['is_working']) {
            return false;
        }

        $workStart = Carbon::parse($schedule['start_time']);
        $workEnd = Carbon::parse($schedule['end_time']);

        $appointmentStart = $startTime->copy()->setDate($workStart->year, $workStart->month, $workStart->day);
        $appointmentEnd = $endTime->copy()->setDate($workEnd->year, $workEnd->month, $workEnd->day);

        if ($appointmentStart->lt($workStart) || $appointmentEnd->gt($workEnd)) {
            return false;
        }

        // Check for overlapping appointments
        $query = $this->appointments()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return !$query->exists();
    }

    /**
     * Get the staff member's upcoming appointments.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function upcomingAppointments(int $limit = 5)
    {
        return $this->appointments()
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the staff member's availability for a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $intervalMinutes
     * @return array
     */
    public function getAvailability(Carbon $startDate, Carbon $endDate, int $intervalMinutes = 30): array
    {
        $availability = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $schedule = $this->getScheduleForDate($currentDate);

            if ($schedule['is_working']) {
                $startTime = Carbon::parse($schedule['start_time']);
                $endTime = Carbon::parse($schedule['end_time']);

                $slots = [];
                $currentSlot = $startTime->copy();

                while ($currentSlot->addMinutes($intervalMinutes)->lte($endTime)) {
                    $slotEnd = $currentSlot->copy()->addMinutes($intervalMinutes);
                    $slots[] = [
                        'start' => $currentSlot->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'available' => $this->isAvailable($currentSlot, $slotEnd),
                    ];
                    $currentSlot = $slotEnd->copy();
                }

                $availability[$currentDate->format('Y-m-d')] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $currentDate->format('l'),
                    'is_working' => true,
                    'slots' => $slots,
                ];
            } else {
                $availability[$currentDate->format('Y-m-d')] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $currentDate->format('l'),
                    'is_working' => false,
                    'slots' => [],
                ];
            }

            $currentDate->addDay();
        }

        return $availability;
    }

    /**
     * Scope a query to only include active staff members.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include staff members who provide a specific service.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $serviceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('service_id', $serviceId);
        });
    }

    // Relationship methods are defined above with proper return type hints
}
