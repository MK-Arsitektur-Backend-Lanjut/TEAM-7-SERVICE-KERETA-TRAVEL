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
        if (Schema::hasTable('bookings')) {
            return;
        }

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('booking_code')->unique();
            $table->string('origin');
            $table->string('destination');
            $table->unsignedBigInteger('schedule_id');
            $table->dateTime('arrival_at')->nullable();
            $table->unsignedSmallInteger('passengers')->default(1);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->string('status')->default('confirmed');
            $table->timestamps();

            $table->index(['user_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
