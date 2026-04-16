<?php

namespace App\Interfaces;

interface ScheduleRepositoryInterface
{
    public function searchSchedules($origin, $destination, $date);
    public function filterByTime($schedules, $startTime, $endTime);
    public function checkAvailability($scheduleId);
}
