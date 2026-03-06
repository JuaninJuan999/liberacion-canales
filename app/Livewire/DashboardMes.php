<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IndicadorDiario;
use App\Models\HallazgoToleranciaZero;
use App\Services\CalculadoraIndicadores;
use Carbon\Carbon;

class DashboardMes extends Component
{
    public $mes;
    public $anio;
    public $indicadoresMes;
    public $indicadoresDiarios = [];
    public $graficoDatos = [];
    public $toleranciaZeroDatos = [];

    protected $listeners = ['hallazgo-registrado' => 'actualizarDespuesDeRegistro'];
    
    public function mount($mes = null, $anio = null)
    {
        $this->mes = $mes ?: Carbon::now()->month;
        $this->anio = $anio ?: Carbon::now()->year;
        $this->cargarDatos();
    }

    public function actualizarDespuesDeRegistro()
    {
        $this->cargarDatos();
    }
    
    public function cargarDatos()
    {
        $calculadora = new CalculadoraIndicadores();
        
        // Calcular indicadores del mes
        $this->indicadoresMes = $calculadora->calcularIndicadoresMes($this->mes, $this->anio);
        
        // Obtener indicadores diarios
        $fechaInicio = Carbon::create($this->anio, $this->mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($this->anio, $this->mes, 1)->endOfMonth();
        
        $this->indicadoresDiarios = IndicadorDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        
        // Obtener datos de Tolerancia Cero del mes
        $this->cargarToleranciaZero($fechaInicio, $fechaFin);
        
        // Preparar datos para gráficos
        $this->prepararDatosGrafico();
    }

    private function cargarToleranciaZero($fechaInicio, $fechaFin)
    {
        // Contar hallazgos por tipo para Tolerancia Cero
        $hallazgos = HallazgoToleranciaZero::whereBetween('fecha_operacion', [$fechaInicio, $fechaFin])
            ->with(['tipoHallazgo', 'producto'])
            ->get();

        // Agrupar por tipo
        $materiaFecal = $hallazgos->filter(function($h) { return $h->tipoHallazgo?->nombre === 'MATERIA FECAL'; })->count();
        $contenidoRuminal = $hallazgos->filter(function($h) { return $h->tipoHallazgo?->nombre === 'CONTENIDO RUMINAL'; })->count();
        $lecheVisible = $hallazgos->filter(function($h) { return $h->tipoHallazgo?->nombre === 'LECHE VISIBLE'; })->count();

        // Agrupar por producto dentro de cada tipo
        $tzPorProducto = [];
        foreach (['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'] as $tipo) {
            $tzPorProducto[$tipo] = [
                'CUARTO ANTERIOR' => 0,
                'CUARTO POSTERIOR' => 0
            ];
        }

        foreach ($hallazgos as $h) {
            $tipo = $h->tipoHallazgo?->nombre;
            $producto = $h->producto?->nombre;
            if ($tipo && $producto && isset($tzPorProducto[$tipo])) {
                if ($producto === 'CUARTO ANTERIOR') {
                    $tzPorProducto[$tipo]['CUARTO ANTERIOR']++;
                } elseif ($producto === 'CUARTO POSTERIOR') {
                    $tzPorProducto[$tipo]['CUARTO POSTERIOR']++;
                }
            }
        }

        $this->toleranciaZeroDatos = [
            'materiaFecal' => $materiaFecal,
            'contenidoRuminal' => $contenidoRuminal,
            'lecheVisible' => $lecheVisible,
            'total' => $materiaFecal + $contenidoRuminal + $lecheVisible,
            'porProducto' => $tzPorProducto,
            'labels' => ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'],
            'values' => [$materiaFecal, $contenidoRuminal, $lecheVisible],
            'colors' => ['#FCD34D', '#F97316', '#3B82F6'],
        ];
    }
    
    protected function prepararDatosGrafico()
    {
        $this->graficoDatos = [
            'labels' => $this->indicadoresDiarios->pluck('fecha')->map(function($fecha) {
                return Carbon::parse($fecha)->format('d/m');
            })->toArray(),
            'datasets' => [
                [
                    'label' => '% Liberación',
                    'data' => $this->indicadoresDiarios->pluck('porcentaje_liberacion')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Hallazgos Críticos',
                    'data' => $this->indicadoresDiarios->pluck('hallazgos_criticos')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Indicador de sobrebarriga rotas',
                    'data' => $this->indicadoresDiarios->pluck('porcentaje_sobrebarriga_rotas')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Indicador de hematomas',
                    'data' => $this->indicadoresDiarios->pluck('porcentaje_hematomas')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Indicador de corte en piernas',
                    'data' => $this->indicadoresDiarios->pluck('porcentaje_corte_en_piernas')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Indicador de cobertura grasa',
                    'data' => $this->indicadoresDiarios->pluck('porcentaje_cobertura_grasa')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ]
            ]
        ];
    }
    
    public function cambiarMes($direccion)
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1);
        
        if ($direccion === 'anterior') {
            $fecha->subMonth();
        } else {
            $fecha->addMonth();
        }
        
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;
        
        $this->cargarDatos();
    }
    
    public function irAHoy()
    {
        $this->mes = Carbon::now()->month;
        $this->anio = Carbon::now()->year;
        $this->cargarDatos();
    }
    
    public function render()
    {
        return view('livewire.dashboard-mes');
    }
}