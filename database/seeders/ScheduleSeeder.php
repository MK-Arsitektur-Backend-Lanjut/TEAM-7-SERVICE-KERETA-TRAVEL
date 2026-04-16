<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        $now = Carbon::now();

        // Kita bikin seeder untuk 10.000 data
        echo "Sedang generate 10.000 data jadwal...\n";

        for ($i = 1; $i <= 10000; $i++) {
            $departure = $now->copy()->addDays(rand(1, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
            $arrival = $departure->copy()->addHours(rand(1, 8)); // Kereta sampe 1-8 jam kemudian

            $data[] = [
                'train_id' => rand(1, 20), // Asumsi kamu sudah punya id kereta 1-20
                'route_id' => rand(1, 10), // Asumsi ada 10 rute standar
                'origin_station_id' => rand(1, 15), // Stasiun asal
                'destination_station_id' => rand(16, 30), // Stasiun tujuan biar gak sama persis
                'departure_time' => $departure,
                'arrival_time' => $arrival,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Kita masukan ke database per 1000 data biar RAM laptop kamu / server aman (enggak out of memory)
            if (count($data) >= 1000) {
                DB::table('schedules')->insert($data);
                $data = []; // Kosongkan lagi array nya
            }
        }
        
        // Sisa data yang belum masuk ke database (kalau misalnya ada sisa)
        if (!empty($data)) {
            DB::table('schedules')->insert($data);
        }

        echo "10.000 Jadwal Selesai Digenerate!\n";
    }
}
