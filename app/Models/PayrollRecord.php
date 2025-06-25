<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
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
        'pay_period_start',
        'pay_period_end',
        'payment_date',
        'hours_worked',
        'overtime_hours',
        'gross_amount',
        'tax_amount',
        'deductions',
        'net_amount',
        'payment_method',
        'payment_status',
        'reference_number',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'payment_date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Get the employee that owns the payroll record.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to only include pending payroll records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope a query to only include processed payroll records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessed($query)
    {
        return $query->where('payment_status', 'processed');
    }

    /**
     * Scope a query to only include cancelled payroll records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('payment_status', 'cancelled');
    }

    /**
     * Calculate the total deductions (tax + other deductions).
     *
     * @return float
     */
    public function getTotalDeductionsAttribute()
    {
        return $this->tax_amount + $this->deductions;
    }
}
