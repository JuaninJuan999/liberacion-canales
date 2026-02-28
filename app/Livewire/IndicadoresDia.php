<?php

namespace App\Livewire;

use App\Models\IndicadorDiario;
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

    /** Metas de porcentaje según especificación */
    const META_COBERTURA = 1.50;
    const META_HEMATOMA = 0.50;
    const META_CORTES_PIERNA = 1.00;
    const META_SOBREBARRIGA = 1.00;

    protected $listeners = ['hallazgo-registrado' => 'cargarIndicadores', 'fechaCambiada' => 'actualizarFecha'];

    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->format('Y-m-d');
        $this->mes = (int) Carbon::now()->month;
        $this->anio = (int) Carbon::now()->year;
        $this->cargarIndicadores();
        $this->cargarHistorial();
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

    public function cambiarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
        $this->dispatch('fechaCambiada', $this->fecha);
    }

    public function actualizarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    public function cambiarMesAnio()
    {
        $this->cargarHistorial();
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

    public function render()
    {
        return view('livewire.indicadores-dia');
    }
}
