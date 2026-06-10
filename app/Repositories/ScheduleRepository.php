<?php

namespace App\Repositories;

use App\Interfaces\ScheduleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * Cari jadwal kereta berdasarkan filter.
     * Menggunakan tabel schedules untuk indexing optimal.
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = \App\Models\Schedule::with(['train', 'originStation', 'destinationStation', 'route'])
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
            $query->where('departure_date', $filters['date']);
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
            $query->where('available_seats', '>', 0);
        }

        // Sorting
        $allowedSortColumns = ['departure_time', 'price', 'duration_minutes'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowedSortColumns)
            ? $filters['sort_by']
            : 'departure_time';
        $sortDir = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) ($filters['per_page'] ?? 15), 100);

        return $query->paginate($perPage);
    }

    /**
     * Cek ketersediaan kursi untuk satu jadwal (schedule).
     * Kursi tersedia sudah disimpan di schedules.available_seats.
     */
    public function checkSeats(int $scheduleId): array
    {
        $schedule = \App\Models\Schedule::with('train')->findOrFail($scheduleId);

        $bookedSeats = $schedule->total_seats - $schedule->available_seats;

        return [
            'schedule_id'     => $scheduleId,
            'route_id'        => $schedule->route_id,
            'train'           => $schedule->train->name,
            'departure_time'  => $schedule->departure_time,
            'arrival_time'    => $schedule->arrival_time,
            'total_seats'     => $schedule->total_seats,
            'booked_seats'    => $bookedSeats,
            'available_seats' => $schedule->available_seats,
            'is_available'    => $schedule->available_seats > 0,
        ];
    }
}
