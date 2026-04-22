<?php

namespace App\Repositories;

use App\Interfaces\ScheduleRepositoryInterface;
use App\Models\Booking;
use App\Models\Route;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * Cari jadwal kereta berdasarkan filter.
     * Menggunakan tabel routes karena departure_time & arrival_time sudah ada di sana.
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Route::with(['train', 'originStation', 'destinationStation'])
            ->where('is_active', true);

        // Filter stasiun asal & tujuan
        if (!empty($filters['origin_station_id'])) {
            $query->where('origin_station_id', (int) $filters['origin_station_id']);
        }

        if (!empty($filters['destination_station_id'])) {
            $query->where('destination_station_id', (int) $filters['destination_station_id']);
        }

        // Filter tanggal keberangkatan
        if (!empty($filters['date'])) {
            $query->whereDate('departure_time', $filters['date']);
        }

        // Filter rentang JAM keberangkatan (contoh: pagi 06:00-12:00)
        if (!empty($filters['time_from'])) {
            $query->whereTime('departure_time', '>=', $filters['time_from']);
        }

        if (!empty($filters['time_to'])) {
            $query->whereTime('departure_time', '<=', $filters['time_to']);
        }

        // Filter tipe kereta (ekonomi / bisnis / eksekutif)
        if (!empty($filters['train_type'])) {
            $query->whereHas('train', function ($q) use ($filters) {
                $q->where('type', $filters['train_type']);
            });
        }

        // Filter harga maksimum
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        // Filter hanya jadwal yang masih ada kursi
        if (!empty($filters['only_available']) && filter_var($filters['only_available'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('train', function ($q) {
                // kursi tersedia = total_seats kereta > jumlah passengers yang sudah booking
                $q->whereRaw(
                    'total_seats > (
                        SELECT COALESCE(SUM(b.passengers), 0)
                        FROM bookings b
                        WHERE b.rute_id = routes.id
                          AND b.status IN (?, ?)
                    )',
                    [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED]
                );
            });
        }

        // Sorting
        $allowedSortColumns = ['departure_time', 'price', 'duration_minutes'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowedSortColumns)
            ? $filters['sort_by']
            : 'departure_time';
        $sortDir = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) ($filters['per_page'] ?? 15), 100);

        $results = $query->paginate($perPage);

        // Hitung kursi terpakai untuk semua route di halaman ini sekaligus (1 query)
        $bookedMap = Booking::whereIn('rute_id', $results->pluck('id'))
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->groupBy('rute_id')
            ->selectRaw('rute_id, COALESCE(SUM(passengers), 0) as total_booked')
            ->pluck('total_booked', 'rute_id');

        // Tambahkan available_seats & is_available ke setiap item
        $results->getCollection()->transform(function ($route) use ($bookedMap) {
            $booked    = (int) ($bookedMap[$route->id] ?? 0);
            $total     = $route->train->total_seats;
            $available = max(0, $total - $booked);

            $route->available_seats = $available;
            $route->is_available    = $available > 0;

            return $route;
        });

        return $results;
    }

    /**
     * Cek ketersediaan kursi untuk satu jadwal (route).
     * Kursi tersedia = total_seats kereta - jumlah passengers yang sudah booking (pending/confirmed).
     */
    public function checkSeats(int $routeId): array
    {
        $route = Route::with('train')->findOrFail($routeId);

        $bookedSeats = Booking::where('rute_id', $routeId)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->sum('passengers');

        $totalSeats     = $route->train->total_seats;
        $availableSeats = max(0, $totalSeats - (int) $bookedSeats);

        return [
            'route_id'        => $routeId,
            'train'           => $route->train->name,
            'departure_time'  => $route->departure_time,
            'arrival_time'    => $route->arrival_time,
            'total_seats'     => $totalSeats,
            'booked_seats'    => (int) $bookedSeats,
            'available_seats' => $availableSeats,
            'is_available'    => $availableSeats > 0,
        ];
    }
}
