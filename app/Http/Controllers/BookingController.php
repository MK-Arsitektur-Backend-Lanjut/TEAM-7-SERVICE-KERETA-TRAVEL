<?php

namespace App\Http\Controllers;

use App\Interfaces\BookingRepositoryInterface;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingRepositoryInterface $bookings)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15;
        }

        return response()->json(
            $this->bookings->listForUser((int) $request->user()->id, $perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rute_id' => ['required', 'integer', 'exists:rute,id'],  // Validasi FK ke tabel rute
            'passengers' => ['required', 'integer', 'min:1', 'max:10'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'total_price' => ['sometimes', 'integer', 'min:0'],
        ]);

        $booking = $this->bookings->createForUser((int) $request->user()->id, $data);

        return response()->json([
            'booking' => $booking->load('rute'),  // Load relasi rute
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $booking = $this->bookings->findForUser((int) $request->user()->id, $id);

        if (! $booking) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'booking' => $booking->load('rute'),  // Load relasi rute
        ]);
    }
}
