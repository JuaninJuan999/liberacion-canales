<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RegistroHallazgo;
use App\Observers\RegistroHallazgoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observer para cálculo automático de indicadores
        RegistroHallazgo::observe(RegistroHallazgoObserver::class);
    }
}
