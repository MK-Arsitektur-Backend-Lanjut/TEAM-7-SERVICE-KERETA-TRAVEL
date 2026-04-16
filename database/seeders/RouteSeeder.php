<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\Station;
use App\Models\Train;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainIds = Train::pluck('id')->toArray();
        $stationIds = Station::pluck('id')->toArray();

        if (empty($trainIds) || count($stationIds) < 2) {
            $this->command->error('Trains and at least 2 Stations must be seeded first.');

            return;
        }

        $totalRoutes = 10000;
        $batchSize = 1000;
        $now = now();

        for ($i = 0; $i < $totalRoutes; $i += $batchSize) {
            $data = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $originId = $stationIds[array_rand($stationIds)];
                $destinationId = $stationIds[array_rand($stationIds)];

                while ($originId === $destinationId) {
                    $destinationId = $stationIds[array_rand($stationIds)];
                }

                $departureTime = Carbon::createFromTime(rand(0, 23), rand(0, 59));
                $duration = rand(60, 720); // 1-12 hours
                $arrivalTime = (clone $departureTime)->addMinutes($duration);

                $data[] = [
                    'train_id' => $trainIds[array_rand($trainIds)],
                    'origin_station_id' => $originId,
                    'destination_station_id' => $destinationId,
                    'departure_time' => $departureTime->format('H:i:s'),
                    'arrival_time' => $arrivalTime->format('H:i:s'),
                    'duration_minutes' => $duration,
                    'distance_km' => rand(50, 1000),
                    'price' => rand(50, 800) * 1000,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Route::insert($data);
            $this->command->info('Inserted batch '.(($i / $batchSize) + 1));
        }
    }
}
