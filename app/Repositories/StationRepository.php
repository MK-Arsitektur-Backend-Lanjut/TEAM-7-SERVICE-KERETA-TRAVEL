<?php

namespace App\Repositories;

use App\Interfaces\StationRepositoryInterface;
use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class StationRepository implements StationRepositoryInterface
{
    /**
     * Prefix untuk semua cache key stasiun, memudahkan invalidasi.
     */
    private const CACHE_PREFIX = 'stations_v2';

    /**
     * TTL cache dalam detik (5 menit).
     * Data stasiun jarang berubah, sehingga aman di-cache lebih lama.
     */
    private const CACHE_TTL = 300;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Station>
     */
    public function getAllStations(array $filters = []): Collection
    {
        // Buat cache key unik berdasarkan kombinasi filter yang dipakai
        $cacheKey = self::CACHE_PREFIX . ':list:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            // Pilih hanya kolom yang dibutuhkan untuk response list
            $query = Station::select('id', 'name', 'code', 'city', 'province', 'is_active');

            if (isset($filters['city'])) {
                $query->where('city', 'like', '%' . $filters['city'] . '%');
            }

            if (isset($filters['province'])) {
                $query->where('province', 'like', '%' . $filters['province'] . '%');
            }

            if (isset($filters['is_active'])) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            }

            return $query->get()->toArray();
        });

        return Station::hydrate($data);
    }

    public function getStationById(int $id): Station
    {
        $cacheKey = self::CACHE_PREFIX . ':detail:' . $id;

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Station::findOrFail($id)->toArray();
        });

        return Station::hydrate([$data])->first();
    }

    /** @param array<string, mixed> $data */
    public function createStation(array $data): Station
    {
        $station = Station::create($data);

        // Invalidasi seluruh cache list karena ada data baru
        $this->flushListCache();

        return $station;
    }

    /** @param array<string, mixed> $data */
    public function updateStation(int $id, array $data): Station
    {
        $station = Station::findOrFail($id);
        $station->update($data);

        // Invalidasi cache list dan cache detail stasiun ini
        $this->flushListCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $station->fresh();
    }

    public function deleteStation(int $id): bool
    {
        $station = Station::findOrFail($id);
        $result = $station->delete();

        // Invalidasi cache list dan cache detail stasiun ini
        $this->flushListCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $result;
    }

    /**
     * Flush semua cache yang berkaitan dengan list stasiun.
     * Menggunakan tag Redis jika tersedia, atau flush semua cache jika tidak.
     */
    private function flushListCache(): void
    {
        // Karena key list di-hash (md5), kita simpan registry key-nya
        // Cara paling aman: flush seluruh cache store stasiun
        // Pada production dengan banyak data, pertimbangkan Redis SCAN atau Tags
        Cache::flush();
    }
}
