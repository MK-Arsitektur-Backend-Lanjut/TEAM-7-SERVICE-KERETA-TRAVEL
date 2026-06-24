<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan index untuk optimasi performa query GET /api/bookings:
     * 1. Covering index — MySQL bisa menjawab query langsung dari index
     *    tanpa table lookup (Using index).
     * 2. Status index — untuk filter berdasarkan status (scope active, dll).
     * 3. Rute index — untuk eager loading relasi rute.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            // Covering index: meng-cover semua kolom di SELECT + WHERE + ORDER BY
            // Query: SELECT id, user_id, booking_code, rute_id, total_price, status, created_at
            //        FROM bookings WHERE user_id = ? ORDER BY id DESC
            $table->index(
                ['user_id', 'id', 'booking_code', 'rute_id', 'total_price', 'status', 'created_at'],
                'bookings_covering_list_index'
            );

            // Index pada status untuk query yang filter per status
            $table->index('status', 'bookings_status_index');

            // Index pada rute_id untuk eager loading relasi
            $table->index('rute_id', 'bookings_rute_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_covering_list_index');
            $table->dropIndex('bookings_status_index');
            $table->dropIndex('bookings_rute_id_index');
        });
    }
};
