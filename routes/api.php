<?php

use App\Http\Controllers\RouteController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TrainController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Trains
    Route::apiResource('trains', TrainController::class);

    // Stations
    Route::apiResource('stations', StationController::class);
    Route::get('stations/{station}/routes', [StationController::class, 'routes'])->name('stations.routes');

    // Routes
    Route::get('routes/estimate-time', [RouteController::class, 'estimateTime'])->name('routes.estimate-time');
    Route::apiResource('routes', RouteController::class);
});
