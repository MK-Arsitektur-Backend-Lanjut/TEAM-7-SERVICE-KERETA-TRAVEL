<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    protected $fillable = [
        'train_id',
        'origin_station_id',
        'destination_station_id',
        'departure_time',
        'arrival_time',
        'duration_minutes',
        'distance_km',
        'price',
        'is_active',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'distance_km' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    /** @return BelongsTo<Train, $this> */
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /** @return BelongsTo<Station, $this> */
    public function originStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'origin_station_id');
    }

    /** @return BelongsTo<Station, $this> */
    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'destination_station_id');
    }

    /**
     * Mendapatkan durasi perjalanan dalam format jam & menit.
     */
    public function getDurationFormattedAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours === 0) {
            return "{$minutes} menit";
        }

        if ($minutes === 0) {
            return "{$hours} jam";
        }

        return "{$hours} jam {$minutes} menit";
    }
}
