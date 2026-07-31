<?php

namespace App\Console\Commands;

use App\Services\CalculadoraIndicadores;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalcularIndicadoresDiaCommand extends Command
{
    protected $signature = 'indicadores:recalcular-dia {fecha : Fecha operativa Y-m-d}';

    protected $description = 'Recalcula indicadores_diarios para una fecha operativa (útil tras correcciones de turno)';

    public function handle(CalculadoraIndicadores $calculadora): int
    {
        $fecha = Carbon::parse($this->argument('fecha'))->toDateString();
        $indicador = $calculadora->calcularIndicadoresDia($fecha);

        if ($indicador === null) {
            $this->warn("No se generó fila para {$fecha} (sin datos o error en log).");

            return self::FAILURE;
        }

        $this->info("Indicadores recalculados para {$fecha}: total_hallazgos={$indicador->total_hallazgos}");

        return self::SUCCESS;
    }
}
