<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use SoftDeletes;

    use HasFactory, Notifiable, HasRoles;
    use HasApiTokens;
    /** @use HasFactory<\Database\Factories\UserFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'sms_notifications',
        'email_notifications',
        'appointment_reminders',
        'promotional_emails',
        'receive_newsletter',
        'receive_special_offers',
        'receive_product_updates',
        'onboarding_completed',
        'stripe_session_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'sms_notifications' => 'boolean',
            'email_notifications' => 'boolean',
            'appointment_reminders' => 'boolean',
            'promotional_emails' => 'boolean',
            'receive_newsletter' => 'boolean',
            'receive_special_offers' => 'boolean',
            'receive_product_updates' => 'boolean',
            'onboarding_completed' => 'boolean',
            'onboarding_checklist_items' => 'array',
        ];
    }

     /**
     * Get the products supplied by this supplier.
     */
    public function giftcards(): HasMany
    {
        return $this->hasMany(GiftCard::class);
    }

    /**
     * Get the user's clients.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'user_id');
    }

    /**
     * Get the user's dashboard widgets.
     */
    public function dashboardWidgets(): HasMany
    {
        return $this->hasMany(\App\Models\DashboardWidget::class);
    }
    
    /**
     * Get the companies associated with the user.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('is_primary', 'role')
            ->withTimestamps();
    }
    
    /**
     * Get the primary company associated with the user.
     */
    public function primaryCompany()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('is_primary', 'role')
            ->wherePivot('is_primary', true)
            ->first();
    }
    
    /**
     * Legacy method for backward compatibility.
     * @deprecated Use primaryCompany() instead
     */
    public function company()
    {
        // For backward compatibility, we'll keep this method
        // but it should be updated throughout the codebase
        // Must return a relationship instance, not the result of a method call
        return $this->belongsToMany(Company::class)
            ->withPivot('is_primary', 'role')
            ->wherePivot('is_primary', true)
            ->withTimestamps();
    }

    /**
     * Get the user's dashboard preferences.
     */
    public function dashboardPreference(): HasOne
    {
        return $this->hasOne(\App\Models\UserDashboardPreference::class);
    }

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if the user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->exists();
    }
}
