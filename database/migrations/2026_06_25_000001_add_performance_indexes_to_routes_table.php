<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan covering index pada tabel routes untuk optimasi
     * eager loading dari BookingRepository::listForUser().
     *
     * Query: SELECT id, origin_station_id, destination_station_id, departure_time
     *        FROM routes WHERE id IN (?, ?, ...)
     */
    public function up(): void
    {
        if (! Schema::hasTable('routes')) {
            return;
        }

        Schema::table('routes', function (Blueprint $table) {
            // Covering index untuk eager load relasi rute dari bookings
            $table->index(
                ['id', 'origin_station_id', 'destination_station_id', 'departure_time'],
                'routes_covering_eager_load_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('routes')) {
            return;
        }

        Schema::table('routes', function (Blueprint $table) {
            $table->dropIndex('routes_covering_eager_load_index');
        });
    }
};
