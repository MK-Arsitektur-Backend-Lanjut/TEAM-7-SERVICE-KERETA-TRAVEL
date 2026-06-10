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
        if (! Schema::hasTable('bookings')) {
            return;
        }

        $hasDestination = Schema::hasColumn('bookings', 'destination');

        Schema::table('bookings', function (Blueprint $table) use ($hasDestination) {
            if (! Schema::hasColumn('bookings', 'schedule_id')) {
                $column = $table->unsignedBigInteger('schedule_id')->nullable();

                if ($hasDestination) {
                    $column->after('destination');
                }
            }

            if (Schema::hasColumn('bookings', 'departure_at')) {
                $table->dropColumn('departure_at');
            }
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
            if (! Schema::hasColumn('bookings', 'departure_at')) {
                $table->dateTime('departure_at')->nullable();
            }

            if (Schema::hasColumn('bookings', 'schedule_id')) {
                $table->dropColumn('schedule_id');
            }
        });
    }
};
