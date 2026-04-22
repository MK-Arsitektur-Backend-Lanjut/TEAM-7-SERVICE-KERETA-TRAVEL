<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'total_seats',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_seats' => 'integer',
    ];

    /** @return HasMany<Route, $this> */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
