<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = [
        'name',
        'code',
        'city',
        'province',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return HasMany<Route, $this> */
    public function departingRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'origin_station_id');
    }

    /** @return HasMany<Route, $this> */
    public function arrivingRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'destination_station_id');
    }
}
