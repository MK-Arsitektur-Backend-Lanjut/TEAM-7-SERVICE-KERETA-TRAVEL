<?php

namespace App\Providers;

use App\Interfaces\RouteRepositoryInterface;
use App\Interfaces\StationRepositoryInterface;
use App\Interfaces\TrainRepositoryInterface;
use App\Repositories\RouteRepository;
use App\Repositories\StationRepository;
use App\Repositories\TrainRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TrainRepositoryInterface::class,
            TrainRepository::class
        );

        $this->app->bind(
            StationRepositoryInterface::class,
            StationRepository::class
        );

        $this->app->bind(
            RouteRepositoryInterface::class,
            RouteRepository::class
        );

        $this->app->bind(
            \App\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Interfaces\BookingRepositoryInterface::class,
            \App\Repositories\BookingRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
