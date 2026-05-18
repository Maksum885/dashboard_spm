<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\PlcDataService;
use App\Services\AlarmService;
use App\Services\ActivityLogService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services sebagai singleton agar tidak re-instantiate per request
        $this->app->singleton(PlcDataService::class, function ($app) {
            return new PlcDataService();
        });

        $this->app->singleton(AlarmService::class, function ($app) {
            return new AlarmService();
        });

        $this->app->singleton(ActivityLogService::class, function ($app) {
            return new ActivityLogService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix untuk MySQL < 5.7.7 atau MariaDB yang strict soal index length
        Schema::defaultStringLength(191);
    }
}
