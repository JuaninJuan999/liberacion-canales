<?php

namespace App\Livewire;

use App\Models\IndicadorDiario;
use Carbon\Carbon;
use Livewire\Component;

class DashboardDia extends Component
{
    public $fecha;
    public $indicadores;

    // Escucha el evento 'fechaCambiada' para actualizar la fecha
    protected $listeners = ['fechaCambiada' => 'actualizarFecha'];

    public function mount()
    {
        $this->fecha = Carbon::now()->format('Y-m-d');
        $this->cargarIndicadores();
    }

    public function cargarIndicadores()
    {
        $this->indicadores = IndicadorDiario::where('fecha_operacion', $this->fecha)->first();
    }

    public function actualizarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    /**
     * Recalcula los indicadores para la fecha actual.
     * Este método es solo un disparador y no realiza cálculos directamente.
     */
    public function recalcular()
    {
        $this->cargarIndicadores(); // Simplemente vuelve a cargar desde la DB
        // Opcional: podrías emitir un evento para que el observer recalcule si fuera necesario
        // $this->dispatch('recalcular-forzado', $this->fecha);
        session()->flash('message', 'Datos del dashboard actualizados.');
    }
    
    public function render()
    {
        // Asegurarse de que siempre haya un objeto, incluso si está vacío
        if (!$this->indicadores) {
            $this->indicadores = new IndicadorDiario();
        }

        return view('livewire.dashboard-dia');
    }
}
