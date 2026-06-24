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
        Schema::table('routes', function (Blueprint $table) {
            // Index status aktif
            $table->index('is_active', 'idx_routes_is_active');

            // Composite index untuk pencarian rute & filter aktif
            $table->index(['origin_station_id', 'destination_station_id', 'is_active'], 'idx_routes_search');

            // Composite index khusus untuk estimasi waktu tempuh (estimateTravelTime)
            // yang memfilter origin + destination + is_active dan melakukan ORDER BY duration_minutes
            $table->index(['origin_station_id', 'destination_station_id', 'is_active', 'duration_minutes'], 'idx_routes_estimation');

            // Composite index untuk filter kereta aktif
            $table->index(['train_id', 'is_active'], 'idx_routes_train_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropIndex('idx_routes_is_active');
            $table->dropIndex('idx_routes_search');
            $table->dropIndex('idx_routes_estimation');
            $table->dropIndex('idx_routes_train_active');
        });
    }
};
