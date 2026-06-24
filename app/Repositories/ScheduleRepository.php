<?php

namespace App\Repositories;

use App\Interfaces\ScheduleRepositoryInterface;
use App\Models\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    private const SEARCH_CACHE_TTL = 30;

    /**
     * Cari jadwal kereta berdasarkan filter.
     * Menggunakan tabel schedules untuk indexing optimal.
     */
    public function search(array $filters): array
    {
        $cacheKey = $this->searchCacheKey($filters);

        return Cache::store('redis')->remember($cacheKey, self::SEARCH_CACHE_TTL, function () use ($filters): array {
            $query = DB::table('schedules')
                ->join('trains', 'schedules.train_id', '=', 'trains.id')
                ->join('stations as origin_stations', 'schedules.origin_station_id', '=', 'origin_stations.id')
                ->join('stations as destination_stations', 'schedules.destination_station_id', '=', 'destination_stations.id')
                ->select([
                    'schedules.id',
                    'schedules.route_id',
                    'schedules.train_id',
                    'trains.name as train_name',
                    'trains.code as train_code',
                    'trains.type as train_type',
                    'schedules.origin_station_id',
                    'origin_stations.name as origin_station_name',
                    'origin_stations.code as origin_station_code',
                    'origin_stations.city as origin_station_city',
                    'schedules.destination_station_id',
                    'destination_stations.name as destination_station_name',
                    'destination_stations.code as destination_station_code',
                    'destination_stations.city as destination_station_city',
                    'schedules.departure_date',
                    'schedules.departure_time',
                    'schedules.arrival_time',
                    'schedules.duration_minutes',
                    'schedules.price',
                    'schedules.total_seats',
                    'schedules.available_seats',
                    'schedules.is_active',
                    DB::raw('(schedules.available_seats > 0) as is_available'),
                ])
                ->where('schedules.is_active', true);

            // Filter stasiun asal & tujuan
            if (! empty($filters['origin_station_id'])) {
                $query->where('schedules.origin_station_id', (int) $filters['origin_station_id']);
            }

            if (! empty($filters['destination_station_id'])) {
                $query->where('schedules.destination_station_id', (int) $filters['destination_station_id']);
            }

            // Filter tanggal keberangkatan
            if (! empty($filters['date'])) {
                $query->where('schedules.departure_date', $filters['date']);
            }

            // Filter rentang JAM keberangkatan (contoh: pagi 06:00-12:00)
            if (! empty($filters['time_from'])) {
                $query->whereTime('schedules.departure_time', '>=', $filters['time_from']);
            }

            if (! empty($filters['time_to'])) {
                $query->whereTime('schedules.departure_time', '<=', $filters['time_to']);
            }

            // Filter tipe kereta (ekonomi / bisnis / eksekutif)
            if (! empty($filters['train_type'])) {
                $query->where('trains.type', $filters['train_type']);
            }

            // Filter harga maksimum
            if (! empty($filters['max_price'])) {
                $query->where('schedules.price', '<=', (float) $filters['max_price']);
            }

            // Filter hanya jadwal yang masih ada kursi
            if (! empty($filters['only_available']) && filter_var($filters['only_available'], FILTER_VALIDATE_BOOLEAN)) {
                $query->where('schedules.available_seats', '>', 0);
            }

            // Sorting
            $sortColumns = [
                'departure_time' => 'schedules.departure_time',
                'price' => 'schedules.price',
                'duration_minutes' => 'schedules.duration_minutes',
            ];
            $sortBy = $sortColumns[$filters['sort_by'] ?? ''] ?? 'schedules.departure_time';
            $sortDir = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortDir);

            $perPage = min((int) ($filters['per_page'] ?? 15), 100);
            $withTotal = ! empty($filters['with_total']) && filter_var($filters['with_total'], FILTER_VALIDATE_BOOLEAN);

            $paginator = $withTotal
                ? $query->paginate($perPage)
                : $query->simplePaginate($perPage);

            $paginator->getCollection()->transform(function (object $schedule): object {
                $schedule->is_available = (bool) $schedule->is_available;

                return $schedule;
            });

            return $paginator->toArray();
        });
    }

    /**
     * Cek ketersediaan kursi untuk satu jadwal (schedule).
     * Kursi tersedia sudah disimpan di schedules.available_seats.
     */
    public function checkSeats(int $scheduleId): array
    {
        $schedule = Schedule::with('train')->findOrFail($scheduleId);

        $bookedSeats = $schedule->total_seats - $schedule->available_seats;

        return [
            'schedule_id' => $scheduleId,
            'route_id' => $schedule->route_id,
            'train' => $schedule->train->name,
            'departure_time' => $schedule->departure_time,
            'arrival_time' => $schedule->arrival_time,
            'total_seats' => $schedule->total_seats,
            'booked_seats' => $bookedSeats,
            'available_seats' => $schedule->available_seats,
            'is_available' => $schedule->available_seats > 0,
        ];
    }

    private function searchCacheKey(array $filters): string
    {
        $normalizedFilters = $filters;
        ksort($normalizedFilters);

        return 'schedules:search:v1:'.md5((string) json_encode($normalizedFilters));
    }
}
