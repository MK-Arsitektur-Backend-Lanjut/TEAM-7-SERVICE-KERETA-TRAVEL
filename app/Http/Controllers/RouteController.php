<?php

namespace App\Http\Controllers;

use App\Interfaces\RouteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function __construct(protected RouteRepositoryInterface $routeRepository) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'origin_station_id',
            'destination_station_id',
            'train_id',
            'is_active',
            'max_price',
        ]);

        $routes = $this->routeRepository->getAllRoutes($filters);

        return response()->json([
            'data' => $routes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'train_id' => ['required', 'integer', 'exists:trains,id'],
            'origin_station_id' => ['required', 'integer', 'exists:stations,id', 'different:destination_station_id'],
            'destination_station_id' => ['required', 'integer', 'exists:stations,id'],
            'departure_time' => ['required', 'date'], 
            'arrival_time' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_km' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $route = $this->routeRepository->createRoute($validated);

        return response()->json([
            'message' => 'Rute berhasil ditambahkan.',
            'data' => $route,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $route = $this->routeRepository->getRouteById($id);

        return response()->json([
            'data' => $route,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'train_id' => ['sometimes', 'integer', 'exists:trains,id'],
            'origin_station_id' => ['sometimes', 'integer', 'exists:stations,id'],
            'destination_station_id' => ['sometimes', 'integer', 'exists:stations,id'],
            'departure_time' => ['sometimes', 'date'],
            'arrival_time' => ['sometimes', 'date'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'distance_km' => ['sometimes', 'numeric', 'min:0'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $route = $this->routeRepository->updateRoute($id, $validated);

        return response()->json([
            'message' => 'Rute berhasil diperbarui.',
            'data' => $route,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->routeRepository->deleteRoute($id);

        return response()->json([
            'message' => 'Rute berhasil dihapus.',
        ]);
    }

    /**
     * Estimasi waktu tempuh antara dua stasiun.
     * Query params: origin_id, destination_id
     */
    public function estimateTime(Request $request): JsonResponse
    {
        $request->validate([
            'origin_id' => ['required', 'integer', 'exists:stations,id'],
            'destination_id' => ['required', 'integer', 'exists:stations,id', 'different:origin_id'],
        ]);

        $durationMinutes = $this->routeRepository->estimateTravelTime(
            (int) $request->query('origin_id'),
            (int) $request->query('destination_id'),
        );

        if ($durationMinutes === null) {
            return response()->json([
                'message' => 'Tidak ada rute aktif yang menghubungkan kedua stasiun tersebut.',
                'data' => null,
            ], 404);
        }

        $hours = intdiv($durationMinutes, 60);
        $minutes = $durationMinutes % 60;

        return response()->json([
            'data' => [
                'origin_station_id' => (int) $request->query('origin_id'),
                'destination_station_id' => (int) $request->query('destination_id'),
                'duration_minutes' => $durationMinutes,
                'duration_formatted' => $hours > 0
                    ? "{$hours} jam {$minutes} menit"
                    : "{$minutes} menit",
            ],
        ]);
    }
}
