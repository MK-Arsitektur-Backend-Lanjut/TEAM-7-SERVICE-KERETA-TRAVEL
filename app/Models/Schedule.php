<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Kereta
    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    // Relasi ke Route (Misal Jakarta - Bandung)
    public function route()
    {
        return $this->belongsTo(Route::class); // Kalau kamu ada model Route
    }

    // Relasi ke Booking (Untuk menghitung kursi yang terpakai)
    public function bookings()
    {
        return $this->hasMany(Booking::class); // Kalau kamu ada model Booking
    }
}
