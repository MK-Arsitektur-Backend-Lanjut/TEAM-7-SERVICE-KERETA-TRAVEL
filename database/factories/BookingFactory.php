<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $arrival = fake()->dateTimeBetween('-30 days', '+30 days');

        $price = fake()->numberBetween(50_000, 300_000);
        $passengers = fake()->numberBetween(1, 4);

        return [
            'booking_code' => fake()->unique()->bothify('BK########'),
            'origin' => fake()->city(),
            'destination' => fake()->city(),
            'schedule_id' => fake()->numberBetween(1, 5000),
            'arrival_at' => $arrival,
            'passengers' => $passengers,
            'price' => $price,
            'total_price' => $price * $passengers,
            'status' => 'confirmed',
        ];
    }
}
