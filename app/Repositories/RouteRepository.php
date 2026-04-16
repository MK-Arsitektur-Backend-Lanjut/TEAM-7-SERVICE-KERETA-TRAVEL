<?php

namespace App\Repositories;

use App\Interfaces\RouteRepositoryInterface;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;

class RouteRepository implements RouteRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Route>
     */
    public function getAllRoutes(array $filters = []): Collection
    {
        $query = Route::with(['train', 'originStation', 'destinationStation']);

        if (isset($filters['origin_station_id'])) {
            $query->where('origin_station_id', $filters['origin_station_id']);
        }

        if (isset($filters['destination_station_id'])) {
            $query->where('destination_station_id', $filters['destination_station_id']);
        }

        if (isset($filters['train_id'])) {
            $query->where('train_id', $filters['train_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->get();
    }

    public function getRouteById(int $id): Route
    {
        return Route::with(['train', 'originStation', 'destinationStation'])->findOrFail($id);
    }

    /** @return Collection<int, Route> */
    public function getRoutesByStation(int $stationId): Collection
    {
        return Route::with(['train', 'originStation', 'destinationStation'])
            ->where(function ($query) use ($stationId) {
                $query->where('origin_station_id', $stationId)
                    ->orWhere('destination_station_id', $stationId);
            })
            ->where('is_active', true)
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function createRoute(array $data): Route
    {
        $route = Route::create($data);

        return $route->load(['train', 'originStation', 'destinationStation']);
    }

    /** @param array<string, mixed> $data */
    public function updateRoute(int $id, array $data): Route
    {
        $route = Route::findOrFail($id);
        $route->update($data);

        return $route->fresh(['train', 'originStation', 'destinationStation']);
    }

    public function deleteRoute(int $id): bool
    {
        $route = Route::findOrFail($id);

        return $route->delete();
    }

    /**
     * Estimasi waktu tempuh antara dua stasiun (dalam menit).
     */
    public function estimateTravelTime(int $originId, int $destinationId): ?int
    {
        $route = Route::where('origin_station_id', $originId)
            ->where('destination_station_id', $destinationId)
            ->where('is_active', true)
            ->orderBy('duration_minutes')
            ->first();

        return $route?->duration_minutes;
    }
}
