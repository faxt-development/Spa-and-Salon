<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'staff_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'total_price',
        'is_paid',
        'cancellation_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_price' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /**
     * Get the client that owns the appointment.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the staff member that owns the appointment.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the services for the appointment.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('price', 'duration')
            ->withTimestamps();
    }

    /**
     * Get the products for the appointment.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    /**
     * Get the payments for the appointment.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the duration of the appointment in minutes.
     *
     * @return int
     */
    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
}
