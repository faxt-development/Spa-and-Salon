<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
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
        'company_id',
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
     * Get the company that owns the location.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

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
     * Get the business hours for this location.
     */
    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class);
    }

    /**
     * Get business hours for a specific day.
     */
    public function getBusinessHoursForDay(int $dayOfWeek)
    {
        $locationHours = $this->businessHours()->where('day_of_week', $dayOfWeek)->first();

        // If no location-specific hours, fall back to company-wide hours
        if (!$locationHours && $this->company) {
            $companyHours = $this
                ->company
                ->businessHours()
                ->whereNull('location_id')
                ->where('day_of_week', $dayOfWeek)
                ->first();

            return $companyHours;
        }

        return $locationHours;
    }

    /**
     * Check if the location is open at a specific date and time.
     */
    public function isOpenAt(\DateTime $dateTime): bool
    {
        $dayOfWeek = (int) $dateTime->format('w');  // 0 (Sunday) to 6 (Saturday)
        $timeString = $dateTime->format('H:i:s');

        $hours = $this->getBusinessHoursForDay($dayOfWeek);

        if (!$hours || $hours->is_closed) {
            return false;
        }

        return $timeString >= $hours->open_time && $timeString < $hours->close_time;
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
