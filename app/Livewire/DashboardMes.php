<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IndicadorDiario;
use App\Services\CalculadoraIndicadores;
use Carbon\Carbon;

class DashboardMes extends Component
{
    public $mes;
    public $anio;
    public $indicadoresMes;
    public $indicadoresDiarios = [];
    public $graficoDatos = [];
    
    public function mount($mes = null, $anio = null)
    {
        $this->mes = $mes ?: Carbon::now()->month;
        $this->anio = $anio ?: Carbon::now()->year;
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
        
        // Preparar datos para gráficos
        $this->prepararDatosGrafico();
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