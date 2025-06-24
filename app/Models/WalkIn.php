<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalkIn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'party_size',
        'notes',
        'status',
        'estimated_wait_time',
        'check_in_time',
        'service_start_time',
        'service_end_time',
        'service_id',
        'staff_id',
        'client_id',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'service_start_time' => 'datetime',
        'service_end_time' => 'datetime',
        'party_size' => 'integer',
        'estimated_wait_time' => 'integer',
    ];

    protected $appends = [
        'wait_time_minutes',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getWaitTimeMinutesAttribute(): int
    {
        if ($this->service_start_time) {
            return 0;
        }

        return $this->estimated_wait_time ?? 
               ($this->service ? $this->service->duration : 15); // Default 15 minutes if no service specified
    }
}
