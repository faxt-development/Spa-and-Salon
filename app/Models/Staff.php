<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Staff extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;

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
        'commission_rate',
        'commission_structure_id',
    ];

    /**
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'position', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Staff member {$this->full_name} was {$eventName}")
            ->useLogName('staff');
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'work_days' => 'array',
        'commission_rate' => 'decimal:2',
        'commission_structure_id' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
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
     * Get the company this staff member belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user account associated with the staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the commission structure assigned to this staff member.
     */
    public function commissionStructure(): BelongsTo
    {
        return $this->belongsTo(CommissionStructure::class, 'commission_structure_id');
    }

    /**
     * Get the performance metrics for this staff member.
     */
    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(StaffPerformanceMetric::class);
    }

    /**
     * Get the commission payments for the staff member.
     */
    public function commissionPayments(): HasMany
    {
        return $this->hasMany(CommissionPayment::class);
    }

    /**
     * Get the commission rules that apply to this staff member.
     */
    public function commissionRules(): MorphMany
    {
        return $this->morphMany(CommissionRule::class, 'applicable');
    }

    /**
     * Get the services this staff member is qualified to perform.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->withPivot('price_override', 'duration_override', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the transactions for this staff member.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate the commission for a given amount based on the staff's commission structure.
     *
     * @param float $amount
     * @param array $context Additional context for commission calculation
     * @return array [
     *     'amount' => float,
     *     'rate' => float,
     *     'rule' => CommissionRule|null
     * ]
     */
    public function calculateCommission(float $amount, array $context = []): array
    {
        // Default to staff's personal commission rate if no structure is assigned
        if (!$this->commissionStructure) {
            return [
                'amount' => $amount * ($this->commission_rate / 100),
                'rate' => $this->commission_rate,
                'rule' => null
            ];
        }

        // Get applicable rule from the commission structure
        $rule = $this->commissionStructure->getApplicableRule(array_merge([
            'applicable_type' => 'staff',
            'applicable_id' => $this->id,
        ], $context));

        // If no specific rule applies, use the structure's default rate
        $rate = $rule ? $rule->rate : $this->commissionStructure->default_rate;

        return [
            'amount' => $amount * ($rate / 100),
            'rate' => $rate,
            'rule' => $rule
        ];
    }

    /**
     * Calculate the staff member's utilization rate for a given date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array [
     *     'available_hours' => float,
     *     'booked_hours' => float,
     *     'utilization_rate' => float
     * ]
     */
    public function calculateUtilization(Carbon $startDate, Carbon $endDate): array
    {
        // Calculate available working hours
        $availableHours = $this->calculateAvailableHours($startDate, $endDate);

        // Calculate booked hours from appointments
        $bookedHours = $this->appointments()
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600'));

        $utilizationRate = $availableHours > 0
            ? min(100, ($bookedHours / $availableHours) * 100)
            : 0;

        return [
            'available_hours' => round($availableHours, 2),
            'booked_hours' => round($bookedHours, 2),
            'utilization_rate' => round($utilizationRate, 2)
        ];
    }

    /**
     * Calculate available working hours for a date range.
     */
    protected function calculateAvailableHours(Carbon $startDate, Carbon $endDate): float
    {
        $totalHours = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dayOfWeek = strtolower($date->format('l'));

            // Skip if not a working day
            if (!in_array($dayOfWeek, $this->work_days ?? $this->defaultWorkDays)) {
                continue;
            }

            // Calculate working hours for the day
            if ($this->work_start_time && $this->work_end_time) {
                $start = Carbon::parse($this->work_start_time);
                $end = Carbon::parse($this->work_end_time);
                $totalHours += $end->diffInHours($start);
            }
        }

        return $totalHours;
    }

    /**
     * Generate performance metrics for a specific date.
     */
    public function generatePerformanceMetrics(\DateTimeInterface $date): StaffPerformanceMetric
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Get utilization data
        $utilization = $this->calculateUtilization($startOfDay, $endOfDay);

        // Get revenue data
        $revenueData = $this->transactions()
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->select([
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT id) as transaction_count'),
                DB::raw('SUM(commission_amount) as total_commission')
            ])
            ->first();

        // Get appointment data
        $appointmentData = $this->appointments()
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->select([
                DB::raw('COUNT(DISTINCT id) as appointments_completed'),
                DB::raw('COUNT(DISTINCT client_id) as unique_clients')
            ])
            ->first();

        // Calculate metrics
        $revenuePerHour = $utilization['booked_hours'] > 0
            ? $revenueData->total_revenue / $utilization['booked_hours']
            : 0;

        $averageTicketValue = $appointmentData->appointments_completed > 0
            ? $revenueData->total_revenue / $appointmentData->appointments_completed
            : 0;

        $averageCommissionRate = $revenueData->total_revenue > 0
            ? ($revenueData->total_commission / $revenueData->total_revenue) * 100
            : 0;

        // Create or update the performance metric
        return $this->performanceMetrics()->updateOrCreate(
            ['metric_date' => $date],
            [
                'available_hours' => $utilization['available_hours'],
                'booked_hours' => $utilization['booked_hours'],
                'utilization_rate' => $utilization['utilization_rate'],
                'total_revenue' => $revenueData->total_revenue ?? 0,
                'revenue_per_hour' => $revenuePerHour,
                'appointments_completed' => $appointmentData->appointments_completed ?? 0,
                'average_ticket_value' => $averageTicketValue,
                'total_commission' => $revenueData->total_commission ?? 0,
                'average_commission_rate' => $averageCommissionRate,
                'new_customers' => 0, // Would need customer tracking to implement
                'repeat_customers' => 0, // Would need customer tracking to implement
            ]
        );
    }

    /**
     * Get the location this staff member belongs to.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
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
            'day_name' => $dayName
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
