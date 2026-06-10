<?php

namespace App\Repositories;

use App\Interfaces\StationRepositoryInterface;
use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;

class StationRepository implements StationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Station>
     */
    public function getAllStations(array $filters = []): Collection
    {
        $query = Station::query();

        if (isset($filters['city'])) {
            $query->where('city', 'like', '%'.$filters['city'].'%');
        }

        if (isset($filters['province'])) {
            $query->where('province', 'like', '%'.$filters['province'].'%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->get();
    }

    public function getStationById(int $id): Station
    {
        return Station::findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createStation(array $data): Station
    {
        return Station::create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateStation(int $id, array $data): Station
    {
        $station = Station::findOrFail($id);
        $station->update($data);

        return $station->fresh();
    }

    public function deleteStation(int $id): bool
    {
        $station = Station::findOrFail($id);

        return $station->delete();
    }
}
