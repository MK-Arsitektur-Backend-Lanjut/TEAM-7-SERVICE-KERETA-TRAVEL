<?php

namespace App\Interfaces;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function listForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function createForUser(int $userId, array $data): Booking;

    public function findForUser(int $userId, int $bookingId): ?Booking;
}
