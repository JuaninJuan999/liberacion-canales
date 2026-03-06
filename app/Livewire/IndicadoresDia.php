<?php

namespace App\Livewire;

use App\Models\IndicadorDiario;
use App\Models\HallazgoToleranciaZero;
use Carbon\Carbon;
use Livewire\Component;

class IndicadoresDia extends Component
{
    public $fecha;
    public $mes;
    public $anio;
    public $indicadores; // un solo día (para detalle cuando se use)
    public $historial = []; // lista de días para la tabla Historial de Liberación
    public $hallazgosPorTipo = [];
    public $promedioMes = 0;
    
    // Tolerancia Cero
    public $hallazgosToleranciaZero = [];
    public $resumenToleranciaZero = [];
    public $materiaFecalTC = 0;
    public $contenidoRuminalTC = 0;
    public $lecheVisibleTC = 0;
    public $totalHallazgosTC = 0;

    /** Metas de porcentaje según especificación */
    const META_COBERTURA = 1.50;
    const META_HEMATOMA = 0.50;
    const META_CORTES_PIERNA = 1.00;
    const META_SOBREBARRIGA = 1.00;

    protected $listeners = ['hallazgo-registrado' => 'actualizarDespuesDeRegistro', 'fechaCambiada' => 'actualizarFecha', 'hallazgo-tolerancia-cero-registrado' => 'actualizarDespuesDeRegistro'];

    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->format('Y-m-d');
        $this->mes = (int) Carbon::now()->month;
        $this->anio = (int) Carbon::now()->year;
        $this->cargarIndicadores();
        $this->cargarHistorial();
        $this->cargarHallazgosToleranciaZero();
    }

    public function actualizarDespuesDeRegistro()
    {
        $this->cargarIndicadores();
        $this->cargarHistorial();
        $this->cargarHallazgosToleranciaZero();
    }

    public function cargarIndicadores()
    {
        $this->indicadores = IndicadorDiario::where('fecha_operacion', $this->fecha)->first();
        $this->prepararHallazgosPorTipo();
    }

    /**
     * Carga el historial del mes actual para la tabla "Historial de Liberación".
     */
    public function cargarHistorial()
    {
        $mesStr = str_pad((string) $this->mes, 2, '0', STR_PAD_LEFT);
        $registros = IndicadorDiario::where('mes', $mesStr)
            ->where('año', $this->anio)
            ->orderBy('fecha_operacion')
            ->get();

        $this->historial = [];
        foreach ($registros as $ind) {
            $totalMedias = $ind->medias_canales_total ?: 1;
            $this->historial[] = [
                'fecha_operacion' => $ind->fecha_operacion,
                'medias_canal_1' => $ind->medias_canal_1 ?? 0,
                'medias_canal_2' => $ind->medias_canal_2 ?? 0,
                'total_hallazgos' => $ind->total_hallazgos ?? 0,
                'cobertura_pct' => $totalMedias > 0 ? round(($ind->cobertura_grasa / $totalMedias) * 100, 2) : 0,
                'hematoma_pct' => $totalMedias > 0 ? round(($ind->hematomas / $totalMedias) * 100, 2) : 0,
                'cortes_pct' => $totalMedias > 0 ? round(($ind->cortes_piernas / $totalMedias) * 100, 2) : 0,
                'sobrebarriga_pct' => $totalMedias > 0 ? round(($ind->sobrebarriga_rota / $totalMedias) * 100, 2) : 0,
                'participacion_total' => (float) ($ind->participacion_total ?? 0),
                'mes' => $ind->mes,
                'año' => $ind->año,
            ];
        }

        $this->promedioMes = $registros->isEmpty() ? 0 : round($registros->avg('participacion_total'), 2);
    }

    public function actualizarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    public function cambiarMesAnio()
    {
        $this->cargarHistorial();
        $this->cargarHallazgosToleranciaZero();
    }

    /**
     * Indica si un valor cumple la meta (<= meta).
     */
    public static function cumpleMeta(float $valor, float $meta): bool
    {
        return $valor <= $meta;
    }

    /** Metas para usar en la vista */
    public function getMetasProperty(): array
    {
        return [
            'cobertura' => self::META_COBERTURA,
            'hematoma' => self::META_HEMATOMA,
            'cortes_pierna' => self::META_CORTES_PIERNA,
            'sobrebarriga' => self::META_SOBREBARRIGA,
        ];
    }

    protected function prepararHallazgosPorTipo()
    {
        $this->hallazgosPorTipo = [];

        if ($this->indicadores && $this->indicadores->desglose_hallazgos) {
            $desglose = json_decode($this->indicadores->desglose_hallazgos, true);
            if (is_array($desglose)) {
                foreach ($desglose as $nombre => $total) {
                    $this->hallazgosPorTipo[] = [
                        'nombre' => $nombre,
                        'total' => $total,
                    ];
                }
            }
        }
    }

    /**
     * Cargar hallazgos de tolerancia cero para el mes actual
     */
    public function cargarHallazgosToleranciaZero()
    {
        $inicio = Carbon::create($this->anio, $this->mes, 1)->startOfMonth();
        $fin = Carbon::create($this->anio, $this->mes, 1)->endOfMonth();

        $hallazgos = HallazgoToleranciaZero::porRangoFechasConTurno($inicio->toDateString(), $fin->toDateString())
            ->with(['producto:id,nombre', 'tipoHallazgo:id,nombre'])
            ->get();

        $resumenPorDia = [];

        foreach ($hallazgos as $hallazgo) {
            $fechaDia = $hallazgo->getFechaOperacionEfectiva()->toDateString();

            if (!isset($resumenPorDia[$fechaDia])) {
                $resumenPorDia[$fechaDia] = [
                    'fecha_operacion' => $fechaDia,
                    'cuarto_anterior' => 0,
                    'cuarto_posterior' => 0,
                    'total_hallazgos' => 0,
                    'contenido_ruminal' => 0,
                    'materia_fecal' => 0,
                    'leche_visible' => 0,
                    'participacion' => 0,
                ];
            }

            $nombreProducto = strtoupper(trim((string) ($hallazgo->producto->nombre ?? '')));
            $nombreTipo = strtoupper(trim((string) ($hallazgo->tipoHallazgo->nombre ?? '')));

            if ($nombreProducto === 'CUARTO ANTERIOR') {
                $resumenPorDia[$fechaDia]['cuarto_anterior']++;
            }

            if ($nombreProducto === 'CUARTO POSTERIOR') {
                $resumenPorDia[$fechaDia]['cuarto_posterior']++;
            }

            if ($nombreTipo === 'CONTENIDO RUMINAL') {
                $resumenPorDia[$fechaDia]['contenido_ruminal']++;
            }

            if ($nombreTipo === 'MATERIA FECAL') {
                $resumenPorDia[$fechaDia]['materia_fecal']++;
            }

            if ($nombreTipo === 'LECHE VISIBLE') {
                $resumenPorDia[$fechaDia]['leche_visible']++;
            }

            $resumenPorDia[$fechaDia]['total_hallazgos']++;
        }

        foreach ($resumenPorDia as $fechaDia => &$fila) {
            $indicadorDia = IndicadorDiario::whereDate('fecha_operacion', $fechaDia)->first();
            $mediasCanales = (int) ($indicadorDia->medias_canales_total ?? 0);
            $fila['participacion'] = $mediasCanales > 0
                ? round(($fila['total_hallazgos'] / $mediasCanales) * 100, 2)
                : 0;
        }
        unset($fila);

        krsort($resumenPorDia);
        $this->resumenToleranciaZero = array_values($resumenPorDia);

        $this->materiaFecalTC = array_sum(array_column($this->resumenToleranciaZero, 'materia_fecal'));
        $this->contenidoRuminalTC = array_sum(array_column($this->resumenToleranciaZero, 'contenido_ruminal'));
        $this->lecheVisibleTC = array_sum(array_column($this->resumenToleranciaZero, 'leche_visible'));
        $this->totalHallazgosTC = array_sum(array_column($this->resumenToleranciaZero, 'total_hallazgos'));
    }

    public function render()
    {
        return view('livewire.indicadores-dia')
        ->layout('layouts.app');
    }
}
