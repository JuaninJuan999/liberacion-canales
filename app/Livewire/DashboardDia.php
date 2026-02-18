<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Services\CalculadoraIndicadores;
use Carbon\Carbon;

class DashboardDia extends Component
{
    public $fecha;
    public $indicadores;
    public $hallazgosPorPuesto = [];
    public $hallazgosPorTipo = [];
    public $ultimosHallazgos = [];
    
    protected $listeners = ['hallazgo-registrado' => 'actualizarDatos'];
    
    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->format('Y-m-d');
        $this->cargarDatos();
    }
    
    public function cargarDatos()
    {
        $calculadora = new CalculadoraIndicadores();
        
        // Obtener indicadores del día
        $this->indicadores = IndicadorDiario::where('fecha', $this->fecha)->first();
        
        // Si no existen, calcularlos
        if (!$this->indicadores) {
            $this->indicadores = $calculadora->calcularIndicadoresDia($this->fecha);
        }
        
        // Hallazgos agrupados por puesto
        $this->hallazgosPorPuesto = RegistroHallazgo::with('puestoTrabajo')
            ->whereDate('created_at', $this->fecha)
            ->get()
            ->groupBy('puesto_trabajo_id')
            ->map(function($hallazgos) {
                return [
                    'puesto' => $hallazgos->first()->puestoTrabajo->nombre,
                    'total' => $hallazgos->count(),
                    'criticos' => $hallazgos->filter(function($h) {
                        return $h->tipoHallazgo && $h->tipoHallazgo->es_critico;
                    })->count()
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->all();
        
        // Hallazgos agrupados por tipo
        $this->hallazgosPorTipo = RegistroHallazgo::with('tipoHallazgo')
            ->whereDate('created_at', $this->fecha)
            ->get()
            ->groupBy('tipo_hallazgo_id')
            ->map(function($hallazgos) {
                return [
                    'tipo' => $hallazgos->first()->tipoHallazgo->nombre,
                    'total' => $hallazgos->count(),
                    'es_critico' => $hallazgos->first()->tipoHallazgo->es_critico
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->all();
        
        // Últimos 10 hallazgos
        $this->ultimosHallazgos = RegistroHallazgo::with(['tipoHallazgo', 'puestoTrabajo', 'operario'])
            ->whereDate('created_at', $this->fecha)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }
    
    public function actualizarDatos()
    {
        $this->cargarDatos();
    }
    
    public function cambiarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarDatos();
    }
    
    public function recalcularIndicadores()
    {
        $calculadora = new CalculadoraIndicadores();
        $this->indicadores = $calculadora->calcularIndicadoresDia($this->fecha);
        $this->cargarDatos();
        
        session()->flash('message', 'Indicadores recalculados correctamente');
    }
    
    public function render()
    {
        return view('livewire.dashboard-dia');
    }
}