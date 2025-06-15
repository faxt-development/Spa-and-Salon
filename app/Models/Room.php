<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'room_number',
        'floor',
        'capacity',
        'is_active',
        'room_type',
        'hourly_rate',
        'daily_rate',
        'features',
        'notes',
        'image_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'features' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * Room type constants.
     */
    public const TYPE_TREATMENT = 'treatment';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_EVENT = 'event';
    public const TYPE_OTHER = 'other';

    /**
     * Get the appointments scheduled for this room.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * The staff members who can use this room.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'room_staff')
            ->withTimestamps();
    }

    /**
     * The services that can be performed in this room.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'room_services')
            ->withTimestamps();
    }

    /**
     * Check if the room is available for a given time period.
     *
     * @param \Carbon\Carbon $startTime
     * @param \Carbon\Carbon $endTime
     * @param int|null $excludeAppointmentId
     * @return bool
     */
    public function isAvailable($startTime, $endTime, $excludeAppointmentId = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

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
     * Get the room's full name including floor and room number if available.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        
        if ($this->room_number) {
            $name .= ' (#' . $this->room_number . ')';
        }
        
        if ($this->floor) {
            $name .= ' - Floor ' . $this->floor;
        }
        
        return $name;
    }
}
