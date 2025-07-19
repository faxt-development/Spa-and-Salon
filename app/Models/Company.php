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
     * The service categories that belong to this company.
     */
    public function serviceCategories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'company_service_category')
            ->withTimestamps()
            ->withPivot('deleted_at');
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
     * Get the staff members associated with the company through locations.
     */
    public function locationStaff()
    {
        return $this->hasManyThrough(Staff::class, Location::class);
    }

    /**
     * Get the staff members associated with the company through user roles.
     */
    public function userStaff()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary', 'role')
            ->wherePivot('role', 'staff')
            ->withTimestamps();
    }

    /**
     * Get all staff members associated with the company through the company_user pivot table.
     * 
     * This method finds users with the 'staff' role in the company_user pivot table
     * and then returns staff records that are linked to those users.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function staff()
    {
        // Get user IDs with 'staff' role for this company
        $staffUserIds = $this->belongsToMany(User::class)
            ->wherePivot('role', 'staff')
            ->pluck('users.id');
            
        // Return staff records linked to these users
        return Staff::whereIn('user_id', $staffUserIds);
    }
    
    /**
     * Get the payment methods available for this company.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'company_payment_method')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Get the company-wide business hours.
     */
    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class);
    }
}
