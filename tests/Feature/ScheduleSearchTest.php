<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Station;
use App\Models\Train;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ScheduleSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_can_search_schedules_by_origin_and_destination(): void
    {
        $origin = Station::factory()->create();
        $destination = Station::factory()->create();

        Schedule::factory()->create([
            'origin_station_id' => $origin->id,
            'destination_station_id' => $destination->id,
            'is_active' => true,
        ]);

        Schedule::factory()->create([
            'origin_station_id' => $destination->id,
            'destination_station_id' => $origin->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/schedules?' . http_build_query([
            'origin_station_id' => $origin->id,
            'destination_station_id' => $destination->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_filter_schedules_by_train_type(): void
    {
        $eksekutifTrain = Train::factory()->create(['type' => 'eksekutif']);
        $ekonomiTrain = Train::factory()->create(['type' => 'ekonomi']);

        Schedule::factory()->create(['train_id' => $eksekutifTrain->id, 'is_active' => true]);
        Schedule::factory()->create(['train_id' => $ekonomiTrain->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/schedules?train_type=eksekutif');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_filter_schedules_by_only_available(): void
    {

        Schedule::factory()->create(['available_seats' => 10, 'is_active' => true]);
        Schedule::factory()->create(['available_seats' => 0, 'is_active' => true]);

        $response = $this->getJson('/api/v1/schedules?only_available=true');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_sort_schedules_by_price(): void
    {

        Schedule::factory()->create(['price' => 100000, 'is_active' => true]);
        Schedule::factory()->create(['price' => 50000, 'is_active' => true]);
        Schedule::factory()->create(['price' => 200000, 'is_active' => true]);

        $response = $this->getJson('/api/v1/schedules?sort_by=price&sort_dir=asc');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(50000, $data[0]['price']);
        $this->assertEquals(100000, $data[1]['price']);
        $this->assertEquals(200000, $data[2]['price']);
    }

    public function test_can_paginate_schedules(): void
    {

        Schedule::factory()->count(10)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/schedules?per_page=5');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
        $this->assertEquals(10, $response->json('total'));
    }

    public function test_can_check_schedule_seats(): void
    {
        $schedule = Schedule::factory()->create([
            'total_seats' => 100,
            'available_seats' => 75,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/schedules/{$schedule->id}/seats");

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'schedule_id' => $schedule->id,
                'total_seats' => 100,
                'available_seats' => 75,
                'booked_seats' => 25,
                'is_available' => true,
            ],
        ]);
    }

    public function test_schedule_not_found_returns_404(): void
    {

        $response = $this->getJson('/api/v1/schedules/99999/seats');

        $response->assertNotFound();
    }
}
