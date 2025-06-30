<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'settings',
        'position',
        'is_visible',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_visible' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the user that owns the dashboard widget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
