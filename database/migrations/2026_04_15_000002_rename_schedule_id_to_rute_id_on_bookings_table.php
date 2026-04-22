<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Check if schedule_id exists before renaming
        if (Schema::hasColumn('bookings', 'schedule_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Add new rute_id column first (copy from schedule_id)
                if (! Schema::hasColumn('bookings', 'rute_id')) {
                    $table->unsignedBigInteger('rute_id')->nullable()->after('booking_code');
                }
            });

            // Copy data from schedule_id to rute_id
            DB::statement('UPDATE bookings SET rute_id = schedule_id');

            // Drop old columns
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('schedule_id');

                if (Schema::hasColumn('bookings', 'origin')) {
                    $table->dropColumn('origin');
                }

                if (Schema::hasColumn('bookings', 'destination')) {
                    $table->dropColumn('destination');
                }

                if (Schema::hasColumn('bookings', 'arrival_at')) {
                    $table->dropColumn('arrival_at');
                }
            });
        }
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
            // Add back the columns
            if (! Schema::hasColumn('bookings', 'schedule_id')) {
                $table->unsignedBigInteger('schedule_id')->nullable()->after('booking_code');
            }

            if (! Schema::hasColumn('bookings', 'origin')) {
                $table->string('origin')->nullable();
            }

            if (! Schema::hasColumn('bookings', 'destination')) {
                $table->string('destination')->nullable();
            }

            if (! Schema::hasColumn('bookings', 'arrival_at')) {
                $table->dateTime('arrival_at')->nullable();
            }

            if (Schema::hasColumn('bookings', 'rute_id')) {
                // Copy data back
                DB::statement('UPDATE bookings SET schedule_id = rute_id');
                $table->dropColumn('rute_id');
            }
        });
    }
};
