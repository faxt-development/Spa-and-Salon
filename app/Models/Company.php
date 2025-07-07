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
        'user_id',
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
     * Get the user that owns the company.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
