<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan index pada kolom yang sering digunakan untuk filter
     * agar performa query meningkat, terutama saat data banyak.
     */
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            // Index tunggal untuk filter individual
            $table->index('city', 'idx_stations_city');
            $table->index('province', 'idx_stations_province');
            $table->index('is_active', 'idx_stations_is_active');

            // Composite index untuk kombinasi filter yang paling umum:
            // GET /stations?is_active=true&city=...
            $table->index(['is_active', 'city'], 'idx_stations_is_active_city');
            // GET /stations?is_active=true&province=...
            $table->index(['is_active', 'province'], 'idx_stations_is_active_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropIndex('idx_stations_city');
            $table->dropIndex('idx_stations_province');
            $table->dropIndex('idx_stations_is_active');
            $table->dropIndex('idx_stations_is_active_city');
            $table->dropIndex('idx_stations_is_active_province');
        });
    }
};
