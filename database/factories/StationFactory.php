<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        $cities = [
            ['Jakarta', 'DKI Jakarta'],
            ['Bandung', 'Jawa Barat'],
            ['Surabaya', 'Jawa Timur'],
            ['Yogyakarta', 'DI Yogyakarta'],
            ['Semarang', 'Jawa Tengah'],
        ];

        $city = fake()->randomElement($cities);

        return [
            'name' => 'Stasiun ' . $city[0],
            'code' => fake()->unique()->bothify('???'),
            'city' => $city[0],
            'province' => $city[1],
            'is_active' => true,
        ];
    }
}
