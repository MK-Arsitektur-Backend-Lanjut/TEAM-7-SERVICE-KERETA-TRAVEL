<?php

namespace App\Repositories;

use App\Interfaces\TrainRepositoryInterface;
use App\Models\Train;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TrainRepository implements TrainRepositoryInterface
{
    private const CACHE_PREFIX = 'trains_v2';
    private const CACHE_TTL = 300; // 5 menit

    /** @return Collection<int, Train> */
    public function getAllTrains(): Collection
    {
        $cacheKey = self::CACHE_PREFIX . ':list';

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Pilih kolom spesifik untuk mengurangi payload
            return Train::select('id', 'name', 'code', 'type', 'total_seats', 'is_active')->get()->toArray();
        });

        return Train::hydrate($data);
    }

    public function getTrainById(int $id): Train
    {
        $cacheKey = self::CACHE_PREFIX . ':detail:' . $id;

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Train::findOrFail($id)->toArray();
        });

        return Train::hydrate([$data])->first();
    }

    /** @param array<string, mixed> $data */
    public function createTrain(array $data): Train
    {
        $train = Train::create($data);
        $this->flushCache();
        return $train;
    }

    /** @param array<string, mixed> $data */
    public function updateTrain(int $id, array $data): Train
    {
        $train = Train::findOrFail($id);
        $train->update($data);

        $this->flushCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $train->fresh();
    }

    public function deleteTrain(int $id): bool
    {
        $train = Train::findOrFail($id);
        $result = $train->delete();

        $this->flushCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $result;
    }

    private function flushCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . ':list');
    }
}
