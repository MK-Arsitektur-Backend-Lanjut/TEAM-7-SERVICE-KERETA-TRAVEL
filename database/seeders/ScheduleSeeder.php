<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Route;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tarik semua data rute dari master table ke dalam RAM
        $routes = Route::with('train')->get();

        // 2. Cegah error kalau tabel routes ternyata kosong
        if ($routes->isEmpty()) {
            $this->command->error('Tabel routes kosong! Jalankan seeder rute dulu.');
            return;
        }

        $this->command->info('Mulai generate 10.000 jadwal... (Tunggu sebentar ya)');

        // 3. Produksi 10.000 jadwal menggunakan data rute yang udah ditarik tadi
        Schedule::factory()
            ->count(10000)
            ->recycle($routes)
            ->create();
            
        $this->command->info('10.000 jadwal berhasil dibuat!');
    }
}