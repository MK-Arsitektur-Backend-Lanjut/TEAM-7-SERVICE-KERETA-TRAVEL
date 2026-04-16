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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained('trains')->cascadeOnDelete();
            $table->foreignId('origin_station_id')->constrained('stations')->cascadeOnDelete();
            $table->foreignId('destination_station_id')->constrained('stations')->cascadeOnDelete();
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->unsignedInteger('duration_minutes');
            $table->decimal('distance_km', 8, 2);
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
