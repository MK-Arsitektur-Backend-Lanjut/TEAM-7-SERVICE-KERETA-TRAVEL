<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('train_id')->constrained('trains')->cascadeOnDelete();
            $table->foreignId('origin_station_id')->constrained('stations')->cascadeOnDelete();
            $table->foreignId('destination_station_id')->constrained('stations')->cascadeOnDelete();
            $table->date('departure_date');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('total_seats');
            $table->unsignedInteger('available_seats');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['origin_station_id', 'destination_station_id', 'departure_date', 'is_active'], 'schedules_search_main');
            $table->index(['origin_station_id', 'destination_station_id', 'is_active', 'price'], 'schedules_search_price');
            $table->index(['origin_station_id', 'destination_station_id', 'is_active', 'departure_time'], 'schedules_search_time');
            $table->index(['origin_station_id', 'destination_station_id', 'is_active', 'duration_minutes'], 'schedules_search_duration');
            $table->index(['train_id', 'departure_time'], 'schedules_train_time');
            $table->index(['route_id', 'departure_time'], 'schedules_route_time');
            $table->index(['available_seats', 'is_active'], 'schedules_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
