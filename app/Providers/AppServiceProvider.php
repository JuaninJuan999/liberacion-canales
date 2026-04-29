<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RegistroHallazgo;
use App\Observers\RegistroHallazgoObserver;
use App\Models\AnimalProcesado;
use App\Observers\AnimalProcesadoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once base_path('app/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observers para cálculo automático de indicadores
        RegistroHallazgo::observe(RegistroHallazgoObserver::class);
        AnimalProcesado::observe(AnimalProcesadoObserver::class);
    }
}
