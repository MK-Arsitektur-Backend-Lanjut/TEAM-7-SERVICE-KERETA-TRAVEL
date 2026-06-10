<?php

namespace App\Interfaces;

use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;

interface RouteRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Route>
     */
    public function getAllRoutes(array $filters = []): Collection;

    public function getRouteById(int $id): Route;

    /**
     * @return Collection<int, Route>
     */
    public function getRoutesByStation(int $stationId): Collection;

    /** @param array<string, mixed> $data */
    public function createRoute(array $data): Route;

    /** @param array<string, mixed> $data */
    public function updateRoute(int $id, array $data): Route;

    public function deleteRoute(int $id): bool;

    /**
     * Estimasi waktu tempuh antara dua stasiun (dalam menit).
     * Mengembalikan null jika tidak ada rute yang menghubungkan kedua stasiun.
     */
    public function estimateTravelTime(int $originId, int $destinationId): ?int;
}
