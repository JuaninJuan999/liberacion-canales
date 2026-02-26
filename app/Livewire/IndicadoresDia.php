<?php

namespace App\Livewire;

use App\Models\IndicadorDiario;
use Carbon\Carbon;
use Livewire\Component;

class IndicadoresDia extends Component
{
    public $fecha;
    public $indicadores;
    public $hallazgosPorTipo = [];

    protected $listeners = ['hallazgo-registrado' => 'cargarIndicadores', 'fechaCambiada' => 'actualizarFecha'];

    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->format('Y-m-d');
        $this->cargarIndicadores();
    }

    public function cargarIndicadores()
    {
        $this->indicadores = IndicadorDiario::where('fecha_operacion', $this->fecha)->first();
        $this->prepararHallazgosPorTipo();
    }

    public function cambiarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
        // Dispara un evento para que otros componentes se actualicen
        $this->dispatch('fechaCambiada', $this->fecha);
    }
    
    public function actualizarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    /**
     * Prepara un array con los nombres de los tipos de hallazgo y sus totales
     * a partir de la columna JSON 'desglose_hallazgos'.
     */
    protected function prepararHallazgosPorTipo()
    {
        $this->hallazgosPorTipo = [];

        if ($this->indicadores && $this->indicadores->desglose_hallazgos) {
            // Decodificar el JSON a un array asociativo
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
