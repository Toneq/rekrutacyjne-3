<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WorktimeService;

class WorktimeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(WorktimeService::class, function ($app) {
            return new WorktimeService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
