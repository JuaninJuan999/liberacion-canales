<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use Carbon\Carbon;

class IndicadoresDia extends Component
{
    public $fecha;
    public $indicadores;
    public $indicadoresPorOperario = [];
    public $comparacionDiaAnterior = [];
    
    protected $listeners = ['hallazgo-registrado' => 'actualizarIndicadores'];
    
    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->format('Y-m-d');
        $this->cargarIndicadores();
    }
    
    public function cargarIndicadores()
    {
        // Indicadores del día
        $this->indicadores = IndicadorDiario::where('fecha', $this->fecha)->first();
        
        // Indicadores por operario
        $this->indicadoresPorOperario = RegistroHallazgo::with(['operario', 'tipoHallazgo'])
            ->whereDate('created_at', $this->fecha)
            ->get()
            ->groupBy('operario_id')
            ->map(function($hallazgos) {
                $total = $hallazgos->count();
                $criticos = $hallazgos->filter(function($h) {
                    return $h->tipoHallazgo && $h->tipoHallazgo->es_critico;
                })->count();
                
                return [
                    'operario' => $hallazgos->first()->operario->nombre_completo,
                    'total' => $total,
                    'criticos' => $criticos,
                    'leves' => $total - $criticos,
                    'efectividad' => $total > 0 ? round((1 - ($criticos / $total)) * 100, 2) : 100
                ];
            })
            ->sortByDesc('efectividad')
            ->values()
            ->all();
        
        // Comparación con día anterior
        $this->compararConDiaAnterior();
    }
    
    protected function compararConDiaAnterior()
    {
        $fechaAnterior = Carbon::parse($this->fecha)->subDay()->format('Y-m-d');
        $indicadoresAyer = IndicadorDiario::where('fecha', $fechaAnterior)->first();
        
        if ($this->indicadores && $indicadoresAyer) {
            $this->comparacionDiaAnterior = [
                'liberacion' => [
                    'actual' => $this->indicadores->porcentaje_liberacion,
                    'anterior' => $indicadoresAyer->porcentaje_liberacion,
                    'diferencia' => $this->indicadores->porcentaje_liberacion - $indicadoresAyer->porcentaje_liberacion,
                    'tendencia' => $this->indicadores->porcentaje_liberacion > $indicadoresAyer->porcentaje_liberacion ? 'up' : 'down'
                ],
                'hallazgos' => [
                    'actual' => $this->indicadores->total_hallazgos,
                    'anterior' => $indicadoresAyer->total_hallazgos,
                    'diferencia' => $this->indicadores->total_hallazgos - $indicadoresAyer->total_hallazgos,
                    'tendencia' => $this->indicadores->total_hallazgos < $indicadoresAyer->total_hallazgos ? 'up' : 'down'
                ],
                'criticos' => [
                    'actual' => $this->indicadores->hallazgos_criticos,
                    'anterior' => $indicadoresAyer->hallazgos_criticos,
                    'diferencia' => $this->indicadores->hallazgos_criticos - $indicadoresAyer->hallazgos_criticos,
                    'tendencia' => $this->indicadores->hallazgos_criticos < $indicadoresAyer->hallazgos_criticos ? 'up' : 'down'
                ]
            ];
        }
    }
    
    public function actualizarIndicadores()
    {
        $this->cargarIndicadores();
    }
    
    public function cambiarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }
    
    public function render()
    {
        return view('livewire.indicadores-dia');
    }
}