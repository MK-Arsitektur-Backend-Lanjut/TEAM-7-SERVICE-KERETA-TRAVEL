<?php

namespace App\Interfaces;

use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;

interface StationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Station>
     */
    public function getAllStations(array $filters = []): Collection;

    public function getStationById(int $id): Station;

    /** @param array<string, mixed> $data */
    public function createStation(array $data): Station;

    /** @param array<string, mixed> $data */
    public function updateStation(int $id, array $data): Station;

    public function deleteStation(int $id): bool;
}
