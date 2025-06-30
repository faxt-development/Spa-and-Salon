<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'business_hours',
        'timezone',
        'currency',
        'is_active',
        'is_primary',
        'settings',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'business_hours' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the staff members associated with this location.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the services offered at this location.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the appointments scheduled at this location.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the revenue snapshots for this location.
     */
    public function revenueSnapshots(): HasMany
    {
        return $this->hasMany(RevenueSnapshot::class);
    }

    /**
     * Get formatted address.
     *
     * @return string
     */
    public function getFormattedAddressAttribute(): string
    {
        $address = $this->address_line_1;
        
        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }
        
        $address .= ', ' . $this->city . ', ' . $this->state . ' ' . $this->postal_code;
        
        if ($this->country && $this->country !== 'US') {
            $address .= ', ' . $this->country;
        }
        
        return $address;
    }

    /**
     * Get the primary location.
     *
     * @return self|null
     */
    public static function getPrimary(): ?self
    {
        return self::where('is_primary', true)->first();
    }

    /**
     * Get all active locations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        return self::where('is_active', true)->orderBy('name')->get();
    }
}
