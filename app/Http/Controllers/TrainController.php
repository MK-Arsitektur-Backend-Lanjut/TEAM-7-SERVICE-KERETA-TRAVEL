<?php

namespace App\Http\Controllers;

use App\Interfaces\TrainRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainController extends Controller
{
    public function __construct(protected TrainRepositoryInterface $trainRepository) {}

    public function index(): JsonResponse
    {
        $trains = $this->trainRepository->getAllTrains();

        return response()->json([
            'data' => $trains,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:trains,code'],
            'type' => ['required', 'in:ekonomi,bisnis,eksekutif'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $train = $this->trainRepository->createTrain($validated);

        return response()->json([
            'message' => 'Kereta berhasil ditambahkan.',
            'data' => $train,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $train = $this->trainRepository->getTrainById($id);

        return response()->json([
            'data' => $train,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:20', "unique:trains,code,{$id}"],
            'type' => ['sometimes', 'in:ekonomi,bisnis,eksekutif'],
            'total_seats' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $train = $this->trainRepository->updateTrain($id, $validated);

        return response()->json([
            'message' => 'Kereta berhasil diperbarui.',
            'data' => $train,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->trainRepository->deleteTrain($id);

        return response()->json([
            'message' => 'Kereta berhasil dihapus.',
        ]);
    }
}
