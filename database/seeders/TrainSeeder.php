<?php

namespace Database\Seeders;

use App\Models\Train;
use Illuminate\Database\Seeder;

class TrainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trains = [
            ['name' => 'Argo Bromo Anggrek', 'code' => 'ABA-01', 'type' => 'eksekutif', 'total_seats' => 450],
            ['name' => 'Argo Parahyangan', 'code' => 'APH-02', 'type' => 'eksekutif', 'total_seats' => 400],
            ['name' => 'Gajayana', 'code' => 'GJN-03', 'type' => 'eksekutif', 'total_seats' => 350],
            ['name' => 'Bima', 'code' => 'BMA-04', 'type' => 'eksekutif', 'total_seats' => 350],
            ['name' => 'Taksaka', 'code' => 'TKS-05', 'type' => 'eksekutif', 'total_seats' => 350],
            ['name' => 'Fajar Utama YK', 'code' => 'FUY-06', 'type' => 'bisnis', 'total_seats' => 500],
            ['name' => 'Senja Utama YK', 'code' => 'SUY-07', 'type' => 'bisnis', 'total_seats' => 500],
            ['name' => 'Mataram', 'code' => 'MTR-08', 'type' => 'bisnis', 'total_seats' => 450],
            ['name' => 'Bogowonto', 'code' => 'BGW-09', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Gajah Wong', 'code' => 'GJW-10', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Jayakarta', 'code' => 'JKT-11', 'type' => 'ekonomi', 'total_seats' => 700],
            ['name' => 'Kertajaya', 'code' => 'KTJ-12', 'type' => 'ekonomi', 'total_seats' => 800],
            ['name' => 'Brantas', 'code' => 'BRT-13', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Pasundan', 'code' => 'PSD-14', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Kahuripan', 'code' => 'KHR-15', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Progo', 'code' => 'PRG-16', 'type' => 'ekonomi', 'total_seats' => 550],
            ['name' => 'Logawa', 'code' => 'LGW-17', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Sri Tanjung', 'code' => 'STJ-18', 'type' => 'ekonomi', 'total_seats' => 600],
            ['name' => 'Tawang Alun', 'code' => 'TWA-19', 'type' => 'ekonomi', 'total_seats' => 500],
            ['name' => 'Pandalungan', 'code' => 'PDL-20', 'type' => 'eksekutif', 'total_seats' => 400],
        ];

        foreach ($trains as $train) {
            Train::create($train);
        }
    }
}
