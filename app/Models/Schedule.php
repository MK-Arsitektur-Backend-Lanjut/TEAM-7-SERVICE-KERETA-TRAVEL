<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'train_id',
        'origin_station_id',
        'destination_station_id',
        'departure_date',
        'departure_time',
        'arrival_time',
        'duration_minutes',
        'price',
        'total_seats',
        'available_seats',
        'is_active',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'total_seats' => 'integer',
        'available_seats' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Route, $this> */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

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

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
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
