<?php

namespace Database\Seeders;

use App\Models\Station;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            ['name' => 'Gambir', 'code' => 'GMR', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta'],
            ['name' => 'Pasar Senen', 'code' => 'PSE', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta'],
            ['name' => 'Bandung', 'code' => 'BD', 'city' => 'Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Kiaracondong', 'code' => 'KAC', 'city' => 'Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Cirebon', 'code' => 'CN', 'city' => 'Cirebon', 'province' => 'Jawa Barat'],
            ['name' => 'Semarang Tawang', 'code' => 'SMT', 'city' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Semarang Poncol', 'code' => 'SMC', 'city' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Yogyakarta', 'code' => 'YK', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Lempuyangan', 'code' => 'LPN', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Solo Balapan', 'code' => 'SLO', 'city' => 'Surakarta', 'province' => 'Jawa Tengah'],
            ['name' => 'Surabaya Gubeng', 'code' => 'SGU', 'city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Surabaya Pasarturi', 'code' => 'SBI', 'city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Malang', 'code' => 'ML', 'city' => 'Malang', 'province' => 'Jawa Timur'],
            ['name' => 'Banyuwangi Kota', 'code' => 'BWI', 'city' => 'Banyuwangi', 'province' => 'Jawa Timur'],
            ['name' => 'Ketapang', 'code' => 'KTG', 'city' => 'Banyuwangi', 'province' => 'Jawa Timur'],
            ['name' => 'Madiun', 'code' => 'MN', 'city' => 'Madiun', 'province' => 'Jawa Timur'],
            ['name' => 'Jember', 'code' => 'JR', 'city' => 'Jember', 'province' => 'Jawa Timur'],
            ['name' => 'Purwokerto', 'code' => 'PWT', 'city' => 'Banyumas', 'province' => 'Jawa Tengah'],
            ['name' => 'Tegal', 'code' => 'TG', 'city' => 'Tegal', 'province' => 'Jawa Tengah'],
            ['name' => 'Pekalongan', 'code' => 'PK', 'city' => 'Pekalongan', 'province' => 'Jawa Tengah'],
            ['name' => 'Cilacap', 'code' => 'CP', 'city' => 'Cilacap', 'province' => 'Jawa Tengah'],
            ['name' => 'Kebumen', 'code' => 'KM', 'city' => 'Kebumen', 'province' => 'Jawa Tengah'],
            ['name' => 'Kutoarjo', 'code' => 'KTA', 'city' => 'Purworejo', 'province' => 'Jawa Tengah'],
            ['name' => 'Klaten', 'code' => 'KT', 'city' => 'Klaten', 'province' => 'Jawa Tengah'],
            ['name' => 'Sragen', 'code' => 'SR', 'city' => 'Sragen', 'province' => 'Jawa Tengah'],
            ['name' => 'Ngawi', 'code' => 'NGW', 'city' => 'Ngawi', 'province' => 'Jawa Timur'],
            ['name' => 'Nganjuk', 'code' => 'NJ', 'city' => 'Nganjuk', 'province' => 'Jawa Timur'],
            ['name' => 'Kediri', 'code' => 'KD', 'city' => 'Kediri', 'province' => 'Jawa Timur'],
            ['name' => 'Tulungagung', 'code' => 'TA', 'city' => 'Tulungagung', 'province' => 'Jawa Timur'],
            ['name' => 'Blitar', 'code' => 'BL', 'city' => 'Blitar', 'province' => 'Jawa Timur'],
            ['name' => 'Sidoarjo', 'code' => 'SDA', 'city' => 'Sidoarjo', 'province' => 'Jawa Timur'],
            ['name' => 'Mojokerto', 'code' => 'MR', 'city' => 'Mojokerto', 'province' => 'Jawa Timur'],
            ['name' => 'Jombang', 'code' => 'JG', 'city' => 'Jombang', 'province' => 'Jawa Timur'],
            ['name' => 'Bojonegoro', 'code' => 'BJ', 'city' => 'Bojonegoro', 'province' => 'Jawa Timur'],
            ['name' => 'Lamongan', 'code' => 'LMG', 'city' => 'Lamongan', 'province' => 'Jawa Timur'],
            ['name' => 'Cepu', 'code' => 'CU', 'city' => 'Blora', 'province' => 'Jawa Tengah'],
            ['name' => 'Bekasi', 'code' => 'BKS', 'city' => 'Bekasi', 'province' => 'Jawa Barat'],
            ['name' => 'Jatinegara', 'code' => 'JNG', 'city' => 'Jakarta Timur', 'province' => 'DKI Jakarta'],
        ];

        foreach ($stations as $station) {
            Station::create($station);
        }

        // Tambah extra stasiun tanpa factory
        $faker = Faker::create();
        for ($i = 0; $i < 12; $i++) {
            Station::create([
                'name' => 'Stasiun '.$faker->city(),
                'code' => strtoupper($faker->unique()->lexify('???')),
                'city' => $faker->city(),
                'province' => $faker->state(),
                'is_active' => true,
            ]);
        }
    }
}
