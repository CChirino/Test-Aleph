<?php

namespace App\Providers;

use App\Contracts\AlephServiceInterface;
use App\Services\AlephService;
use Illuminate\Support\ServiceProvider;

class AlephServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AlephServiceInterface::class, AlephService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
