<?php

namespace App\Http\Controllers;

use App\Interfaces\RouteRepositoryInterface;
use App\Interfaces\StationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function __construct(
        protected StationRepositoryInterface $stationRepository,
        protected RouteRepositoryInterface $routeRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['city', 'province', 'is_active']);
        $stations = $this->stationRepository->getAllStations($filters);

        return response()->json([
            'data' => $stations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:stations,code'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $station = $this->stationRepository->createStation($validated);

        return response()->json([
            'message' => 'Stasiun berhasil ditambahkan.',
            'data' => $station,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $station = $this->stationRepository->getStationById($id);

        return response()->json([
            'data' => $station,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:10', "unique:stations,code,{$id}"],
            'city' => ['sometimes', 'string', 'max:100'],
            'province' => ['sometimes', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $station = $this->stationRepository->updateStation($id, $validated);

        return response()->json([
            'message' => 'Stasiun berhasil diperbarui.',
            'data' => $station,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->stationRepository->deleteStation($id);

        return response()->json([
            'message' => 'Stasiun berhasil dihapus.',
        ]);
    }

    /**
     * Menampilkan semua rute yang berangkat dari atau tiba di stasiun ini.
     */
    public function routes(int $id): JsonResponse
    {
        // Pastikan stasiun ada
        $this->stationRepository->getStationById($id);

        $routes = $this->routeRepository->getRoutesByStation($id);

        return response()->json([
            'data' => $routes,
        ]);
    }
}
