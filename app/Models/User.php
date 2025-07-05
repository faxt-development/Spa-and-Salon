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
     * Get the company associated with the user.
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Get the user's dashboard preferences.
     */
    public function dashboardPreference(): HasOne
    {
        return $this->hasOne(\App\Models\UserDashboardPreference::class);
    }
}
