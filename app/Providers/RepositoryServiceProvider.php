<?php

namespace App\Providers;

use App\Interfaces\Rooms\RoomRepositoryInterface;
use App\Interfaces\Services\ServiceRepositoryInterface;
use App\Repository\Rooms\RoomRepository;
use App\Repository\Services\ServiceRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(RoomRepositoryInterface::class, RoomRepository::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
