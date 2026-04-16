<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('train_id'); // Relasi ke tabel trains
            $table->unsignedBigInteger('route_id')->nullable(); // Opsional, tergantung desain DB kamu
            $table->unsignedBigInteger('origin_station_id'); // Stasiun Awal
            $table->unsignedBigInteger('destination_station_id'); // Stasiun Tujuan
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
