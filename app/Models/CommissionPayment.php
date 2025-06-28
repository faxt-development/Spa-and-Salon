<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionPayment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'period_name',
        'start_date',
        'end_date',
        'amount',
        'status',
        'paid_at',
        'notes',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'paid_at',
        'deleted_at',
    ];

    /**
     * Get the staff member this payment is for.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the user who processed this payment.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the performance metrics included in this payment.
     */
    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(StaffPerformanceMetric::class);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include payments for a specific staff member.
     */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to only include payments within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate = null)
    {
        $endDate = $endDate ?: $startDate;
        
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q) use ($startDate, $endDate) {
                  $q->where('start_date', '<=', $startDate)
                    ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Mark the payment as paid.
     */
    public function markAsPaid($userId = null): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'processed_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Calculate the total commission for a staff member in a given period.
     */
    public static function calculateTotalForPeriod($staffId, $startDate, $endDate): float
    {
        return (float) static::forStaff($staffId)
            ->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate)
            ->sum('amount');
    }

    /**
     * Get the URL to view the payment details.
     */
    public function getViewUrlAttribute(): string
    {
        return route('admin.commissions.payments.show', $this->id);
    }

    /**
     * Get the URL to download the payment as PDF.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('admin.commissions.payments.download', $this->id);
    }
}
