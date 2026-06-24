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

        $cacheKey = 'routes_json:v4:' . md5(serialize($filters));

        // Ambil raw JSON langsung dari Redis (sangat cepat, bypass Eloquent dan json_encode)
        $cachedJson = \Illuminate\Support\Facades\Redis::get($cacheKey);
        if ($cachedJson !== null) {
            return new \Illuminate\Http\JsonResponse($cachedJson, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age=60',
            ], 0, true);
        }

        $routes = $this->routeRepository->getAllRoutes($filters);

        $jsonResponse = json_encode(['data' => $routes]);

        // Simpan raw JSON string langsung ke Redis dengan TTL 5 menit (300 detik)
        \Illuminate\Support\Facades\Redis::setex($cacheKey, 300, $jsonResponse);

        return new \Illuminate\Http\JsonResponse($jsonResponse, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=60',
        ], 0, true);
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
            'origin_id' => ['required', 'integer'],
            'destination_id' => ['required', 'integer', 'different:origin_id'],
        ]);

        $originId = (int) $request->query('origin_id');
        $destinationId = (int) $request->query('destination_id');

        // Gunakan cache untuk mengecek apakah stasiun ada agar tidak hit DB terus-menerus
        $originExists = \Illuminate\Support\Facades\Cache::remember("station_exists:{$originId}", 3600, function () use ($originId) {
            return \App\Models\Station::where('id', $originId)->exists();
        });
        $destExists = \Illuminate\Support\Facades\Cache::remember("station_exists:{$destinationId}", 3600, function () use ($destinationId) {
            return \App\Models\Station::where('id', $destinationId)->exists();
        });

        if (!$originExists || !$destExists) {
            $errors = [];
            if (!$originExists) {
                $errors['origin_id'] = ['The selected origin id is invalid.'];
            }
            if (!$destExists) {
                $errors['destination_id'] = ['The selected destination id is invalid.'];
            }
            return response()->json([
                'message' => 'The selected station is invalid.',
                'errors' => $errors
            ], 422);
        }

        $route = $this->routeRepository->estimateTravelTime($originId, $destinationId);

        if ($route === null) {
            return response()->json([
                'message' => 'Tidak ada rute aktif yang menghubungkan kedua stasiun tersebut.',
                'data' => null,
            ], 404);
        }

        $durationMinutes = $route->duration_minutes;
        $hours = intdiv($durationMinutes, 60);
        $minutes = $durationMinutes % 60;

        return response()->json([
            'data' => [
                'origin' => [
                    'code' => $route->originStation->code,
                    'name' => $route->originStation->name,
                ],
                'destination' => [
                    'code' => $route->destinationStation->code,
                    'name' => $route->destinationStation->name,
                ],
                'distance_km' => (float) $route->distance_km,
                'duration_minutes' => $durationMinutes,
                'duration_formatted' => $hours > 0
                    ? "{$hours} jam {$minutes} menit"
                    : "{$minutes} menit",
            ],
        ])->header('Cache-Control', 'public, max-age=300');
    }
}
