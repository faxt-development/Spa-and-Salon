<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
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
    ];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name', 'lifetime_value', 'average_visit_value', 'days_since_last_visit'];
    
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
     * Get all payments for the client.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Get the client's lifetime value (total spent).
     */
    public function getLifetimeValueAttribute()
    {
        return $this->total_spent ?? $this->payments()->where('status', 'completed')->sum('amount');
    }
    
    /**
     * Get the average value per visit.
     */
    public function getAverageVisitValueAttribute()
    {
        $visitCount = $this->visit_count ?: $this->appointments()->where('status', 'completed')->count();
        return $visitCount > 0 ? $this->lifetime_value / $visitCount : 0;
    }
    
    /**
     * Get the average days between visits.
     */
    public function getAverageDaysBetweenVisitsAttribute()
    {
        $appointments = $this->appointments()
            ->where('status', 'completed')
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
