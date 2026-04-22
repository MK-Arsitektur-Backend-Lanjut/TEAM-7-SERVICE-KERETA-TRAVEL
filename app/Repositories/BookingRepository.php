<?php

namespace App\Repositories;

use App\Interfaces\BookingRepositoryInterface;
use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BookingRepository implements BookingRepositoryInterface
{
    public function listForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->with('rute')  // Eager load rute data
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function createForUser(int $userId, array $data): Booking
    {
        $passengers = (int) ($data['passengers'] ?? 1);
        $price = (int) ($data['price'] ?? 0);

        // Data origin, destination, arrival_at akan diambil dari rute via accessor
        $payload = [
            'user_id' => $userId,
            'booking_code' => $data['booking_code'] ?? $this->generateBookingCode(),
            'rute_id' => (int) $data['rute_id'],
            'passengers' => $passengers,
            'price' => $price,
            'total_price' => (int) ($data['total_price'] ?? ($price * $passengers)),
            'status' => $data['status'] ?? 'confirmed',
            'payment_status' => $data['payment_status'] ?? 'pending',
        ];

        return Booking::create($payload);
    }

    public function findForUser(int $userId, int $bookingId): ?Booking
    {
        return Booking::query()
            ->where('id', $bookingId)
            ->where('user_id', $userId)
            ->with('rute')  // Eager load rute data
            ->first();
    }

    private function generateBookingCode(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = 'BK-' . Str::upper(Str::random(8));

            if (! Booking::query()->where('booking_code', $code)->exists()) {
                return $code;
            }
        }

        return 'BK-' . Str::uuid()->toString();
    }
}
