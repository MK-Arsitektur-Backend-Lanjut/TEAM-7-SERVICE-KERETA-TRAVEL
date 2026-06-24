<?php

namespace App\Repositories;

use App\Interfaces\RouteRepositoryInterface;
use App\Models\Route;
use App\Models\Train;
use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RouteRepository implements RouteRepositoryInterface
{
    private const CACHE_PREFIX = 'routes_v2';
    private const CACHE_TTL = 300; // 5 menit

    public function getAllRoutes(array $filters = []): Collection
    {
        $cacheKey = self::CACHE_PREFIX . ':flat_list_v4:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = Route::query()
                ->join('trains', 'routes.train_id', '=', 'trains.id')
                ->join('stations as origin', 'routes.origin_station_id', '=', 'origin.id')
                ->join('stations as destination', 'routes.destination_station_id', '=', 'destination.id')
                ->select([
                    'routes.id',
                    'trains.code as train_code',
                    'trains.name as train_name',
                    'origin.code as origin_station_code',
                    'origin.name as origin_station_name',
                    'destination.code as destination_station_code',
                    'destination.name as destination_station_name',
                    'routes.distance_km',
                    'routes.is_active',
                ]);

            if (isset($filters['origin_station_id'])) {
                $query->where('routes.origin_station_id', $filters['origin_station_id']);
            }

            if (isset($filters['destination_station_id'])) {
                $query->where('routes.destination_station_id', $filters['destination_station_id']);
            }

            if (isset($filters['train_id'])) {
                $query->where('routes.train_id', $filters['train_id']);
            }

            if (isset($filters['is_active'])) {
                $query->where('routes.is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            }

            if (isset($filters['max_price'])) {
                $query->where('routes.price', '<=', $filters['max_price']);
            }

            return $query->get()->toArray();
        });

        return Route::hydrate($data);
    }

    public function getRouteById(int $id): Route
    {
        $cacheKey = self::CACHE_PREFIX . ':detail:' . $id;

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Route::with(['train', 'originStation', 'destinationStation'])
                ->findOrFail($id)
                ->toArray();
        });

        return $this->hydrateRoutesWithRelations([$data])->first();
    }

    /** @return Collection<int, Route> */
    public function getRoutesByStation(int $stationId): Collection
    {
        $cacheKey = self::CACHE_PREFIX . ':station:' . $stationId;

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($stationId) {
            return Route::with(['train', 'originStation', 'destinationStation'])
                ->where(function ($query) use ($stationId) {
                    $query->where('origin_station_id', $stationId)
                        ->orWhere('destination_station_id', $stationId);
                })
                ->where('is_active', true)
                ->get()
                ->toArray();
        });

        return $this->hydrateRoutesWithRelations($data);
    }

    /** @param array<string, mixed> $data */
    public function createRoute(array $data): Route
    {
        $route = Route::create($data);
        $this->flushCache();

        return $route->load(['train', 'originStation', 'destinationStation']);
    }

    /** @param array<string, mixed> $data */
    public function updateRoute(int $id, array $data): Route
    {
        $route = Route::findOrFail($id);
        $route->update($data);
        $this->flushCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $route->fresh(['train', 'originStation', 'destinationStation']);
    }

    public function deleteRoute(int $id): bool
    {
        $route = Route::findOrFail($id);
        $result = $route->delete();
        $this->flushCache();
        Cache::forget(self::CACHE_PREFIX . ':detail:' . $id);

        return $result;
    }

    public function estimateTravelTime(int $originId, int $destinationId): ?Route
    {
        $cacheKey = self::CACHE_PREFIX . ':estimate_v2:' . $originId . ':' . $destinationId;

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($originId, $destinationId) {
            $route = Route::with(['originStation', 'destinationStation'])
                ->where('origin_station_id', $originId)
                ->where('destination_station_id', $destinationId)
                ->where('is_active', true)
                ->orderBy('duration_minutes')
                ->first();

            return $route ? $route->toArray() : null;
        });

        if ($data === null) {
            return null;
        }

        return $this->hydrateRoutesWithRelations([$data])->first();
    }

    private function hydrateRoutesWithRelations(array $items): Collection
    {
        $routeInstance = new Route();
        $trainInstance = new Train();
        $stationInstance = new Station();

        $models = array_map(function ($item) use ($routeInstance, $trainInstance, $stationInstance) {
            $trainData = $item['train'] ?? null;
            $originStationData = $item['origin_station'] ?? null;
            $destinationStationData = $item['destination_station'] ?? null;

            unset($item['train'], $item['origin_station'], $item['destination_station']);

            $route = $routeInstance->newFromBuilder($item);

            if ($trainData) {
                $route->setRelation('train', $trainInstance->newFromBuilder($trainData));
            }
            if ($originStationData) {
                $route->setRelation('originStation', $stationInstance->newFromBuilder($originStationData));
            }
            if ($destinationStationData) {
                $route->setRelation('destinationStation', $stationInstance->newFromBuilder($destinationStationData));
            }

            return $route;
        }, $items);

        return $routeInstance->newCollection($models);
    }

    /**
     * Invalidate seluruh data list cache
     */
    private function flushCache(): void
    {
        // Flush seluruh cache standar
        Cache::flush();

        // Bersihkan raw JSON keys di Redis
        $prefix = \Illuminate\Support\Str::slug(config('app.name', 'laravel')) . '-database-';
        $keys = \Illuminate\Support\Facades\Redis::keys('routes_json:v2:*');
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $cleanKey = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $key);
                \Illuminate\Support\Facades\Redis::del($cleanKey);
            }
        }
    }
}
