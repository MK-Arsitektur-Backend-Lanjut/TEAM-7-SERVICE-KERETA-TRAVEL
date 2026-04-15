<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        Booking::factory()
            ->count(20)
            ->for($user)
            ->create();
    }
}
