<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('jwt.auth')->get('me', [AuthController::class, 'me']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show'])->whereNumber('id');
});
