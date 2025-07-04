<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessTax extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'tax_type', // 'sales', 'property', 'income', 'other'
        'amount',
        'tax_period_start',
        'tax_period_end',
        'due_date',
        'payment_date',
        'payment_status', // 'pending', 'paid', 'overdue'
        'payment_reference',
        'tax_authority',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'tax_period_start' => 'date',
        'tax_period_end' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the formatted amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get the status class for styling.
     *
     * @return string
     */
    public function getStatusClassAttribute()
    {
        return [
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
        ][$this->payment_status] ?? 'secondary';
    }

    /**
     * Scope a query to only include taxes of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    /**
     * Scope a query to only include taxes with a specific payment status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to only include taxes due within a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include taxes paid within a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaidBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
}
