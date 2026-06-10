<?php

namespace App\Http\Controllers;

use App\Interfaces\ScheduleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $schedules
    ) {}

    /**
     * Cari jadwal kereta.
     *
     * GET /api/v1/schedules
     *
     * Query params:
     *   - origin_station_id      (int)    stasiun asal
     *   - destination_station_id (int)    stasiun tujuan
     *   - date                   (string) format Y-m-d, contoh: 2026-05-01
     *   - max_price              (float)  harga tiket maksimum
     *   - per_page               (int)    jumlah per halaman (default 15, maks 100)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'origin_station_id'      => ['sometimes', 'integer', 'exists:stations,id'],
            'destination_station_id' => ['sometimes', 'integer', 'exists:stations,id', 'different:origin_station_id'],
            'date'                   => ['sometimes', 'date_format:Y-m-d'],
            'time_from'              => ['sometimes', 'date_format:H:i'],
            'time_to'                => ['sometimes', 'date_format:H:i', 'after:time_from'],
            'train_type'             => ['sometimes', 'string', 'in:ekonomi,bisnis,eksekutif'],
            'max_price'              => ['sometimes', 'numeric', 'min:0'],
            'only_available'         => ['sometimes', 'nullable', 'in:true,false,1,0'],
            'sort_by'                => ['sometimes', 'string', 'in:departure_time,price,duration_minutes'],
            'sort_dir'               => ['sometimes', 'string', 'in:asc,desc'],
            'per_page'               => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $results = $this->schedules->search($request->only([
            'origin_station_id',
            'destination_station_id',
            'date',
            'time_from',
            'time_to',
            'train_type',
            'max_price',
            'only_available',
            'sort_by',
            'sort_dir',
            'per_page',
        ]));

        return response()->json($results);
    }

    /**
     * Cek ketersediaan kursi untuk jadwal tertentu.
     *
     * GET /api/v1/schedules/{id}/seats
     */
    public function checkSeats(int $id): JsonResponse
    {
        $data = $this->schedules->checkSeats($id);

        return response()->json(['data' => $data]);
    }
}
