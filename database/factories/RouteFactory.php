<?php

namespace Database\Factories;

use App\Models\Route;
use App\Models\Train;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $train = Train::inRandomOrder()->first() ?? Train::factory()->create();
        $origin = Station::inRandomOrder()->first() ?? Station::factory()->create();
        $destination = Station::where('id', '!=', $origin->id)->inRandomOrder()->first()
            ?? Station::factory()->create();

        $durationMinutes = fake()->numberBetween(60, 480);
        $departureTime = fake()->dateTimeBetween('now', '+7 days');
        $arrivalTime = (clone $departureTime)->modify("+{$durationMinutes} minutes");

        return [
            'train_id' => $train->id,
            'origin_station_id' => $origin->id,
            'destination_station_id' => $destination->id,
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'duration_minutes' => $durationMinutes,
            'distance_km' => fake()->randomFloat(2, 50, 800),
            'price' => fake()->numberBetween(50000, 500000),
            'is_active' => true,
        ];
    }
}
