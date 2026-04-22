<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

#[Fillable([
    'user_id',
    'booking_code',
    'rute_id',
    'passengers',
    'price',
    'total_price',
    'status',
    'payment_status',
])]
class Booking extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_COMPLETED = 'completed';
    const PAYMENT_FAILED = 'failed';

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Rute (schedule)
     * Ambil departure_time, arrival_time, origin, destination dari sini
     */
    public function rute(): BelongsTo
    {
        return $this->belongsTo(Rute::class, 'rute_id');
    }

    /**
     * Casting
     */
    protected function casts(): array
    {
        return [
            'passengers' => 'integer',
            'price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Accessors - ambil dari relasi Rute
     */
    public function getDepartureAtAttribute()
    {
        return $this->rute?->departure_time;
    }

    public function getArrivalAtAttribute()
    {
        return $this->rute?->arrival_time;
    }

    public function getOriginAttribute()
    {
        return $this->rute?->origin_station_id;
    }

    public function getDestinationAttribute()
    {
        return $this->rute?->destination_station_id;
    }

    public function getDurationAttribute()
    {
        return $this->rute?->duration_minutes;
    }

    /**
     * Scopes
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereHas('rute', function ($q) {
            $q->where('departure_time', '>', now());
        })->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function scopePast($query)
    {
        return $query->whereHas('rute', function ($q) {
            $q->where('departure_time', '<=', now());
        });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PENDING]);
    }

    /**
     * Check if booking can be cancelled
     */
    public function getCanCancelAttribute(): bool
    {
        $departureTime = $this->rute?->departure_time;
        return $this->status === self::STATUS_CONFIRMED 
            && $departureTime 
            && Carbon::parse($departureTime)->diffInHours(now()) > 24;
    }
}
