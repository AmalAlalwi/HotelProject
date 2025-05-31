<?php

namespace App\Providers;

use App\Interfaces\User\InvoiceInterface;
use App\Interfaces\User\Rooms\RoomRepositoryInterface;
use App\Interfaces\User\Services\ServiceRepositoryInterface;
use App\Repository\Rooms\RoomRepository;
use App\Repository\Services\ServiceRepository;
use App\Repository\User\InvoiceRepository;
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
        $this->app->bind(InvoiceInterface::class, InvoiceRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
