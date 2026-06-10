<?php

namespace Database\Factories;

use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Train>
 */
class TrainFactory extends Factory
{
    protected $model = Train::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Argo Bromo', 'Bima', 'Gajayana', 'Turangga', 'Harina']),
            'code' => fake()->unique()->bothify('??###'),
            'type' => fake()->randomElement(['ekonomi', 'bisnis', 'eksekutif']),
            'total_seats' => fake()->randomElement([100, 150, 200, 250]),
            'is_active' => true,
        ];
    }
}
