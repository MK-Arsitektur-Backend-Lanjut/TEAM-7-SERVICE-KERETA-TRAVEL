<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TrainController;
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

Route::prefix('v1')->group(function () {
    // Trains
    Route::get('trains', [TrainController::class, 'index']);
    Route::post('trains', [TrainController::class, 'store']);
    Route::get('trains/{train}', [TrainController::class, 'show']);
    Route::put('trains/{train}', [TrainController::class, 'update']);
    Route::delete('trains/{train}', [TrainController::class, 'destroy']);

    // Stations
    Route::get('stations', [StationController::class, 'index']);
    Route::post('stations', [StationController::class, 'store']);
    Route::get('stations/{station}', [StationController::class, 'show']);
    Route::put('stations/{station}', [StationController::class, 'update']);
    Route::delete('stations/{station}', [StationController::class, 'destroy']);
    Route::get('stations/{station}/routes', [StationController::class, 'routes'])->name('stations.routes');

    // Routes
    Route::get('routes/estimate-time', [RouteController::class, 'estimateTime'])->name('routes.estimate-time');
    Route::get('routes', [RouteController::class, 'index']);
    Route::post('routes', [RouteController::class, 'store']);
    Route::get('routes/{route}', [RouteController::class, 'show']);
    Route::put('routes/{route}', [RouteController::class, 'update']);
    Route::delete('routes/{route}', [RouteController::class, 'destroy']);
});
