<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Interfaces\TrainRepositoryInterface::class,
            \App\Repositories\TrainRepository::class
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
