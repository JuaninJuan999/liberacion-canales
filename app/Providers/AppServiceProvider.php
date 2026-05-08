<?php

namespace App\Providers;

use App\Models\AnimalProcesado;
use App\Models\HallazgoToleranciaZero;
use App\Models\RegistroHallazgo;
use App\Observers\AnimalProcesadoObserver;
use App\Observers\HallazgoToleranciaZeroObserver;
use App\Observers\RegistroHallazgoObserver;
use Illuminate\Support\ServiceProvider;

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
        HallazgoToleranciaZero::observe(HallazgoToleranciaZeroObserver::class);
        AnimalProcesado::observe(AnimalProcesadoObserver::class);
    }
}
