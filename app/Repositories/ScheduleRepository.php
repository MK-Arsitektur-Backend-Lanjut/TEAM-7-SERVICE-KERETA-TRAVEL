<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Interfaces\ScheduleRepositoryInterface;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function searchSchedules($origin, $destination, $date)
    {
        // Mencari jadwal berdasarkan asal, tujuan, dan tanggal
        return Schedule::with(['train', 'route'])
            ->where('origin_station_id', $origin)
            ->where('destination_station_id', $destination)
            ->whereDate('departure_time', $date)
            ->get();
    }

    public function filterByTime($schedules, $startTime, $endTime)
    {
        // Filter dari hasil pencarian berdasarkan jam
        return $schedules->filter(function ($item) use ($startTime, $endTime) {
            $time = date('H:i', strtotime($item->departure_time));
            return $time >= $startTime && $time <= $endTime;
        });
    }

    public function checkAvailability($scheduleId)
    {
        $schedule = Schedule::withCount('bookings')->find($scheduleId);
        if (!$schedule) return null;

        // Logika: Total Kursi di Kereta - Jumlah Booking yang sudah ada
        $remainingSeats = $schedule->train->total_seats - $schedule->bookings_count;
        
        return [
            'schedule_id' => $schedule->id,
            'total_seats' => $schedule->train->total_seats,
            'remaining_seats' => $remainingSeats,
            'is_available' => $remainingSeats > 0
        ];
    }
}
