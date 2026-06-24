<?php

namespace App\Providers;

use App\Services\GeminiModelService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeminiModelService::class, fn () => GeminiModelService::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL/MariaDB antiguos limitan el largo de clave; evita el error 1071
        // ("Specified key was too long") en índices de columnas string con utf8mb4.
        Schema::defaultStringLength(191);
    }
}
