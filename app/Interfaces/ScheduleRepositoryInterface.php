<?php

namespace App\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface ScheduleRepositoryInterface
{
    /**
     * Cari jadwal berdasarkan filter keberangkatan.
     *
     * @param array<string, mixed> $filters
     *   - origin_station_id      (int)    stasiun asal
     *   - destination_station_id (int)    stasiun tujuan
     *   - date                   (string) tanggal keberangkatan, format Y-m-d
     *   - time_from              (string) jam mulai keberangkatan, format H:i  (contoh: "06:00")
     *   - time_to                (string) jam akhir keberangkatan, format H:i  (contoh: "12:00")
     *   - train_type             (string) tipe kereta: ekonomi | bisnis | eksekutif
     *   - max_price              (float)  harga tiket maksimum
     *   - only_available         (bool)   hanya tampilkan jadwal yang masih ada kursi
     *   - sort_by                (string) urutan: departure_time | price | duration_minutes (default: departure_time)
     *   - sort_dir               (string) arah: asc | desc (default: asc)
     *   - per_page               (int)    jumlah per halaman (default 15, maks 100)
     */
    public function search(array $filters): LengthAwarePaginator;

    /**
     * Cek ketersediaan kursi untuk satu jadwal (route).
     *
     * @return array{
     *   route_id: int,
     *   train: string,
     *   total_seats: int,
     *   booked_seats: int,
     *   available_seats: int,
     *   is_available: bool
     * }
     */
    public function checkSeats(int $routeId): array;
}
