<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPerformanceMetric extends Model
{
    protected $fillable = [
        'staff_id',
        'metric_date',
        'available_hours',
        'booked_hours',
        'utilization_rate',
        'total_revenue',
        'revenue_per_hour',
        'appointments_completed',
        'average_ticket_value',
        'total_commission',
        'average_commission_rate',
        'new_customers',
        'repeat_customers',
        'customer_satisfaction_score'
    ];

    protected $casts = [
        'metric_date' => 'date',
        'available_hours' => 'decimal:2',
        'booked_hours' => 'decimal:2',
        'utilization_rate' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'revenue_per_hour' => 'decimal:2',
        'average_ticket_value' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'average_commission_rate' => 'decimal:2',
        'customer_satisfaction_score' => 'decimal:1',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function calculateUtilizationRate(): void
    {
        $this->utilization_rate = $this->available_hours > 0 
            ? min(100, ($this->booked_hours / $this->available_hours) * 100) 
            : 0;
    }

    public function calculateRevenuePerHour(): void
    {
        $this->revenue_per_hour = $this->booked_hours > 0 
            ? $this->total_revenue / $this->booked_hours 
            : 0;
    }

    public function calculateAverageTicketValue(): void
    {
        $this->average_ticket_value = $this->appointments_completed > 0 
            ? $this->total_revenue / $this->appointments_completed 
            : 0;
    }
}
