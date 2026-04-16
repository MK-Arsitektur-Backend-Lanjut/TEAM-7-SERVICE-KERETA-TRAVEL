<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ScheduleRepositoryInterface;

class ScheduleController extends Controller
{
    private $scheduleRepository;

    public function __construct(ScheduleRepositoryInterface $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    public function index(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'date' => 'required|date',
        ]);

        // 2. Panggil Repository untuk Search
        $results = $this->scheduleRepository->searchSchedules(
            $request->origin, 
            $request->destination, 
            $request->date
        );

        // 3. Optional: Filter jika ada jam keberangkatan
        if ($request->has('start_time') && $request->has('end_time')) {
            $results = $this->scheduleRepository->filterByTime(
                $results, 
                $request->start_time, 
                $request->end_time
            );
        }

        return response()->json([
            'status' => 'success',
            'count' => count($results),
            'data' => $results
        ]);
    }

    public function checkSeats($id)
    {
        $availability = $this->scheduleRepository->checkAvailability($id);

        if (!$availability) {
            return response()->json(['message' => 'Jadwal tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $availability
        ]);
    }
}
