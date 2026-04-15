<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'booking_code',
    'origin',
    'destination',
    'schedule_id',
    'arrival_at',
    'passengers',
    'price',
    'total_price',
    'status',
])]
class Booking extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'arrival_at' => 'datetime',
        ];
    }
}
