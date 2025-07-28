<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;


    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'notes',
        'marketing_consent',
        'last_visit',
        'source',
        'total_spent',
        'visit_count',
        'is_guest',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'marketing_consent' => 'boolean',
        'last_visit' => 'datetime',
        'total_spent' => 'decimal:2',
        'is_guest' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'lifetime_value',
        'average_visit_value',
        'days_since_last_visit',
        'client_since_formatted',
        'last_visit_formatted'
    ];

    /**
     * Get the client's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get all of the appointments for the client.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class)->orderBy('start_time', 'desc');
    }

    /**
     * Get the company that owns the client.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all payments for the client through appointments.
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            Appointment::class,
            'client_id', // Foreign key on appointments table
            'appointment_id', // Foreign key on payments table
            'id', // Local key on clients table
            'id' // Local key on appointments table
        );
    }

    /**
     * Get the client's lifetime value (total spent).
     */
    public function getLifetimeValueAttribute()
    {
        return $this->total_spent ?? $this->payments()->where('payments.status', 'completed')->sum('amount');
    }

    /**
     * Get the average value per visit.
     */
    public function getAverageVisitValueAttribute()
    {
        $visitCount = $this->visit_count ?: $this->appointments()->where('appointments.status', 'completed')->count();
        return $visitCount > 0 ? $this->lifetime_value / $visitCount : 0;
    }

    /**
     * Get the average days between visits.
     */
    public function getAverageDaysBetweenVisitsAttribute()
    {
        $appointments = $this->appointments()
            ->where('appointments.status', 'completed')
            ->orderBy('start_time')
            ->pluck('start_time');

        if ($appointments->count() < 2) return null;

        $totalDays = 0;
        $previous = $appointments->first();

        foreach ($appointments->skip(1) as $appointment) {
            $totalDays += $previous->diffInDays($appointment);
            $previous = $appointment;
        }

        return round($totalDays / ($appointments->count() - 1), 1);
    }

    /**
     * Get days since last visit.
     */
    public function getDaysSinceLastVisitAttribute()
    {
        return $this->last_visit ? now()->diffInDays($this->last_visit) : null;
    }

    /**
     * Get client since date formatted.
     */
    public function getClientSinceFormattedAttribute()
    {
        return $this->created_at->format('M d, Y');
    }

    /**
     * Get last visit date formatted.
     */
    public function getLastVisitFormattedAttribute()
    {
        return $this->last_visit ? $this->last_visit->format('M d, Y') : 'Never';
    }

    /**
     * Get client's spend by category
     */
    public function getSpendByCategory()
    {
        return \App\Models\Appointment::select(
            'services.name as category',
            DB::raw('SUM(appointment_service.price) as total_spent')
        )
        ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
        ->join('services', 'appointment_service.service_id', '=', 'services.id')
        ->where('appointments.client_id', $this->id)
        ->where('appointments.status', 'completed')
        ->groupBy('services.name')
        ->orderBy('total_spent', 'desc')
        ->get();
    }

    /**
     * Get client's payment methods summary
     */
    public function getPaymentMethodsSummary()
    {
        return $this->payments()
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Get client's spend trend over time
     */
    public function getSpendTrend($months = 12)
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths($months - 1)->startOfMonth();

        $payments = $this->payments()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $data = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $key = $currentDate->format('Y-m');
            $data[$key] = [
                'month' => $currentDate->format('M Y'),
                'total' => 0
            ];
            $currentDate->addMonth();
        }

        foreach ($payments as $payment) {
            $key = $payment->year . '-' . str_pad($payment->month, 2, '0', STR_PAD_LEFT);
            if (isset($data[$key])) {
                $data[$key]['total'] = (float) $payment->total;
            }
        }

        return array_values($data);
    }

    /**
     * Scope a query to filter clients by their lifetime value.
     */
    public function scopeWithLifetimeValue($query, $min = null, $max = null)
    {
        return $query->when($min, function($q) use ($min) {
                $q->where('total_spent', '>=', $min);
            })
            ->when($max, function($q) use ($max) {
                $q->where('total_spent', '<=', $max);
            });
    }

    /**
     * Scope a query to filter clients by their visit frequency.
     */
    public function scopeWithVisitFrequency($query, $min = null, $max = null)
    {
        return $query->when($min, function($q) use ($min) {
                $q->where('visit_count', '>=', $min);
            })
            ->when($max, function($q) use ($max) {
                $q->where('visit_count', '<=', $max);
            });
    }
}
