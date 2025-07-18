<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'theme_id',
        'name',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'website',
        'domain',
        'is_primary_domain',
        'homepage_content',
        'theme_settings',
        'logo',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary_domain' => 'boolean',
        'homepage_content' => 'json',
        'theme_settings' => 'json',
    ];

    /**
     * Get the locations for the company.
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * The services that belong to this company.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'company_service');
    }

    /**
     * Get all users associated with the company.
     */
    public function users()
    {
        return $this
            ->belongsToMany(User::class)
            ->withPivot('is_primary', 'role')
            ->withTimestamps();
    }

    /**
     * Get the primary admin user that owns the company.
     */
    public function owner()
    {
        return $this
            ->belongsToMany(User::class)
            ->withPivot('is_primary', 'role')
            ->wherePivot('is_primary', true)
            ->wherePivot('role', 'admin')
            ->first();
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use owner() instead
     */
    public function user()
    {
        // For backward compatibility, we'll keep this method
        // Must return a relationship instance, not the result of a method call
        return $this
            ->belongsToMany(User::class)
            ->withPivot('is_primary', 'role')
            ->wherePivot('role', 'admin')
            ->withTimestamps();
    }

    /**
     * Get the theme associated with the company.
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Get the company-wide business hours.
     */
    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class);
    }
}
