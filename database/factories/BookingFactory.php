<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        // Get a random route from database
        $route = Route::inRandomOrder()->first();

        if (!$route) {
            // Fallback if no routes exist
            return [
                'user_id' => 1,
                'booking_code' => fake()->unique()->bothify('BK########'),
                'rute_id' => null,
                'passengers' => fake()->numberBetween(1, 4),
                'price' => 0,
                'total_price' => 0,
                'status' => Booking::STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_COMPLETED,
            ];
        }

        $passengers = fake()->numberBetween(1, 4);
        $price = (int) $route->price;
        $totalPrice = $price * $passengers;

        return [
            'user_id' => 1,
            'booking_code' => fake()->unique()->bothify('BK########'),
            'rute_id' => $route->id,
            'passengers' => $passengers,
            'price' => $price,
            'total_price' => $totalPrice,
            'status' => fake()->randomElement([Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING]),
            'payment_status' => fake()->randomElement([Booking::PAYMENT_COMPLETED, Booking::PAYMENT_PENDING]),
        ];
    }
}
