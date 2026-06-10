<?php

namespace Database\Factories;

use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $route = Route::with('train')->inRandomOrder()->first() ?? Route::factory()->create();
        $departureTime = fake()->dateTimeBetween('now', '+30 days');
        $durationMinutes = $route->duration_minutes;
        $arrivalTime = (clone $departureTime)->modify("+{$durationMinutes} minutes");
        $totalSeats = $route->train->total_seats ?? 100;
        $availableSeats = fake()->numberBetween(0, $totalSeats);

        return [
            'route_id' => $route->id,
            'train_id' => $route->train_id,
            'origin_station_id' => $route->origin_station_id,
            'destination_station_id' => $route->destination_station_id,
            'departure_date' => $departureTime->format('Y-m-d'),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'duration_minutes' => $durationMinutes,
            'price' => $route->price,
            'total_seats' => $totalSeats,
            'available_seats' => $availableSeats,
            'is_active' => $route->is_active,
        ];
    }
}
