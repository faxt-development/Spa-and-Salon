<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staff';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'bio',
        'profile_image',
        'active',
        'work_start_time',
        'work_end_time',
        'work_days',
        'user_id',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'work_days' => 'array',
    ];
    
    /**
     * Get the user associated with the staff member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the appointments for the staff member.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    
    /**
     * Get the services that the staff member can perform.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
    
    /**
     * Get the staff member's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
