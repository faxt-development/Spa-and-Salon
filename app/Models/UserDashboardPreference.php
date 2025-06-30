<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardPreference extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'layout',
        'filters',
        'theme',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'layout' => 'array',
        'filters' => 'array',
    ];

    /**
     * Get the user that owns the dashboard preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
