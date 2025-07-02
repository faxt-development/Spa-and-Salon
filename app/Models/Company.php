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
        'logo',
        'description',
    ];

    /**
     * Get the user that owns the company.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
